<?php
// 1. Define paths safely
$langDir = __DIR__ . '/../lang/'; // Absolute path to language files

// 2. Available languages configuration
$GLOBALS['languages'] = [
    'en' => ['name' => 'English', 'dir' => 'ltr', 'file' => 'en.php'],
    'fr' => ['name' => 'Français', 'dir' => 'ltr', 'file' => 'fr.php'],
    'ar' => ['name' => 'العربية', 'dir' => 'rtl', 'file' => 'ar.php']
];

// 3. Verify language files exist
foreach ($GLOBALS['languages'] as $code => $lang) {
    if (!file_exists($langDir . $lang['file'])) {
        die("Missing language file: " . $langDir . $lang['file']);
    }
}

// 4. Initialize language (GET > SESSION > default)
if (isset($_GET['lang']) && isset($GLOBALS['languages'][$_GET['lang']])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$GLOBALS['selectedLang'] = $_SESSION['lang'] ?? 'en';

function t($key, $default = '') {
     // Check if translations are loaded
     if (!isset($GLOBALS['translations'])) {
         return $default;
     }
     
     // Return translation if exists, otherwise return default
     return $GLOBALS['translations'][$key] ?? $default;
 }$GLOBALS['translations'] = include $langDir . $GLOBALS['languages'][$GLOBALS['selectedLang']]['file'];
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

// Store in session for other files
$_SESSION['available_languages'] = $GLOBALS['languages'];
$_SESSION['available_countries'] = $GLOBALS['countries'];

// Load translations
$GLOBALS['translations'] = [];
$langFile = __DIR__ . '/../lang/' . $GLOBALS['selectedLang'] . '.php';
if (file_exists($langFile)) {
    $GLOBALS['translations'] = include $langFile;
} else {
    $GLOBALS['translations'] = include __DIR__ . '/../lang/en.php';
}
function addLangAndCountryToUrl($url)
{
    // Valeurs par défaut si non définies
    $lang = $_GET['lang'] ?? 'en';
    $country = $_GET['country'] ?? 'tn';

    // Vérifie si l'URL contient déjà des paramètres
    $separator = (parse_url($url, PHP_URL_QUERY) == NULL) ? '?' : '&';

    // Ajoute les paramètres
    return $url . $separator . 'lang=' . urlencode($lang) . '&country=' . urlencode($country);
}
