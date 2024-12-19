<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Creates a MongoDB timestamp
 * @return mixed UTCDateTime object or integer timestamp
 */
function mongoTimestamp() {
    try {
        // Cek apakah class dan extension tersedia
        if (!extension_loaded('mongodb')) {
            return time() * 1000;
        }

        // Import class secara dinamis untuk menghindari error parsing
        $utcDateTime = 'MongoDB\BSON\UTCDateTime';
        if (class_exists($utcDateTime)) {
            return new $utcDateTime(time() * 1000);
        }

        return time() * 1000;
    } catch (Exception $e) {
        // Fallback jika terjadi error
        return time() * 1000;
    }
}

/**
 * Checks if MongoDB is properly configured
 * @return bool
 */
function isMongoDBAvailable() {
    return extension_loaded('mongodb') && 
           class_exists('MongoDB\BSON\UTCDateTime');
} 