<?php
require_once '../includes/session.php';

// Hapus semua data session
session_destroy();

// Redirect ke halaman login
header('Location: /pages/login.php');
exit;
?> 