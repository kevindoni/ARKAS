<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cetak BKU Umum</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #333; padding: 4px 6px; }
    th { background: #f0f0f0; }
    .text-right { text-align: right; }
    .no-border td { border: 0; }
    @media print { @page { size: A4; margin: 12mm; } }
  </style>
</head>
<body>
  <h3 style="margin:0 0 8px;">Buku Kas Umum</h3>
  @if($month && $year)
    <div style="margin-bottom:10px;">Periode: {{ str_pad($month,2,'0',STR_PAD_LEFT) }}/{{ $year }}</div>
  @endif

  <table>
    <thead>
      <tr>
        <th style="width: 14%">Tanggal</th>
        <th style="width: 14%">Kode Kegiatan</th>
        <th style="width: 14%">Kode Rekening</th>
        <th style="width: 12%">No. Bukti</th>
        <th>Uraian</th>
        <th style="width: 12%" class="text-right">Penerimaan</th>
        <th style="width: 12%" class="text-right">Pengeluaran</th>
        <th style="width: 12%" class="text-right">Saldo</th>
      </tr>
    </thead>
    <tbody>
  @php $running = $saldoAwal ?? 0; @endphp
      @if(($month ?? null) && ($year ?? null))
        @php $prevLabel = 'Bulan '.\Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->locale('id')->translatedFormat('F Y'); @endphp
        <tr>
          <td>{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('d-m-Y') }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td>Saldo Bank {{ $prevLabel }}</td>
          <td class="text-right">{{ number_format(max($saldoAwal * $displayScale ?? 0, 0), 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format(0, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($running * $displayScale, 0, ',', '.') }}</td>
        </tr>
        <tr>
          <td>{{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('d-m-Y') }}</td>
          <td></td>
          <td></td>
          <td></td>
          <td>Saldo Tunai {{ $prevLabel }}</td>
          <td class="text-right">{{ number_format(0, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format(0, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format($running * $displayScale, 0, ',', '.') }}</td>
        </tr>
      @endif
      @foreach($entries as $e)
        <tr>
          <td>{{ optional($e->tanggal)->format('d-m-Y') }}</td>
          <td>{{ $e->kode_kegiatan }}</td>
          <td>{{ $e->kode_rekening }}</td>
          <td>{{ $e->no_bukti }}</td>
          <td>{{ $e->uraian }}</td>
          <td class="text-right">{{ number_format((float)$e->penerimaan * $displayScale, 0, ',', '.') }}</td>
          <td class="text-right">{{ number_format((float)$e->pengeluaran * $displayScale, 0, ',', '.') }}</td>
          @php $running += (float)$e->penerimaan - (float)$e->pengeluaran; @endphp
          <td class="text-right">{{ number_format($running * $displayScale, 0, ',', '.') }}</td>
        </tr>
      @endforeach
      <tr>
        <th colspan="5" class="text-right">Jumlah</th>
        <th class="text-right">
          @if(($month ?? null) && ($year ?? null))
            {{ number_format(($totalIn ?? 0), 0, ',', '.') }}
          @else
            {{ number_format(($totalIn ?? 0), 0, ',', '.') }}
          @endif
        </th>
        <th class="text-right">
          @if(($month ?? null) && ($year ?? null))
            {{ number_format(($totalOut ?? 0), 0, ',', '.') }}
          @else
            {{ number_format(($totalOut ?? 0), 0, ',', '.') }}
          @endif
        </th>
        <th class="text-right">{{ number_format((($totalIn ?? 0) - ($totalOut ?? 0)), 0, ',', '.') }}</th>
      </tr>
    </tbody>
  </table>

  @if(($month ?? null) && ($year ?? null))
    @php
      $totalSaldo = ($totalIn ?? 0) - ($totalOut ?? 0);
      
      // Get accurate saldo breakdown from controller data (already scaled properly)
      $saldoTunai = ($totals['saldo_tunai'] ?? 0) * $displayScale;
      $saldoBank = ($totals['saldo_bank'] ?? 0) * $displayScale;
      
      // If no saldo_bank data from controller, calculate as difference
      if ($saldoBank <= 0 && isset($totals['saldo_bank'])) {
          $saldoBank = $totals['saldo_bank'] * $displayScale;
      } else if ($saldoBank <= 0) {
          $saldoBank = $totalSaldo - $saldoTunai;
      }
      
      $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
      ];
      $monthName = $monthNames[$month] ?? 'Januari';
      
      // Get last day of month
      $lastDay = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->day;
      $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      $dayName = $dayNames[\Carbon\Carbon::createFromDate($year, $month, $lastDay)->dayOfWeek];
    @endphp

    <div style="margin-top:30px; font-size:12px; line-height:1.6;">
      <p style="margin:0 0 12px 0; text-align:justify;">
        Pada hari ini <strong>{{ $dayName }} {{ $lastDay }} {{ $monthName }} {{ $year }}</strong> Buku Kas Umum Ditutup dengan keadaan/posisi buku sebagai berikut :
      </p>
      
      <table style="width:100%; border-collapse:collapse; margin:15px 0;">
        <tr>
          <td style="padding:8px 15px; background-color:#f5f5f5; border:1px solid #ddd; font-weight:bold; width:70%;">
            Saldo Buku Kas Umum
          </td>
          <td style="padding:8px 15px; background-color:#f5f5f5; border:1px solid #ddd; text-align:right; font-weight:bold; width:30%;">
            Rp {{ number_format($totalSaldo, 0, ',', '.') }}
          </td>
        </tr>
      </table>
      
      <p style="margin:15px 0 8px 0; font-weight:bold;">Terdiri Dari :</p>
      
      <table style="width:100%; border-collapse:collapse; margin:10px 0 20px 0;">
        <tr>
          <td style="padding:6px 15px; border:1px solid #ddd; width:70%;">- Saldo Bank</td>
          <td style="padding:6px 15px; border:1px solid #ddd; text-align:right; width:30%;">
            Rp {{ number_format($saldoBank, 0, ',', '.') }}
          </td>
        </tr>
        <tr>
          <td style="padding:6px 15px; border:1px solid #ddd;">- Saldo Kas Tunai</td>
          <td style="padding:6px 15px; border:1px solid #ddd; text-align:right;">
            Rp {{ number_format($saldoTunai, 0, ',', '.') }}
          </td>
        </tr>
        <tr style="background-color:#f0f0f0;">
          <td style="padding:8px 15px; border:1px solid #ddd; font-weight:bold;">Jumlah</td>
          <td style="padding:8px 15px; border:1px solid #ddd; text-align:right; font-weight:bold;">
            Rp {{ number_format($totalSaldo, 0, ',', '.') }}
          </td>
        </tr>
      </table>
    </div>
  @endif

  <div style="margin-top:35px; display:flex; justify-content:space-between; font-size:12px;">
    <div style="width:45%; text-align:center;">
      <p style="margin:0 0 5px 0;">Menyetujui,</p>
      <p style="margin:0 0 60px 0; font-weight:bold;">Kepala Sekolah</p>
      <p style="margin:0; border-bottom:1px solid #000; display:inline-block; min-width:150px; padding-bottom:2px;"></p>
      <p style="margin:5px 0 0 0; font-size:11px;">NIP.</p>
    </div>
    <div style="width:45%; text-align:center;">
      @if(($month ?? null) && ($year ?? null))
        <p style="margin:0 0 5px 0;">Kec. Kedu, {{ $lastDay }} {{ $monthName }} {{ $year }}</p>
      @else
        <p style="margin:0 0 5px 0;">Kec. Kedu, {{ now()->translatedFormat('d F Y') }}</p>
      @endif
      <p style="margin:0 0 60px 0; font-weight:bold;">Bendahara,</p>
      <p style="margin:0; border-bottom:1px solid #000; display:inline-block; min-width:150px; padding-bottom:2px; font-weight:bold;">{{ auth()->user()->name }}</p>
      <p style="margin:5px 0 0 0; font-size:11px;">NIP.</p>
    </div>
  </div>

  <script>window.print();</script>
</body>
</html>
