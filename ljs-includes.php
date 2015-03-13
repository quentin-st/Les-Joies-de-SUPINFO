<?php
/**
 * This file ensures that everything is ready to make magic work
 */

require_once('ljs-config.php');
require_once('ljs-functions.php');

// Check that everything is set
// If not, let's die()
ensureReady();

// Import model
require_once('ljs-model/Gif.php');
require_once('ljs-model/User.php');

// Import controllers
require_once('ljs-controller/GifsController.php');
require_once('ljs-controller/UsersController.php');
