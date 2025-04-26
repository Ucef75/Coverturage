<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ForsaDrive - Carpooling Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/interface.css">
</head>
<body>
<?php
function t($key, $default = '') {
    static $translations = [
        'copyright' => '&copy; 2023 ForsaDrive | All Rights Reserved',
        'created_by' => 'Created by'
        // Add other translations here
    ];
    return $translations[$key] ?? $default;
}