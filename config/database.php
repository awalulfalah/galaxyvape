<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\Exception as MongoDBException;

try {
    $mongoClient = new Client("mongodb://localhost:27017");
    $database = $mongoClient->galaxyvape;
} catch (MongoDBException $e) {
    die("Error connecting to MongoDB: " . $e->getMessage());
} 