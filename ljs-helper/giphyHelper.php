<?php
require_once('../ljs-includes.php');

/**
 * Gets trending gifs from Giphy API
 *  (Giphy des idées de génie)
 * This script is called within an Ajax request
 *  from the submission page
 */

if (isset($_POST['action']) && $_POST['action'] == 'getTrendingGifs') {
    echo json_encode(getTrendingGifs());
}

function getTrendingGifs() {
    $url = 'http://api.giphy.com/v1/gifs/trending?api_key=' . GIPHY_API_KEY . '&limit=' . GIPHY_GIFS_LIMIT;
    $apiResult = file_get_contents($url);

    $res = array();

    if ($apiResult === false) {
        $res['success'] = false;
        return json_encode($res);
    }

    $json = json_decode($apiResult, true);
    $res['gifs'] = array();

    foreach ($json['data'] as $giphyGif) {
        $res['gifs'][] = [
            'image' => $giphyGif['images']['downsized']['url'],
            'url' => $giphyGif['bitly_url']
        ];
    }

    $res['success'] = true;

    return $res;
}
