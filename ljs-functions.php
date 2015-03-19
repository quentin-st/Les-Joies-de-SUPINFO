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
    if (!file_exists(ROOT_DIR.'/ljs-config.php')) {
        echo 'You forgot to create ljs-config.php from ljs-config-sample.php.';
        die();
    }

    if (!is_writable(ROOT_DIR.'/uploads/')) {
        echo 'uploads/ directory isn\'t writable. Please fix this.';
        die();
    }

    if (ini_get('file_uploads') != 1) {
        echo 'File uploads are\'nt enabled on this server. Please fix this.';
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

function getRelativeTime($date) {
    $time = time() - strtotime($date);

    if ($time > 0)
        $when = "il y a";
    else if ($time < 0)
        $when = "dans environ";
    else
        return "il y a moins d'une seconde";

    $time = abs($time);

    $times = array( 31104000 =>  'an{s}',       // 12 * 30 * 24 * 60 * 60 secondes
        2592000  =>  'mois',        // 30 * 24 * 60 * 60 secondes
        86400    =>  'jour{s}',     // 24 * 60 * 60 secondes
        3600     =>  'heure{s}',    // 60 * 60 secondes
        60       =>  'minute{s}',   // 60 secondes
        1        =>  'seconde{s}'); // 1 seconde

    foreach ($times as $seconds => $unit) {
        $delta = round($time / $seconds);

        if ($delta >= 1) {
            if ($delta == 1)
                $unit = str_replace('{s}', '', $unit);
            else
                $unit = str_replace('{s}', 's', $unit);

            return $when." ".$delta." ".$unit;
        }
    }
}
