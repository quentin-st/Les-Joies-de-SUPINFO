<?php
/**
 * This file ensures that everything is ready to make magic work
 */

define('ROOT_DIR', dirname(__FILE__));

require_once(ROOT_DIR.'/ljs-config.php');
require_once(ROOT_DIR.'/ljs-functions.php');

// Enable errors logging on a dev environment
if (!PRODUCTION) {
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
}

// Check that everything is set
// If not, let's die()
ensureReady();

// Import model
require_once(ROOT_DIR.'/ljs-model/Gif.php');
require_once(ROOT_DIR.'/ljs-model/User.php');

// Import controllers
require_once(ROOT_DIR.'/ljs-controller/GifsController.php');
require_once(ROOT_DIR.'/ljs-controller/UsersController.php');
