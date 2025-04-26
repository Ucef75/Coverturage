<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'forsadrive');

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
spl_autoload_register(function ($class_name) {
     $file = __DIR__ . '/../classes/' . strtolower($class_name) . '.php';
     if (file_exists($file)) {
         require_once $file;
     }
 });
?>