<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BKU Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for BKU (Book of Cash Receipt and Disbursement)
    |
    */

    // Display scaling factor - multiply by this to show in rupiah format
    'display_scale_factor' => 1000,
    
    // Parsing threshold - values >= this will be divided by scale_factor during import
    'parse_threshold' => 1000,
    
    // Default pagination per page
    'default_per_page' => 25,
    
    // Limit for print all functionality
    'print_all_limit' => 1000000,
    
    // Excluded entries from totals calculation
    'excluded_entries' => [
        'Bunga Bank',
        'Pajak Bunga'
    ],
    
    // Note: Tax keywords are now dynamically extracted from bku_master_entries table
    // See BKUController::getTaxKeywordsFromDatabase() method
    
    // Opening balance patterns
    'opening_balance_patterns' => [
        'saldo_bank' => '/^Saldo\s+Bank\s+Bulan\s+/i',
        'saldo_tunai' => '/^Saldo\s+Tunai\s+Bulan\s+/i',
        'saldo_general' => '/^Saldo\s+(Bank|Tunai)\s+Bulan\s+/i',
    ],
    
    // Column mapping for Excel import
    'excel_column_mapping' => [
        'tanggal' => 'tanggal',
        'kode kegiatan' => 'kode kegiatan', 
        'kode rekening' => 'kode rekening',
        'no. bukti' => 'no. bukti',
        'uraian' => 'uraian',
        'penerimaan' => 'penerimaan',
        'pengeluaran' => 'pengeluaran', 
        'saldo' => 'saldo'
    ]
];