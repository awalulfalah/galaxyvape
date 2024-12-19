<?php
// Status pesanan yang tersedia
const ORDER_STATUSES = [
    'pending' => 'Menunggu Pembayaran',
    'waiting_confirmation' => 'Menunggu Konfirmasi',
    'paid' => 'Sudah Dibayar', 
    'processing' => 'Diproses',
    'shipped' => 'Dikirim',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

// Status badge classes untuk tampilan
const STATUS_BADGES = [
    'pending' => 'warning',
    'waiting_confirmation' => 'info',
    'paid' => 'primary',
    'processing' => 'primary', 
    'shipped' => 'info',
    'completed' => 'success',
    'cancelled' => 'danger'
];

// Alur status yang valid
const VALID_STATUS_TRANSITIONS = [
    'pending' => ['waiting_confirmation', 'cancelled'],
    'waiting_confirmation' => ['paid', 'cancelled'],
    'paid' => ['processing', 'cancelled'],
    'processing' => ['shipped', 'cancelled'],
    'shipped' => ['completed', 'cancelled'],
    'completed' => [],
    'cancelled' => []
]; 