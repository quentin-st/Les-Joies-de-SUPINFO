<?php
/**
 * Shared functions
 */

/**
 * Return a valid DBO instance
 */
function getDb() { return null; }

/**
 * Ensure that the local installation is ready to go
 */
function ensureReady() {
    if (!file_exists('ljs-config.php')) {
        echo 'You forgot to create ljs-config.php from ljs-config-sample.php.';
        die();
    }
}

function getUrlReadyPermalink($catchPhrase) {
    $catchPhrase = str_replace(' ', '-', $catchPhrase);
    $catchPhrase = preg_replace('/[^A-Za-z0-9\-]/', '', $catchPhrase);
    $catchPhrase = strtolower($catchPhrase);
    return urlencode($catchPhrase);
}
