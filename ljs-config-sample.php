<?php
/**
 * This is a sample file to help you prepare a local copy of this project.
 * Be careful: some changed may be done in this file. Please be sure to reflect
 *  those changes in your ljs-config.php file.
 */

/**
 * Defines if the scripts are run on a production server
 * Do not forget to switch to true when uploading this file
 *  on a production server!
 */
define('PRODUCTION', false);

/**
 * Website URL, with trailing slash
 */
define('WEBSITE_URL', 'http://localhost/');

/**
 * Defines if we use pretty urls for single gif page
 * Should be set to false with WAMP on Windows
 */
define('PRETTY_URLS', true);

/**
 * Database connection parameters
 */
define('MYSQL_HOST', 'localhost');
define('MYSQL_DATABASE', '');
define('MYSQL_USER', '');
define('MYSQL_PASSWORD', '');

/**
 * Pagination: how many gifs should be displayed in one page
 *  (default: 5)
 */
define('GIFS_PER_PAGE', 5);

/**
 * Generated file name length for new gifs files
 */
define('RANDOM_FILE_NAME_LENGTH', 6);

/**
 * Giphy API key
 * You can temporarily use public beta key (dc6zaTOxFJmzC)
 */
define ('GIPHY_API_KEY', 'dc6zaTOxFJmzC');

/**
 * Giphy gifs limit
 * How many gifs are fetched from Giphy API
 */
define('GIPHY_GIFS_LIMIT', 24);

/**
 * Max gifs posted each time the cron is run
 */
define('CRON_MAX_ITEMS_PER_PUBLICATION', 2);
