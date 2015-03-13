<?php
/**
 * Prints the server configuration
 */

require_once('ljs-includes.php');

if (PRODUCTION)
    die();

phpinfo();
