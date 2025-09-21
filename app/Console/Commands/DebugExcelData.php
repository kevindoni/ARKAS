<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class DebugExcelData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:excel {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug Excel file content for BKU import';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        
        if (!Storage::exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $fullPath = Storage::path($file);
        
        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $allRows = $sheet->toArray(null, true, true, true);
            
            $this->info("Total rows: " . count($allRows));
            $this->info("First 10 rows:");
            
            $rowCount = 0;
            foreach ($allRows as $index => $row) {
                if ($rowCount > 10) break;
                
                $this->line("Row {$index}: " . json_encode(array_values($row), JSON_UNESCAPED_UNICODE));
                $rowCount++;
            }
            
            // Focus on specific problematic rows
            $this->info("\n=== Looking for Saldo Bank and BOSP rows ===");
            foreach ($allRows as $index => $row) {
                $rowData = array_values($row);
                $uraian = isset($rowData[4]) ? trim($rowData[4]) : '';
                
                if (stripos($uraian, 'Saldo Bank') !== false || 
                    stripos($uraian, 'BOSP') !== false ||
                    stripos($uraian, 'Registrasi') !== false) {
                    $this->line("Row {$index}: " . json_encode($rowData, JSON_UNESCAPED_UNICODE));
                }
            }
            
        } catch (\Throwable $e) {
            $this->error("Failed to read Excel: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
