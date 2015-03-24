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
    case 'change_gif_status':
        checkParameters('gif_id', 'new_gif_state', 'caption');

        $gif = getGif($_POST['gif_id']);

        if ($gif == null)
            finishOnError('unkown_gif');

        $gif->catchPhrase = $_POST['caption'];

        switch ($_POST['new_gif_state']) {
            case 'submitted': $gif->gifStatus = GifState::SUBMITTED; break;
            case 'accepted': $gif->gifStatus = GifState::ACCEPTED; break;
            case 'refused': $gif->gifStatus = GifState::REFUSED; break;
            case 'published': $gif->gifStatus = GifState::PUBLISHED; $gif->publishDate = new DateTime();break;
        }

        updateGif($gif);

        break;

    case 'change_report_status':
        checkParameters('gif_id');

        $gif = getGif($_POST['gif_id']);

        if ($gif == null)
            finishOnError('unkown_gif');

        $gif->reportStatus = ReportState::IGNORED;

        updateGif($gif);

        break;


    case 'delete_gif':
        checkParameters('gif_id');

        $gif = getGif($_POST['gif_id']);

        if ($gif == null)
            finishOnError('unknown gif');

        deleteGif($gif);


        break;

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

function checkParameters() {
    $arg_list = func_get_args();
    foreach ($arg_list as $arg) {
        if (!array_key_exists($arg, $_POST))
            finishOnError('missing_parameter:'.$arg);
    }
}

function checkApiKey($apiKey) {
    return true;
}
