<?php
// server/session.php
session_start();

// Available languages configuration
$GLOBALS['languages'] = [
    'en' => ['name' => 'English', 'dir' => 'ltr'],
    'fr' => ['name' => 'Français', 'dir' => 'ltr'],
    'ar' => ['name' => 'العربية', 'dir' => 'rtl']
];

// Available countries configuration
$GLOBALS['countries'] = [
    'TN' => 'Tunisia',
    'DZ' => 'Algeria',
    'MA' => 'Morocco',
    'LY' => 'Libya',
    'EG' => 'Egypt',
    'MR' => 'Mauritania'
];

// Initialize language (GET > SESSION > default)
if (isset($_GET['lang']) && isset($GLOBALS['languages'][$_GET['lang']])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$GLOBALS['selectedLang'] = $_SESSION['lang'] ?? 'en';

// Initialize country (GET > SESSION > default)
if (isset($_GET['country']) && isset($GLOBALS['countries'][$_GET['country']])) {
    $_SESSION['country'] = $_GET['country'];
}
$GLOBALS['selectedCountry'] = $_SESSION['country'] ?? 'TN';

// Load translations
$GLOBALS['translations'] = [];
$langFile = __DIR__ . '/../lang/' . $GLOBALS['selectedLang'] . '.php';
if (file_exists($langFile)) {
    $GLOBALS['translations'] = include $langFile;
} else {
    $GLOBALS['translations'] = include __DIR__ . '/../lang/en.php';
}

// Translation helper function
function t($key, $default = '') {
    return $GLOBALS['translations'][$key] ?? $default;
}