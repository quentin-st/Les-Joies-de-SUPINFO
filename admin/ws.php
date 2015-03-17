<?php
/**
 * Admin WebService
 * Ajax calls from admin ends up here
 */
require_once('../ljs-includes.php');
require_once('ws.lib.php');

/* Request integrity check */
if (!isset($_POST['api_key']))
    finishOnError('missing_api_key');

if (!checkApiKey($_POST['api_key']))
    finishOnError('wrong_api_key');

if (!isset($_POST['action']))
    finishOnError('missing_action');


// Create result
$result = [ 'success' => true ];

/* Execute request */
switch ($_POST['action']) {
    default:
        finishOnError('unknown_action');
        break;
}

// Print result (to be json_decoded by client)
echo json_encode($result);



function finishOnError($errorText) {
    echo json_encode([
        'success' => false,
        'error' => $errorText
    ]);
    die();
}

function checkApiKey($apiKey) {
    return true;
}
