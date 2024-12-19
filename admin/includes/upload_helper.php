<?php
function ensureUploadDirectoryExists($path) {
    if (!file_exists($path)) {
        if (!mkdir($path, 0755, true)) {
            throw new Exception("Gagal membuat direktori upload");
        }
        
        // Buat .htaccess untuk mengamankan folder uploads
        $htaccess = $path . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Options -Indexes\nAllow from all");
        }
    }
}

function uploadImage($file, $targetDir) {
    if (!isset($file) || !is_array($file) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error: " . getUploadErrorMessage($file['error'] ?? -1));
    }
    
    // Validasi ukuran file (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Ukuran file maksimal 5MB");
    }
    
    // Validasi tipe file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Tipe file tidak diizinkan. Gunakan format: JPG, PNG, GIF, atau WEBP");
    }
    
    // Pastikan targetDir diakhiri dengan slash
    $targetDir = rtrim($targetDir, '/') . '/';
    
    // Pastikan direktori ada
    ensureUploadDirectoryExists($targetDir);
    
    // Generate nama file unik
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $targetPath = $targetDir . $fileName;
    
    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception("Gagal mengupload file");
    }
    
    return $fileName; // Hanya return nama file saja
}

function getUploadErrorMessage($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return "File terlalu besar (melebihi upload_max_filesize)";
        case UPLOAD_ERR_FORM_SIZE:
            return "File terlalu besar (melebihi MAX_FILE_SIZE)";
        case UPLOAD_ERR_PARTIAL:
            return "File hanya terupload sebagian";
        case UPLOAD_ERR_NO_FILE:
            return "Tidak ada file yang diupload";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Folder temporary tidak ditemukan";
        case UPLOAD_ERR_CANT_WRITE:
            return "Gagal menulis file";
        case UPLOAD_ERR_EXTENSION:
            return "Upload dihentikan oleh ekstensi";
        default:
            return "Error upload tidak diketahui";
    }
}
 