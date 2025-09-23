<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\BkuMasterEntry;
use App\Models\TunaiMasterEntry;
use Illuminate\Http\Response;

class BKUController extends Controller
{
    // Configuration constants - can be moved to config file later
    private const DISPLAY_SCALE_FACTOR = 1;
    private const PARSE_THRESHOLD = 1000;
    private const DEFAULT_PER_PAGE = 25;
    private const PRINT_ALL_LIMIT = 1000000;

    public function master()
    {
        return view('bku.master');
    }

    /**
     * Analyze existing BKU data to extract tax-related keywords dynamically
     */
    private function getTaxKeywordsFromDatabase()
    {
        // Cache the results to avoid repeated database queries
        return Cache::remember('bku_tax_keywords', 3600, function () {
            $keywords = [
                'direct' => ['PPN', 'PPh', 'Pajak'],
                'terima_categories' => [],
                'setor_categories' => [],
                'bayar_categories' => ['PPN', 'PPh', 'Pajak']
            ];

            // Get unique patterns from existing data (only transactions with values)
            // Use limit() instead of take() for better performance and proper SQL LIMIT
            $terimaEntries = \App\Models\BkuMasterEntry::where('uraian', 'like', '%Terima%')
                ->where(function($q) {
                    $q->where('penerimaan', '>', 0)->orWhere('pengeluaran', '>', 0);
                })
                ->distinct()
                ->limit(50) // Reduced limit for better performance
                ->pluck('uraian');

            $setorEntries = \App\Models\BkuMasterEntry::where('uraian', 'like', '%Setor%')
                ->where(function($q) {
                    $q->where('penerimaan', '>', 0)->orWhere('pengeluaran', '>', 0);
                })
                ->distinct()
                ->limit(50) // Reduced limit for better performance
                ->pluck('uraian');

            // Extract patterns from Terima transactions
            foreach ($terimaEntries as $uraian) {
                $categories = $this->extractCategoriesFromUraian($uraian);
                $keywords['terima_categories'] = array_merge($keywords['terima_categories'], $categories);
            }

            // Extract patterns from Setor transactions  
            foreach ($setorEntries as $uraian) {
                $categories = $this->extractCategoriesFromUraian($uraian);
                $keywords['setor_categories'] = array_merge($keywords['setor_categories'], $categories);
            }

            // Remove duplicates and empty values
            $keywords['terima_categories'] = array_unique(array_filter($keywords['terima_categories']));
            $keywords['setor_categories'] = array_unique(array_filter($keywords['setor_categories']));
            
            // Add common setor destinations
            $keywords['setor_categories'] = array_merge($keywords['setor_categories'], ['Kas Negara', 'Bank']);

            return $keywords;
        });
    }

    /**
     * Extract meaningful categories from transaction description
     */
    private function extractCategoriesFromUraian($uraian)
    {
        $categories = [];
        
        // Common patterns to extract
        $patterns = [
            '/Pendaftaran Peserta Didik/',
            '/Pengadaan (Peralatan|Bahan)/',
            '/Pembelian Bahan/',
            '/Operasional Sekolah/',
            '/Pengembangan/',
            '/Praktik Pembelajaran/',
            '/SIPLah/',
            '/UKS/',
            '/Pemeliharaan/',
            '/Penilaian/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $uraian, $matches)) {
                if (strpos($pattern, 'Pengadaan') !== false && isset($matches[1])) {
                    $categories[] = "Pengadaan {$matches[1]}";
                } else {
                    // Extract the matched pattern and clean it
                    $category = trim($matches[0], '/');
                    $categories[] = $category;
                }
            }
        }

        // Additional specific extractions
        if (strpos($uraian, 'Pendaftaran Peserta Didik') !== false) {
            $categories[] = 'Pendaftaran Peserta Didik';
        }
        if (strpos($uraian, 'Praktik Pembelajaran') !== false) {
            $categories[] = 'Praktik Pembelajaran';
        }
        if (strpos($uraian, 'SIPLah') !== false) {
            $categories[] = 'SIPLah';
        }

        return array_unique($categories);
    }

    /**
     * Helper method to check if transaction description contains any of the specified keywords
     */
    private function containsAnyKeyword($text, array $keywords)
    {
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper method to determine if an entry is tax-related using dynamic keywords from database
     */
    private function isTaxRelatedEntry($entry)
    {
        $uraian = $entry->uraian;
        $taxKeywords = $this->getTaxKeywordsFromDatabase();
        
        // Direct tax keywords
        if ($this->containsAnyKeyword($uraian, $taxKeywords['direct'])) {
            return true;
        }
        
        // Terima transactions with tax implications
        if (strpos($uraian, 'Terima') !== false && 
            ($this->containsAnyKeyword($uraian, ['PPN']) || 
             $this->containsAnyKeyword($uraian, $taxKeywords['terima_categories']))) {
            return true;
        }
        
        // Setor transactions with tax implications
        if (strpos($uraian, 'Setor') !== false && 
            ($this->containsAnyKeyword($uraian, ['PPN', 'PPh']) || 
             $this->containsAnyKeyword($uraian, $taxKeywords['setor_categories']))) {
            return true;
        }
        
        // Bayar transactions related to tax
        if (strpos($uraian, 'Bayar') !== false && 
            $this->containsAnyKeyword($uraian, $taxKeywords['bayar_categories'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Build query conditions for tax-related transactions using dynamic keywords
     * Only includes transactions with actual values (penerimaan > 0 OR pengeluaran > 0)
     */
    private function buildTaxQueryConditions($query)
    {
        $taxKeywords = $this->getTaxKeywordsFromDatabase();
        
        return $query->where(function($mainQuery) use ($taxKeywords) {
            // Direct tax keywords
            foreach ($taxKeywords['direct'] as $keyword) {
                $mainQuery->orWhere('uraian', 'like', "%{$keyword}%");
            }
            
            // Terima transactions
            $mainQuery->orWhere(function($q) use ($taxKeywords) {
                $q->where('uraian', 'like', '%Terima%')
                  ->where(function($q2) use ($taxKeywords) {
                      $q2->where('uraian', 'like', '%PPN%');
                      foreach ($taxKeywords['terima_categories'] as $category) {
                          $q2->orWhere('uraian', 'like', "%{$category}%");
                      }
                  });
            });
            
            // Setor transactions
            $mainQuery->orWhere(function($q) use ($taxKeywords) {
                $q->where('uraian', 'like', '%Setor%')
                  ->where(function($q2) use ($taxKeywords) {
                      $q2->where('uraian', 'like', '%PPN%')
                         ->orWhere('uraian', 'like', '%PPh%');
                      foreach ($taxKeywords['setor_categories'] as $category) {
                          $q2->orWhere('uraian', 'like', "%{$category}%");
                      }
                  });
            });
            
            // Bayar transactions
            $mainQuery->orWhere(function($q) use ($taxKeywords) {
                $q->where('uraian', 'like', '%Bayar%')
                  ->where(function($q2) use ($taxKeywords) {
                      foreach ($taxKeywords['bayar_categories'] as $category) {
                          $q2->orWhere('uraian', 'like', "%{$category}%");
                      }
                  });
            });
        })
        // Only include entries with actual transaction values
        ->where(function($valueQuery) {
            $valueQuery->where('penerimaan', '>', 0)
                      ->orWhere('pengeluaran', '>', 0);
        });
    }

    public function umum()
    {
        $user = request()->user();
        $month = request('month'); // 1-12 or null
        $year = request('year');   // YYYY or null

        $query = \App\Models\BkuMasterEntry::where('user_id', $user->id)
            ->where(function($q){
                $q->where('uraian', 'not like', 'Saldo Bank Bulan %')
                  ->where('uraian', 'not like', 'Saldo Tunai Bulan %');
            });
        if ($month && $year) {
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        }
        $query->orderBy('tanggal')->orderBy('id');

    $perPage = self::DEFAULT_PER_PAGE;
    $entries = $query->paginate($perPage)->withQueryString();

        $baseQuery = \App\Models\BkuMasterEntry::where('user_id', $user->id)
            ->where(function($q){
                $q->where('uraian', 'not like', 'Saldo Bank Bulan %')
                  ->where('uraian', 'not like', 'Saldo Tunai Bulan %');
            });
        if ($month && $year) {
            $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
            
            // For saldo awal calculation, we need to:
            // 1. Include all transactions before the period
            // 2. Include saldo rows from the beginning of the period (as they represent opening balance)
            $saldoQuery = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->where(function($q) use ($periodStart, $year, $month) {
                    // All transactions before the period
                    $q->where(function($sub) use ($periodStart) {
                        $sub->whereNull('tanggal')->orWhere('tanggal', '<', $periodStart);
                    })
                    // OR saldo rows from the first day of the period (opening balance)
                    ->orWhere(function($sub) use ($year, $month) {
                        $sub->whereYear('tanggal', $year)
                            ->whereMonth('tanggal', $month)
                            ->whereDay('tanggal', 1)
                            ->where(function($saldo) {
                                $saldo->where('uraian', 'like', 'Saldo Bank Bulan %')
                                      ->orWhere('uraian', 'like', 'Saldo Tunai Bulan %');
                            });
                    });
                });
            
            // Get saldo awal from both regular transactions and opening saldo rows
            $pBefore = (float) (clone $saldoQuery)->sum('penerimaan');
            $gBefore = (float) (clone $saldoQuery)->sum('pengeluaran');
            
            // For opening balance entries, use the saldo value directly instead of penerimaan - pengeluaran
            $openingBankEntry = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereDay('tanggal', 1)
                ->where('uraian', 'like', 'Saldo Bank Bulan %')
                ->first();
            
            if ($openingBankEntry) {
                $saldoAwal = (float) $openingBankEntry->saldo;
            } else {
            $saldoAwal = $pBefore - $gBefore;
            }

            // total dalam periode (excluding saldo rows)
            $inPeriod = (clone $baseQuery)->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
            $pIn = (float) (clone $inPeriod)->sum('penerimaan');
            $gIn = (float) (clone $inPeriod)->sum('pengeluaran');

            $openingDebit = $saldoAwal > 0 ? $saldoAwal : 0.0;
            $openingCredit = $saldoAwal < 0 ? abs($saldoAwal) : 0.0;
            $prevLabel = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->locale(app()->getLocale())->translatedFormat('F Y');

            // Calculate total from ALL period data (EXCLUDING Bunga Bank & Pajak Bunga)
            $allInPeriodQuery = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->where('uraian', 'NOT LIKE', '%Bunga Bank%')
                ->where('uraian', 'NOT LIKE', '%Pajak Bunga%');
                
            $totalPenerimaan = (float) (clone $allInPeriodQuery)->sum('penerimaan');
            $totalPengeluaran = (float) (clone $allInPeriodQuery)->sum('pengeluaran');
            
            // Raw penerimaan = transactions only (excluding opening balance entries)
            $rawPenerimaan = (float) (clone $allInPeriodQuery)
                ->where('uraian', 'NOT LIKE', '%Saldo Bank%')
                ->where('uraian', 'NOT LIKE', '%Saldo Tunai%')
                ->sum('penerimaan');
            
            // Opening balance penerimaan from saldo entries (mainly for January)
            $openingPenerimaan = (float) (clone $allInPeriodQuery)
                ->where(function($q) {
                    $q->where('uraian', 'LIKE', '%Saldo Bank%')
                      ->orWhere('uraian', 'LIKE', '%Saldo Tunai%');
                })
                ->sum('penerimaan');
            
            // Display penerimaan = raw + opening (from entries) + saldo awal (from previous month)
            // For January: opening comes from saldo entries in database
            // For other months: opening comes from saldo awal (previous month ending)
            if ($openingPenerimaan > 0) {
                // January case - opening balance is in database
                $displayPenerimaan = $rawPenerimaan + $openingPenerimaan;
            } else {
                // Other months - opening balance is saldo awal from previous month
                $displayPenerimaan = $rawPenerimaan + $saldoAwal;
            }
            
            // Calculate separate saldo for Kas Tunai and Bank
            // Get actual tunai saldo from tunai_master_entries table
            $lastTunaiEntry = TunaiMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
                
            $saldoTunaiAkurat = $lastTunaiEntry ? ($lastTunaiEntry->saldo * self::DISPLAY_SCALE_FACTOR) : 0;
                
            $lastBankEntry = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->where('uraian', 'LIKE', '%Bank%')
                ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
                
            // Get the final saldo (overall)
            $finalEntry = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();

            $totalSaldoAkhir = $finalEntry ? $finalEntry->saldo : $saldoAwal + ($pIn - $gIn);
            $saldoBankAkurat = ($totalSaldoAkhir * self::DISPLAY_SCALE_FACTOR) - $saldoTunaiAkurat;
            
            $totals = [
                'penerimaan' => $pIn, // Period transactions only (excluding opening balance)
                'pengeluaran' => $gIn, // Period transactions only (excluding opening balance)
                'saldo_period' => $pIn - $gIn,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $totalSaldoAkhir,
                'raw_penerimaan' => $rawPenerimaan, // Transactions only (no opening balance)
                'display_penerimaan' => $displayPenerimaan, // Total including opening balance for reporting
                'display_pengeluaran' => $totalPengeluaran, // Total excluding Bunga Bank & Pajak Bunga
                'saldo_tunai' => $saldoTunaiAkurat / self::DISPLAY_SCALE_FACTOR, // From tunai_master_entries (converted back)
                'saldo_bank' => $saldoBankAkurat / self::DISPLAY_SCALE_FACTOR, // Calculated as difference
            ];
            $opening = [
                'label' => 'Saldo Bulan '.$prevLabel,
                'debit' => $openingDebit,
                'credit' => $openingCredit,
                'date' => \Carbon\Carbon::createFromDate($year, $month, 1),
            ];
        } else {
            $totals = [
                'penerimaan' => (float) (clone $baseQuery)->sum('penerimaan'),
                'pengeluaran' => (float) (clone $baseQuery)->sum('pengeluaran'),
                'saldo_awal' => 0,
                'saldo_akhir' => 0
            ];
            
            // Get the actual final saldo from the last entry
            $lastEntry = (clone $baseQuery)->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
            $totals['saldo_akhir'] = $lastEntry ? (float) $lastEntry->saldo : 0;
            
            $opening = null;
        }

        // Running start baseline for pagination
        $runningStart = 0.0;
        if ($month && $year) {
            // For monthly filtered view, running start is just saldo awal (no scaling here)
            $runningStart = $totals['saldo_awal'] ?? 0.0;
            
            // Add transactions before current page (if not first page) - don't double count opening
            if ($entries->count() > 0 && $entries->currentPage() > 1) {
                $first = $entries->first();
                $beforeIn = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                    ->where(function($q){
                        $q->where('uraian', 'not like', 'Saldo Bank Bulan %')
                          ->where('uraian', 'not like', 'Saldo Tunai Bulan %');
                    })
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->where(function($q) use ($first) {
                        if (is_null($first->tanggal)) {
                            $q->whereNull('tanggal')->where('id', '<', $first->id);
                        } else {
                            $q->where('tanggal', '<', $first->tanggal)
                              ->orWhere(function($q2) use ($first) {
                                  $q2->where('tanggal', $first->tanggal)
                                     ->where('id', '<', $first->id);
                              });
                        }
                    });
                $pB = (float) (clone $beforeIn)->sum('penerimaan');
                $gB = (float) (clone $beforeIn)->sum('pengeluaran');
                $runningStart += ($pB - $gB);
            }
        } else {
            // For all-time view, use actual DB saldo as baseline for accuracy
            if ($entries->count() > 0 && $entries->currentPage() > 1) {
                $first = $entries->first();
                // Use the actual saldo from previous entry as running start baseline
                $prevEntry = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                    ->where(function($q){
                        $q->where('uraian', 'not like', 'Saldo Bank Bulan %')
                          ->where('uraian', 'not like', 'Saldo Tunai Bulan %');
                    })
                    ->where(function($q) use ($first) {
                        if (is_null($first->tanggal)) {
                            $q->whereNull('tanggal')->where('id', '<', $first->id);
                        } else {
                            $q->whereNull('tanggal')
                              ->orWhere('tanggal', '<', $first->tanggal)
                              ->orWhere(function($q2) use ($first) {
                                  $q2->where('tanggal', $first->tanggal)
                                     ->where('id', '<', $first->id);
                              });
                        }
                    })
                    ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')
                    ->first();
                
                if ($prevEntry) {
                    // Start with the previous entry's saldo, then add current entry's transaction
                    $runningStart = (float) $prevEntry->saldo + (float) $first->penerimaan - (float) $first->pengeluaran;
                } else {
                    $runningStart = (float) $first->saldo;
                }
            }
        }

        $showOpeningRow = ($month && $year && $entries->currentPage() === 1);

        return view('bku.umum', compact('entries', 'totals', 'runningStart', 'month', 'year', 'opening', 'showOpeningRow') + [
            'displayScale' => self::DISPLAY_SCALE_FACTOR
        ]);
    }

    public function umumPrint()
    {
        $user = request()->user();
        $month = request('month');
        $year = request('year');

        // Use the same query logic as umum() method
        $query = \App\Models\BkuMasterEntry::where('user_id', $user->id)
            ->where(function($q){
                $q->where('uraian', 'not like', 'Saldo Bank Bulan %')
                  ->where('uraian', 'not like', 'Saldo Tunai Bulan %');
            });

        if ($month && $year) {
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        }

        $entries = $query->orderBy('tanggal')->orderBy('id')->get();

        // Use exact same logic as umum() method for totals calculation
        $baseQuery = \App\Models\BkuMasterEntry::where('user_id', $user->id)
            ->where(function($q){
                $q->where('uraian', 'not like', 'Saldo Bank Bulan %')
                  ->where('uraian', 'not like', 'Saldo Tunai Bulan %');
            });

        if ($month && $year) {
            $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
            
            // Same saldo awal calculation as umum()
            $saldoQuery = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->where(function($q) use ($periodStart, $year, $month) {
                    $q->where(function($sub) use ($periodStart) {
                        $sub->whereNull('tanggal')->orWhere('tanggal', '<', $periodStart);
                    })
                    ->orWhere(function($sub) use ($year, $month) {
                        $sub->whereYear('tanggal', $year)
                            ->whereMonth('tanggal', $month)
                            ->whereDay('tanggal', 1)
                            ->where(function($saldo) {
                                $saldo->where('uraian', 'like', 'Saldo Bank Bulan %')
                                      ->orWhere('uraian', 'like', 'Saldo Tunai Bulan %');
                            });
                    });
                });
            
            $pBefore = (float) (clone $saldoQuery)->sum('penerimaan');
            $gBefore = (float) (clone $saldoQuery)->sum('pengeluaran');
            
            // For opening balance entries, use the saldo value directly instead of penerimaan - pengeluaran
            $openingBankEntry = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereDay('tanggal', 1)
                ->where('uraian', 'like', 'Saldo Bank Bulan %')
                ->first();
            
            if ($openingBankEntry) {
                $saldoAwal = (float) $openingBankEntry->saldo;
            } else {
            $saldoAwal = $pBefore - $gBefore;
            }

            // Same total calculation as umum() method
            $allInPeriodQuery = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->where('uraian', 'NOT LIKE', '%Bunga Bank%')
                ->where('uraian', 'NOT LIKE', '%Pajak Bunga%');
                
            $totalPenerimaan = (float) (clone $allInPeriodQuery)->sum('penerimaan');
            $totalPengeluaran = (float) (clone $allInPeriodQuery)->sum('pengeluaran');
            
            // Same raw penerimaan calculation
            $rawPenerimaan = (float) (clone $allInPeriodQuery)
                ->where('uraian', 'NOT LIKE', '%Saldo Bank%')
                ->where('uraian', 'NOT LIKE', '%Saldo Tunai%')
                ->sum('penerimaan');
            
            $openingPenerimaan = (float) (clone $allInPeriodQuery)
                ->where(function($q) {
                    $q->where('uraian', 'LIKE', '%Saldo Bank%')
                      ->orWhere('uraian', 'LIKE', '%Saldo Tunai%');
                })
                ->sum('penerimaan');
            
            // Same display penerimaan logic as umum()
            if ($openingPenerimaan > 0) {
                $displayPenerimaan = $rawPenerimaan + $openingPenerimaan;
            } else {
                $displayPenerimaan = $rawPenerimaan + $saldoAwal;
            }

            // Get final saldo
            $finalEntry = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
            
            $saldoAkhir = $finalEntry ? $finalEntry->saldo : $saldoAwal;

            // Calculate separate saldo for Kas Tunai and Bank (same as umum method)
            // Get actual tunai saldo from tunai_master_entries table
            $lastTunaiEntry = TunaiMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
                
            $saldoTunaiAkurat = $lastTunaiEntry ? ($lastTunaiEntry->saldo * self::DISPLAY_SCALE_FACTOR) : 0;
                
            $lastBankEntry = \App\Models\BkuMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->where('uraian', 'LIKE', '%Bank%')
                ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();

            $totalSaldoAkhir = $finalEntry ? $finalEntry->saldo : $saldoAwal;
            $saldoBankAkurat = ($totalSaldoAkhir * self::DISPLAY_SCALE_FACTOR) - $saldoTunaiAkurat;

            $totals = [
                'raw_penerimaan' => $rawPenerimaan,
                'display_penerimaan' => $displayPenerimaan,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $totalSaldoAkhir,
                'saldo_tunai' => $saldoTunaiAkurat / self::DISPLAY_SCALE_FACTOR, // From tunai_master_entries (converted back)
                'saldo_bank' => $saldoBankAkurat / self::DISPLAY_SCALE_FACTOR, // Calculated as difference
            ];

            $openingDebit = $saldoAwal > 0 ? $saldoAwal : 0.0;
            $openingCredit = $saldoAwal < 0 ? abs($saldoAwal) : 0.0;
            $prevLabel = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->locale(app()->getLocale())->translatedFormat('F Y');
        } else {
            $saldoAwal = 0.0;
            $pIn = (float) (clone $baseQuery)->sum('penerimaan');
            $gIn = (float) (clone $baseQuery)->sum('pengeluaran');
            $saldoAkhir = $pIn - $gIn;
            $totalPenerimaan = $pIn;
            $totalPengeluaran = $gIn;
            $openingDebit = 0.0; 
            $openingCredit = 0.0; 
            $prevLabel = null;
            $totals = [
                'raw_penerimaan' => 0,
                'display_penerimaan' => $totalPenerimaan,
                'saldo_awal' => 0,
                'saldo_akhir' => $saldoAkhir,
            ];
        }

        return view('bku.umum_print', [
            'entries' => $entries,
            'month' => $month,
            'year' => $year,
            'saldoAwal' => $saldoAwal,
            'saldoAkhir' => $saldoAkhir,
            'totalIn' => $displayPenerimaan * self::DISPLAY_SCALE_FACTOR,
            'totalOut' => $totalPengeluaran * self::DISPLAY_SCALE_FACTOR,
            'openingDebit' => $openingDebit,
            'openingCredit' => $openingCredit,
            'openingLabel' => $prevLabel ? ('Saldo Bulan '.$prevLabel) : null,
            'displayScale' => self::DISPLAY_SCALE_FACTOR,
            'totals' => $totals,
        ]);
    }

    public function tunai()
    {
        $user = request()->user();
        $month = request('bulan');
        $year = request('tahun');

        // Base query for TunaiMasterEntry
        $query = TunaiMasterEntry::where('user_id', $user->id);

        if ($month && $year) {
            $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
        }

        // Get paginated entries
        $entries = $query->orderBy('tanggal')->orderBy('id')->paginate(20);

        if ($month && $year) {
            // Calculate saldo awal (before current month)
            $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
            
            $saldoQuery = TunaiMasterEntry::where('user_id', $user->id)
                ->where(function($q) use ($periodStart) {
                    $q->whereNull('tanggal')->orWhere('tanggal', '<', $periodStart);
                });
            
            $pBefore = (float) (clone $saldoQuery)->sum('penerimaan');
            $gBefore = (float) (clone $saldoQuery)->sum('pengeluaran');
            $saldoAwal = $pBefore - $gBefore;

            // Current period totals
            $allInPeriodQuery = TunaiMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
                
            $pIn = (float) (clone $allInPeriodQuery)->sum('penerimaan');
            $gIn = (float) (clone $allInPeriodQuery)->sum('pengeluaran');
            
            // Get final saldo
            $finalEntry = TunaiMasterEntry::where('user_id', $user->id)
                ->whereYear('tanggal', $year)->whereMonth('tanggal', $month)
                ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
            
            $saldoAkhir = $finalEntry ? $finalEntry->saldo : $saldoAwal;
            
            $totals = [
                'penerimaan' => $pIn,
                'pengeluaran' => $gIn,
                'saldo_period' => $pIn - $gIn,
                'saldo_awal' => $saldoAwal,
                'saldo_akhir' => $saldoAkhir,
            ];

            // Opening balance for display
            $openingDebit = $saldoAwal > 0 ? $saldoAwal : 0.0;
            $openingCredit = $saldoAwal < 0 ? abs($saldoAwal) : 0.0;
            $prevLabel = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->locale('id')->translatedFormat('F Y');
            
            $opening = [
                'label' => 'Saldo Tunai Bulan '.$prevLabel,
                'debit' => $openingDebit,
                'credit' => $openingCredit,
                'date' => \Carbon\Carbon::createFromDate($year, $month, 1),
            ];
            $showOpeningRow = ($openingDebit + $openingCredit) > 0;
        } else {
            // For all data (no month/year filter)
            $totals = [
                'penerimaan' => (float) TunaiMasterEntry::where('user_id', $user->id)->sum('penerimaan'),
                'pengeluaran' => (float) TunaiMasterEntry::where('user_id', $user->id)->sum('pengeluaran'),
                'saldo_awal' => 0,
                'saldo_akhir' => 0
            ];
            
            // Get the actual final saldo from the last entry
            $lastEntry = TunaiMasterEntry::where('user_id', $user->id)
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $totals['saldo_akhir'] = $lastEntry ? (float) $lastEntry->saldo : 0;
            
            $opening = null;
            $showOpeningRow = false;
        }

        // Running start baseline for pagination
        $runningStart = 0.0;
        if ($month && $year) {
            $runningStart = $totals['saldo_awal'] ?? 0.0;
            
            // Add transactions before current page (if not first page)
            if ($entries->count() > 0 && $entries->currentPage() > 1) {
                $first = $entries->first();
                $beforeIn = TunaiMasterEntry::where('user_id', $user->id)
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->where(function($q) use ($first) {
                        if (is_null($first->tanggal)) {
                            $q->whereNull('tanggal')->where('id', '<', $first->id);
                        } else {
                            $q->where('tanggal', '<', $first->tanggal)
                              ->orWhere(function($q2) use ($first) {
                                  $q2->where('tanggal', $first->tanggal)
                                     ->where('id', '<', $first->id);
                              });
                        }
                    });
                $pB = (float) (clone $beforeIn)->sum('penerimaan');
                $gB = (float) (clone $beforeIn)->sum('pengeluaran');
                $runningStart += ($pB - $gB);
            }
        }

        return view('bku.tunai', compact('entries', 'totals', 'runningStart', 'month', 'year', 'opening', 'showOpeningRow') + [
            'displayScale' => self::DISPLAY_SCALE_FACTOR
        ]);
    }

    public function bank(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;

        // Get month and year from request
        $month = $request->get('bulan');
        $year = $request->get('tahun');

        // Get BKU Umum entries for the specified month/year
        $query = \App\Models\BkuMasterEntry::where('user_id', $userId);
        
        if ($month && $year) {
            $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }
        
        $entries = $query->orderBy('tanggal')->orderBy('id')->get();

        // Filter for bank-related transactions (BNU, BBU, bank saldo, and bank interest entries)
        $bankEntries = $entries->filter(function ($entry) {
            // Include BNU transactions (non-cash/transfer)
            if (strpos($entry->no_bukti, 'BNU') === 0) {
                return true;
            }
            
            // Include BBU transactions (bank umum)
            if (strpos($entry->no_bukti, 'BBU') === 0) {
                return true;
            }
            
            // Include bank saldo entries (all months)
            if (strpos($entry->uraian, 'Saldo Bank Bulan') !== false) {
                return true;
            }
            
            // Include bank interest and tax entries
            if (strpos($entry->uraian, 'Bunga Bank') !== false) {
                return true;
            }
            
            if (strpos($entry->uraian, 'Pajak Bunga') !== false) {
                return true;
            }
            
            return false;
        });

        // Calculate totals
        $totalPenerimaan = $bankEntries->sum('penerimaan');
        $totalPengeluaran = $bankEntries->sum('pengeluaran');
        
        // Add saldo bank entries to penerimaan total
        $saldoBankEntries = $bankEntries->filter(function ($entry) {
            return strpos($entry->uraian, 'Saldo Bank Bulan') !== false;
        });
        
        foreach ($saldoBankEntries as $entry) {
            $totalPenerimaan += $entry->saldo;
        }
        
        $totals = [
            'penerimaan' => $totalPenerimaan,
            'pengeluaran' => $totalPengeluaran,
            'saldo_awal' => 0,
            'saldo_akhir' => 0
        ];

        // Get opening balance and calculate saldo akhir only if month/year is specified
        if ($month && $year) {
            // Get opening balance from "Saldo Bank Bulan" entry in current month
            $saldoBankEntry = $bankEntries->filter(function ($entry) {
                return strpos($entry->uraian, 'Saldo Bank Bulan') !== false;
            })->first();

            $saldoAwal = 0;
            if ($saldoBankEntry) {
                $saldoAwal = (float) $saldoBankEntry->saldo;
            }

            $totals['saldo_awal'] = $saldoAwal;
            
            // Get the actual final saldo from the last bank entry in the period
            $lastBankEntry = $bankEntries->last();
            $totals['saldo_akhir'] = $lastBankEntry ? (float) $lastBankEntry->saldo : $saldoAwal;
        } else {
            // For "Semua" view, get the actual final saldo from the last entry
            $lastEntry = $bankEntries->last();
            $totals['saldo_akhir'] = $lastEntry ? (float) $lastEntry->saldo : 0;
        }

        // Get available months and years for dropdown
        $availableMonths = \App\Models\BkuMasterEntry::where('user_id', $userId)
            ->selectRaw('MONTH(tanggal) as month')
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->map(function ($month) {
                return [
                    'value' => $month,
                    'label' => \Carbon\Carbon::create()->month($month)->format('F')
                ];
            });

        $availableYears = \App\Models\BkuMasterEntry::where('user_id', $userId)
            ->selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('bku.bank', compact(
            'bankEntries',
            'totals',
            'month',
            'year',
            'availableMonths',
            'availableYears'
        ));
    }

    public function pajak(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;

        // Get month and year from request
        $month = $request->get('bulan');
        $year = $request->get('tahun');

        // Get BKU Umum entries for the specified month/year
        $query = \App\Models\BkuMasterEntry::where('user_id', $userId);
        
        if ($month && $year) {
            $query->whereMonth('tanggal', $month)->whereYear('tanggal', $year);
        }
        
        $entries = $query->orderBy('tanggal')->orderBy('id')->get();

        // Filter for tax-related transactions using helper method
        // Only include transactions that have actual values (penerimaan > 0 OR pengeluaran > 0)
        $pajakEntries = $entries->filter(function ($entry) {
            // Skip entries with no actual transaction values
            if ($entry->penerimaan == 0 && $entry->pengeluaran == 0) {
                return false;
            }
            
            return $this->isTaxRelatedEntry($entry);
        });

        // Calculate totals
        $totalPenerimaan = $pajakEntries->sum('penerimaan');
        $totalPengeluaran = $pajakEntries->sum('pengeluaran');
        
        // Calculate pajak saldo (running balance for tax transactions only)
        $pajakSaldoAwal = 0;
        $pajakSaldoAkhir = 0;
        
        if ($month && $year) {
            // Get accumulated balance from ALL previous tax transactions using helper method
            $periodStart = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
            $prevPajakEntries = \App\Models\BkuMasterEntry::where('user_id', $userId)
                ->where('tanggal', '<', $periodStart);
            $prevPajakEntries = $this->buildTaxQueryConditions($prevPajakEntries)->get();

            // Calculate accumulated saldo from all previous tax transactions
            foreach ($prevPajakEntries as $entry) {
                $pajakSaldoAwal += (float)$entry->penerimaan - (float)$entry->pengeluaran;
            }
        } else {
            // If no filter, calculate from beginning of all data using helper method
            $allPrevPajakEntries = \App\Models\BkuMasterEntry::where('user_id', $userId);
            $allPrevPajakEntries = $this->buildTaxQueryConditions($allPrevPajakEntries)
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get();
            
            // Only calculate from entries before current filtered entries
            $currentEntryIds = $pajakEntries->pluck('id')->toArray();
            foreach ($allPrevPajakEntries as $entry) {
                if (!in_array($entry->id, $currentEntryIds)) {
                    $pajakSaldoAwal += (float)$entry->penerimaan - (float)$entry->pengeluaran;
                }
            }
        }
        
        // Calculate running saldo for current period
        $runningSaldo = $pajakSaldoAwal;
        foreach ($pajakEntries as $entry) {
            $runningSaldo += $entry->penerimaan - $entry->pengeluaran;
            $entry->pajak_saldo = $runningSaldo;
        }
        
        $pajakSaldoAkhir = $runningSaldo;
        
        $totals = [
            'penerimaan' => $totalPenerimaan,
            'pengeluaran' => $totalPengeluaran,
            'saldo_awal' => $pajakSaldoAwal,
            'saldo_akhir' => $pajakSaldoAkhir
        ];

        // Get available months and years for dropdown
        $availableMonths = \App\Models\BkuMasterEntry::where('user_id', $userId)
            ->selectRaw('MONTH(tanggal) as month')
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->map(function ($month) {
                return [
                    'value' => $month,
                    'label' => str_pad($month, 2, '0', STR_PAD_LEFT) . ' - ' . 
                        \Carbon\Carbon::createFromDate(null, $month, 1)->format('F')
                ];
            });

        $availableYears = \App\Models\BkuMasterEntry::where('user_id', $userId)
            ->selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('bku.pajak', compact(
            'pajakEntries',
            'totals',
            'month',
            'year',
            'availableMonths',
            'availableYears'
        ));
    }

    /**
     * Show extracted tax categories for admin review
     */
    public function showTaxCategories()
    {
        $keywords = $this->getTaxKeywordsFromDatabase();
        
        // Get some sample transactions for each category
        $samples = [];
        foreach (['terima_categories', 'setor_categories'] as $type) {
            $samples[$type] = [];
            foreach ($keywords[$type] as $category) {
                $sample = \App\Models\BkuMasterEntry::where('uraian', 'like', "%{$category}%")
                    ->first();
                if ($sample) {
                    $samples[$type][$category] = $sample->uraian;
                }
            }
        }
        
        return response()->json([
            'keywords' => $keywords,
            'samples' => $samples,
            'cache_status' => Cache::has('bku_tax_keywords') ? 'cached' : 'fresh'
        ]);
    }

    /**
     * Clear tax keywords cache
     */
    public function clearTaxCache()
    {
        Cache::forget('bku_tax_keywords');
        return response()->json(['status' => 'Cache cleared successfully']);
    }

    public function masterUpload(Request $request)
    {
        $data = $request->validate([
            'file' => [
                'required', 
                'file', 
                'mimes:xlsx', 
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'max:5120' // up to ~5MB
            ]
        ]);

        if (!$request->hasFile('file')) {
            return back()->withErrors(['file' => 'Silakan pilih file .xlsx terlebih dahulu.'])->withInput();
        }
        $uploaded = $request->file('file');
        if (!$uploaded || !$uploaded->isValid()) {
            return back()->withErrors(['file' => 'File tidak valid.'])->withInput();
        }

        // On Windows, getRealPath() may return false; getPathname() is reliable for tmp path
        $filePath = $uploaded->getPathname();
        if (empty($filePath) || !file_exists($filePath)) {
            return back()->withErrors(['file' => 'Gagal membaca file sementara yang diunggah.'])->withInput();
        }
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true); // preserve columns by letter
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal membaca file: '.$e->getMessage()])->withInput();
        }

        // Map to headers + preview (first row as header if looks like header)
        $headers = [];
        $preview = [];
        if (!empty($rows)) {
            $first = array_values($rows)[0];
            // Normalize header names
            $headers = array_values(array_map(function($v) {
                $v = is_string($v) ? trim($v) : $v;
                return $v === null || $v === '' ? '-' : $v;
            }, $first));

            // Remaining as data
            $dataRows = array_slice(array_values($rows), 1);
            foreach ($dataRows as $r) {
                $preview[] = array_values($r);
                if (count($preview) >= 100) break; // limit preview
            }
        }

        // Keep file name for user feedback
        $fileName = $uploaded->getClientOriginalName();

        // Persist the uploaded file temporarily and pass a token so import can read the full file
        $token = Str::uuid()->toString();
        // Ensure directory exists and copy the uploaded tmp file content into storage
        Storage::disk('local')->makeDirectory('bku_imports');
        $relative = 'bku_imports/'.$token.'.xlsx';
        try {
            $contents = @file_get_contents($filePath);
            if ($contents === false) {
                throw new \RuntimeException('Tidak bisa membaca file sementara yang diunggah.');
            }
            Storage::disk('local')->put($relative, $contents);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal menyimpan file sementara untuk impor: '.$e->getMessage()])->withInput();
        }

        return view('bku.master', [
            'headers' => $headers,
            'preview' => $preview,
            'fileName' => $fileName,
            'importToken' => $token,
        ])->with('status', 'File berhasil diunggah: '.$fileName);
    }

    public function masterImport(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string']
        ]);

        $token = $request->string('token');
        $relativePath = 'bku_imports/'.$token.'.xlsx';
        if (!Storage::exists($relativePath)) {
            return back()->withErrors(['file' => 'File impor sementara tidak ditemukan. Silakan unggah ulang.']);
        }

        $fullPath = Storage::path($relativePath);
        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $allRows = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Gagal membaca file impor: '.$e->getMessage()]);
        }

        if (empty($allRows)) {
            return redirect()->route('bku.master')->with('status', 'Tidak ada data untuk diimpor.');
        }

        $rowsIndexed = array_values($allRows);
        $headerRow = array_values($rowsIndexed[0] ?? []);
        $dataRows = array_slice($rowsIndexed, 1);

        // Build header map
        $map = [];
        foreach ($headerRow as $i => $h) {
            $key = strtolower(trim((string)$h));
            $map[$key] = $i;
        }

        $required = ['tanggal','kode kegiatan','kode rekening','no. bukti','uraian','penerimaan','pengeluaran','saldo'];
        foreach ($required as $req) {
            if (!array_key_exists($req, $map)) {
                return back()->withErrors(["Kolom '{$req}' tidak ditemukan pada header."]);
            }
        }

        $user = $request->user();

        // If requested, detect months in file and clear existing data for those months to avoid duplicates (run once)
        if ($request->boolean('clear_period')) {
            $months = [];
            foreach ($dataRows as $r) {
                $vals = array_values($r);
                $raw = $vals[$map['tanggal'] ?? null] ?? null;
                if ($raw === null || $raw === '') continue;
                $dt = null;
                if (is_numeric($raw) && $raw > 30000 && $raw < 60000) {
                    try { $dt = ExcelDate::excelToDateTimeObject((float)$raw); } catch (\Throwable $e) { $dt = null; }
                } else {
                    try { $dt = new \DateTime(str_replace('/', '-', (string)$raw)); } catch (\Throwable $e) { $dt = null; }
                }
                if ($dt) {
                    $m = (int)$dt->format('n');
                    $y = (int)$dt->format('Y');
                    $months[$y.'-'.$m] = [$y, $m];
                }
            }
            foreach ($months as [$yy, $mm]) {
                \App\Models\BkuMasterEntry::where('user_id', $user->id)
                    ->whereYear('tanggal', $yy)
                    ->whereMonth('tanggal', $mm)
                    ->delete();
            }
        }

        $num = function ($v) {
            if ($v === null || $v === '') return 0;
            $s = str_replace(["\u{00A0}", '\u00A0'], ' ', (string)$v); // normalize nbsp
            $s = trim($s);
            
            // Handle different number formats including comma-separated thousands
            // Remove spaces
            $s = str_replace(' ', '', $s);
            
            // Handle possible Excel formula results or formatted strings
            if (is_string($s)) {
                // Remove currency symbols and non-numeric characters except dots, commas, and minus
                $s = preg_replace('/[^\d.,-]/', '', $s);
            }
            
            // Count dots and commas to determine format
            $dotCount = substr_count($s, '.');
            $commaCount = substr_count($s, ',');
            
            if ($commaCount > 0 && $dotCount == 0) {
                // Format with commas only
                $parts = explode(',', $s);
                if (count($parts) == 2 && strlen($parts[1]) <= 2) {
                    // Format like 123,45 (decimal comma)
                    $s = str_replace(',', '.', $s);
                    return is_numeric($s) ? (float)$s : 0;
                } else {
                    // Format like 13,696,000 (comma as thousands separator)
                    $s = str_replace(',', '', $s);
                    return is_numeric($s) ? (float)$s : 0;
                }
            } elseif ($dotCount > 0 && $commaCount == 0) {
                // Format like 427.840.000 or 300.000 (Indonesian thousands separator)
                if ($dotCount > 1) {
                    // Multiple dots = thousands separator
                    $s = str_replace('.', '', $s);
                    return is_numeric($s) ? (float)$s : 0;
                } else {
                    // Single dot = could be decimal point or thousands separator
                    $result = is_numeric($s) ? (float)$s : 0;
                    // For BKU, check if it looks like thousands (ends with 000)
                    if ($result >= self::PARSE_THRESHOLD && $result % 1000 == 0) {
                        // Looks like thousands, don't divide
                        return $result;
                    } elseif ($result >= self::PARSE_THRESHOLD) {
                        // Large number, don't divide - use as is
                        return $result;
                    }
                    return $result;
                }
            } elseif ($dotCount > 0 && $commaCount > 0) {
                // Mixed format - determine which is decimal
                if (strrpos($s, '.') > strrpos($s, ',')) {
                    // Dot comes after comma: 1,234.56
                    $s = str_replace(',', '', $s);
                } else {
                    // Comma comes after dot: 1.234,56
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                }
                return is_numeric($s) ? (float)$s : 0;
            } else {
                // Plain number - use as is (no division)
                return is_numeric($s) ? (float)$s : 0;
            }
        };

        $imported = 0;
        $debugLog = []; // Debug logging
        foreach ($dataRows as $r) {
            $row = array_values($r);
            // skip empty lines
            if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) continue;

            $tanggalRaw = $row[$map['tanggal']] ?? null;
            $tanggal = null;
            if ($tanggalRaw !== null && $tanggalRaw !== '') {
                if (is_numeric($tanggalRaw) && $tanggalRaw > 30000 && $tanggalRaw < 60000) {
                    // Excel serial date
                    try {
                        $dt = ExcelDate::excelToDateTimeObject((float)$tanggalRaw);
                        $tanggal = \Carbon\Carbon::instance($dt)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $tanggal = null;
                    }
                } else {
                    $normalized = str_replace('/', '-', (string)$tanggalRaw);
                    try {
                        $tanggal = \Carbon\Carbon::parse($normalized)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $tanggal = null;
                    }
                }
            }

            // Skip opening balance rows that have no values (we render them ourselves)
            // But import saldo rows that have actual penerimaan/pengeluaran values
            $uraianCell = trim((string)($row[$map['uraian']] ?? ''));
            $penerimaanRaw = $row[$map['penerimaan']] ?? 0;
            $pengeluaranRaw = $row[$map['pengeluaran']] ?? 0;
            $penerimaanValue = $num($penerimaanRaw);
            $pengeluaranValue = $num($pengeluaranRaw);
            
            // Debug log
            $debugLog[] = [
                'row_index' => count($debugLog),
                'tanggal' => $tanggal,
                'uraian' => $uraianCell,
                'penerimaan_raw' => $penerimaanRaw,
                'penerimaan_parsed' => $penerimaanValue,
                'pengeluaran_raw' => $pengeluaranRaw,
                'pengeluaran_parsed' => $pengeluaranValue,
                'is_saldo_row' => preg_match('/^Saldo\s+(Bank|Tunai)\s+Bulan\s+/i', $uraianCell) ? true : false,
                'will_skip' => (preg_match('/^Saldo\s+(Bank|Tunai)\s+Bulan\s+/i', $uraianCell) && $penerimaanValue == 0 && $pengeluaranValue == 0),
            ];
            
            if (preg_match('/^Saldo\s+(Bank|Tunai)\s+Bulan\s+/i', $uraianCell, $matches)) {
                // Debug: Log saldo values for opening balance entries
                $saldoRaw = $row[$map['saldo']] ?? '';
                $saldoProcessed = $num($row[$map['saldo']] ?? 0);
                \Log::info("Opening balance entry: {$uraianCell}", [
                    'saldo_raw' => $saldoRaw,
                    'saldo_processed' => $saldoProcessed,
                    'penerimaan_raw' => $row[$map['penerimaan']] ?? '',
                    'pengeluaran_raw' => $row[$map['pengeluaran']] ?? ''
                ]);

                // For opening balance entries, they should not affect the running balance
                // They are just informational entries showing what the previous period ended with
                $accountType = strtolower($matches[1]); // 'bank' or 'tunai'
                
                // Opening balance entries should be 0 for both penerimaan and pengeluaran
                // They don't change the balance, just document what it was
                $penerimaanValue = 0;
                $pengeluaranValue = 0;
                
                // For opening balance entries, use the saldo value from Excel as the starting point
                // This will be used to set the initial running saldo
                $saldoForEntry = $saldoProcessed;
            } else {
                // For regular entries, saldo column should be 0 as it will be calculated dynamically
                $saldoForEntry = 0;
            }

            \App\Models\BkuMasterEntry::create([
                'user_id' => $user->id,
                'tanggal' => $tanggal,
                'kode_kegiatan' => trim((string)($row[$map['kode kegiatan']] ?? '')),
                'kode_rekening' => trim((string)($row[$map['kode rekening']] ?? '')),
                'no_bukti' => trim((string)($row[$map['no. bukti']] ?? '')),
                'uraian' => $uraianCell,
                'penerimaan' => $penerimaanValue,
                'pengeluaran' => $pengeluaranValue,
                'saldo' => $saldoForEntry,
            ]);
            $imported++;
        }

        // Clean up temp file after import
        Storage::delete($relativePath);

        // After import, recalculate running saldo for this user
        $this->recalculateRunningSaldo($user->id);

        // Debug: Log to Laravel log for debugging
        // \Log::info('BKU Import Debug', ['imported' => $imported, 'debug_log' => $debugLog]);

        return redirect()->route('bku.master')->with('status', "Berhasil impor {$imported} baris Data Master.");
    }

    /**
     * Recalculate running saldo for all entries of a user
     */
    private function recalculateRunningSaldo($userId)
    {
        // Get all entries ordered by date and id
        $entries = \App\Models\BkuMasterEntry::where('user_id', $userId)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $runningSaldo = 0;
        $bankSaldoSet = false;

        foreach ($entries as $entry) {
            // Handle opening balance entries specially
            if (preg_match('/^Saldo\s+(Bank|Tunai)\s+Bulan\s+/i', $entry->uraian)) {
                // Opening balance entries should not affect running saldo calculation
                // They are just informational entries showing what the previous period ended with
                // Only set the running saldo from the FIRST Bank opening balance entry
                if (strpos($entry->uraian, 'Saldo Bank Bulan') !== false && !$bankSaldoSet) {
                    // Bank opening balance sets the starting point only once
                    $runningSaldo = $entry->saldo;
                    $bankSaldoSet = true;
                }
                // For opening balance entries, keep their original saldo value
                // Don't update it to current running balance
            } else {
                // Regular entries: add penerimaan, subtract pengeluaran
                $runningSaldo += $entry->penerimaan - $entry->pengeluaran;
                
                // Update the saldo column with running saldo
                $entry->update(['saldo' => $runningSaldo]);
            }
        }

        \Log::info("Recalculated running saldo for user {$userId}, final saldo: {$runningSaldo}");
    }

    /**
     * Handle BKU Tunai file upload and preview
     */
    public function tunaiUpload(Request $request)
    {
        \Log::info('TunaiUpload called', ['files' => $request->hasFile('file')]);
        
        $data = $request->validate([
            'file' => [
                'required', 
                'file', 
                'mimes:xlsx', 
                'mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'max:5120' // up to ~5MB
            ]
        ]);
        
        \Log::info('TunaiUpload validation passed');

        if (!$request->hasFile('file')) {
            return back()->withErrors(['file' => 'Silakan pilih file .xlsx terlebih dahulu.'])->withInput();
        }
        
        $uploaded = $request->file('file');
        if (!$uploaded || !$uploaded->isValid()) {
            return back()->withErrors(['file' => 'File tidak valid.'])->withInput();
        }

        $filePath = $uploaded->getPathname();
        if (empty($filePath) || !file_exists($filePath)) {
            return back()->withErrors(['file' => 'Gagal membaca file sementara yang diunggah.'])->withInput();
        }
        
        \Log::info('TunaiUpload file processed', ['path' => $filePath]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true); // preserve columns by letter
            \Log::info('TunaiUpload spreadsheet loaded', ['rows_count' => count($rows)]);
        } catch (\Throwable $e) {
            \Log::error('TunaiUpload spreadsheet error', ['error' => $e->getMessage()]);
            return back()->withErrors(['file' => 'Gagal membaca file: '.$e->getMessage()])->withInput();
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'File kosong atau tidak ada data.'])->withInput();
        }

        $headers = array_shift($rows); // First row as headers
        $preview = array_slice($rows, 0, 100); // Max 100 rows for preview

        // Store data in session with tunai prefix
        $token = Str::random(32);
        $sessionKey = 'tunai_upload_data_' . $token;
        session([$sessionKey => [
            'headers' => $headers,
            'rows' => $rows,
            'fileName' => $uploaded->getClientOriginalName(),
        ]]);
        
        \Log::info('TunaiUpload completed', ['token' => $token, 'headers_count' => count($headers)]);

        // Store in session with flash
        session()->flash('tunaiHeaders', $headers);
        session()->flash('tunaiPreview', $preview);
        session()->flash('tunaiFileName', $uploaded->getClientOriginalName());
        session()->flash('tunaiImportToken', $token);
        
        return back()->with([
            'tunaiHeaders' => $headers,
            'tunaiPreview' => $preview,
            'tunaiFileName' => $uploaded->getClientOriginalName(),
            'tunaiImportToken' => $token,
            'success' => 'File berhasil diupload! ' . count($headers) . ' kolom, ' . count($preview) . ' baris preview.'
        ]);
    }

    /**
     * Import BKU Tunai data to database
     */
    public function tunaiImport(Request $request)
    {
        $token = $request->input('token');
        if (empty($token)) {
            return back()->withErrors(['general' => 'Token impor tidak valid.'])->withInput();
        }

        $sessionKey = 'tunai_upload_data_' . $token;
        $uploadData = session($sessionKey);
        if (!$uploadData) {
            return back()->withErrors(['general' => 'Data impor tidak ditemukan atau sudah kedaluwarsa.'])->withInput();
        }

        $headers = $uploadData['headers'];
        $rows = $uploadData['rows'];
        $clearPeriod = $request->boolean('clear_period', false);

        $user = $request->user();
        $imported = 0;

        try {
            \DB::transaction(function() use ($headers, $rows, $user, $clearPeriod, &$imported) {
                $periodsToClear = [];

                foreach ($rows as $rowData) {
                    if (empty(array_filter($rowData))) continue; // Skip empty rows

                    $entry = $this->parseTunaiRow($headers, $rowData, $user->id);
                    if (!$entry) continue;

                    // Track periods for clearing
                    if ($entry['tanggal'] && $clearPeriod) {
                        $period = \Carbon\Carbon::parse($entry['tanggal'])->format('Y-m');
                        $periodsToClear[$period] = true;
                    }

                    $imported++;
                }

                // Clear existing data for the periods if requested
                if ($clearPeriod && !empty($periodsToClear)) {
                    foreach ($periodsToClear as $period => $value) {
                        [$year, $month] = explode('-', $period);
                        \App\Models\TunaiMasterEntry::where('user_id', $user->id)
                            ->whereYear('tanggal', $year)
                            ->whereMonth('tanggal', $month)
                            ->delete();
                    }
                }

                // Import data
                foreach ($rows as $rowData) {
                    if (empty(array_filter($rowData))) continue;

                    $entry = $this->parseTunaiRow($headers, $rowData, $user->id);
                    if (!$entry) continue;

                    \App\Models\TunaiMasterEntry::create($entry);
                }

                // Recalculate running saldo for tunai entries
                $this->recalculateTunaiRunningSaldo($user->id);
            });

            // Clear session data
            session()->forget($sessionKey);

            return back()->with('success', "Berhasil mengimpor {$imported} data BKU Tunai.");
        } catch (\Throwable $e) {
            \Log::error('Tunai import error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['general' => 'Terjadi kesalahan saat mengimpor: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Parse BKU Tunai row data
     */
    private function parseTunaiRow($headers, $rowData, $userId)
    {
        $entry = ['user_id' => $userId];

        foreach ($headers as $colIndex => $header) {
            $value = $rowData[$colIndex] ?? '';
            $headerLower = strtolower(trim($header ?? ''));

            if (strpos($headerLower, 'tanggal') !== false) {
                $entry['tanggal'] = $this->parseDate($value);
            } elseif (strpos($headerLower, 'kode kegiatan') !== false) {
                $entry['kode_kegiatan'] = $value;
            } elseif (strpos($headerLower, 'kode rekening') !== false) {
                $entry['kode_rekening'] = $value;
            } elseif (strpos($headerLower, 'no. bukti') !== false || strpos($headerLower, 'no bukti') !== false) {
                $entry['no_bukti'] = $value;
            } elseif (strpos($headerLower, 'uraian') !== false) {
                $entry['uraian'] = $value;
            } elseif (strpos($headerLower, 'penerimaan') !== false) {
                $entry['penerimaan'] = $this->parseNumber($value);
            } elseif (strpos($headerLower, 'pengeluaran') !== false) {
                $entry['pengeluaran'] = $this->parseNumber($value);
            } elseif (strpos($headerLower, 'saldo') !== false) {
                $entry['saldo'] = $this->parseNumber($value);
            }
        }

        // Validate required fields
        if (empty($entry['uraian'])) {
            return null;
        }

        return $entry;
    }

    /**
     * Recalculate running saldo for tunai entries
     */
    private function recalculateTunaiRunningSaldo($userId)
    {
        $entries = \App\Models\TunaiMasterEntry::where('user_id', $userId)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $runningSaldo = 0;

        foreach ($entries as $entry) {
            if (preg_match('/^Saldo\s+Tunai\s+Bulan\s+/i', $entry->uraian)) {
                // Tunai opening balance sets the starting point
                $runningSaldo = $entry->saldo;
            } else {
                // Regular entries: add penerimaan, subtract pengeluaran
                $runningSaldo += $entry->penerimaan - $entry->pengeluaran;
                $entry->update(['saldo' => $runningSaldo]);
            }
        }

        \Log::info("Recalculated tunai running saldo for user {$userId}, final saldo: {$runningSaldo}");
    }

    /**
     * Parse date value from Excel
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // If numeric, might be Excel serial date
        if (is_numeric($value) && $value > 30000 && $value < 60000) {
            try {
                $dt = ExcelDate::excelToDateTimeObject((float)$value);
                return \Carbon\Carbon::instance($dt)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        // Try to parse as string date
        $normalized = str_replace('/', '-', (string)$value);
        try {
            return \Carbon\Carbon::parse($normalized)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Parse number value with Indonesian/international formatting
     */
    private function parseNumber($value)
    {
        if (empty($value) || !is_scalar($value)) {
            return 0;
        }

        $s = trim((string)$value);
        if ($s === '' || $s === '-') {
            return 0;
        }

        // Remove currency symbols and spaces
        $s = preg_replace('/[^\d,.-]/', '', $s);
        
        if (empty($s)) {
            return 0;
        }

        $dotCount = substr_count($s, '.');
        $commaCount = substr_count($s, ',');

        if ($commaCount > 0 && $dotCount == 0) {
            // Format like 13,696,000 (comma as thousands separator)
            $s = str_replace(',', '', $s);
            $result = is_numeric($s) ? (float)$s : 0;
            return $result;
        } elseif ($dotCount > 0 && $commaCount == 0) {
            // Format like 427.840.000 or 300.000 (Indonesian thousands separator)
            if ($dotCount > 1) {
                // Multiple dots = thousands separator
                $s = str_replace('.', '', $s);
                return is_numeric($s) ? (float)$s : 0;
            } else {
                // Single dot = decimal point
                return is_numeric($s) ? (float)$s : 0;
            }
        } elseif ($dotCount > 0 && $commaCount > 0) {
            // Mixed format - determine which is decimal
            if (strrpos($s, '.') > strrpos($s, ',')) {
                // Dot comes after comma: 1,234.56
                $s = str_replace(',', '', $s);
            } else {
                // Comma comes after dot: 1.234,56
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            }
            return is_numeric($s) ? (float)$s : 0;
        } else {
            // Plain number
            return is_numeric($s) ? (float)$s : 0;
        }
    }

}
