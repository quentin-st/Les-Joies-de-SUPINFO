<?php
/**
 * This cron is run twice twice daily
 * The number of gifs posted each time is set in config (CRON_MAX_ITEMS_PER_PUBLICATION)
 */
require_once('../ljs-includes.php');

$acceptedGifs = getGifs(-1, GifState::ACCEPTED);

$reportString = PHP_EOL
              . 'Publish Cron - ' . (new DateTime())->format('Y-m-d h:m:s') . PHP_EOL;

$to = min(CRON_MAX_ITEMS_PER_PUBLICATION, count($acceptedGifs));
for ($i=0; $i<$to; $i++) {
    $gif = $acceptedGifs[$i];

    // Update GifState
    $gif->gifState = GifState::PUBLISHED;
    $gif->publishDate = new DateTime();
    updateGif($gif);

    $reportString .= '  - ' . $gif->catchPhrase . PHP_EOL;

    // Post to social networks
    // TODO
}

file_put_contents('publishCron_report.txt', $reportString, FILE_APPEND | LOCK_EX);
