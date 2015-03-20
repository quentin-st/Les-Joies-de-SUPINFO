<?php

function getFacebookLikes() {
    $gifs = getGifs(-1, GifState::PUBLISHED);

    // Build api call url
    $urls = '';
    foreach ($gifs as $gif)
        $urls .= $gif->getPermalink().',';

    $apiUrl = 'http://api.facebook.com/restserver.php?method=links.getStats&urls=' . $urls;
    $result = file_get_contents($apiUrl);
    $xmlRes = new SimpleXMLElement($result);

    $likes = [];
    for ($i=0; $i<count($xmlRes[0]); $i++) {
        $url = $xmlRes->link_stat[$i]->url;
        $likesCount = (int)$xmlRes->link_stat[$i]->like_count;

        $likes[] = [ 'url' => $url, 'likes' => $likesCount, 'gif' => findGif($gifs, $url) ];
    }

    // Sort array
    usort($likes, function($a, $b) {
        return $b['likes'] - $a['likes'];
    });

    return $likes;
}

function findGif($list, $permalink) {
    foreach ($list as $listItem) {
        if ($listItem->getPermalink() == $permalink)
            return $listItem;
    }
    return null;
}
