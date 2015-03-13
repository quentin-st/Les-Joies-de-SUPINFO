<?php
/**
 * Shared functions
 */

/**
 * Return a valid DBO instance
 */
function getDb() { return new PDO("mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD); }

/**
 * Ensure that the local installation is ready to go
 */
function ensureReady() {
    if (!file_exists('ljs-config.php')) {
        echo 'You forgot to create ljs-config.php from ljs-config-sample.php.';
        die();
    }

    if (!is_writable('uploads/')) {
        echo 'uploads/ directory isn\'t writable. Please fix this.';
        die();
    }
}

function getUrlReadyPermalink($catchPhrase) {
    $catchPhrase = str_replace(' ', '-', $catchPhrase);
    $catchPhrase = preg_replace('/[^A-Za-z0-9\-]/', '', $catchPhrase);
    $catchPhrase = strtolower($catchPhrase);
    return urlencode($catchPhrase);
}

function str_startsWith($haystack, $needle) {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function str_endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0)
        return false;

    return (substr($haystack, -$length) === $needle);
}

/**
 * Generates a random file name, unique in its directory
 * @param $directory String Destination directory with trailing slash
 * @param $length int File name length
 * @param $extension String File extension
 * @return String Unique random file name
 */
function generateRandomFileName($directory, $length, $extension) {
    $random = randomString($length);

    while (file_exists($directory . $random . $extension))
        $random = randomString($length);

    return $random . $extension;
}

function randomString($length) {
    return substr(str_shuffle(MD5(microtime())), 0, $length);
}
