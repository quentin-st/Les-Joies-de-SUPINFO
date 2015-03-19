<?php
/**
 * Generate a RSS feed
 */

// Set RSS XML header
header("Content-Type: application/rss+xml; charset=UTF-8");

require_once('ljs-includes.php');
echo '<?xml version="1.0" encoding="ISO-8859-1"?>'; // We have to echo it because of short tags (<?)
?>

<rss version="2.0">
    <channel>
        <title>Les Joies de Supinfo</title>
        <link><?= WEBSITE_URL ?></link>
        <description></description>
        <language>fr</language>
        <? foreach (getGifs() as $gif) { ?>
            <item>
                <title><?= $gif->catchPhrase ?></title>
                <link><?= $gif->getPermalink() ?></link>
                <description><?= $gif->getGifUrl() ?></description>
                <pubDate><?= $gif->publishDate->format('Y-m-d H:i:s') ?></pubDate>
            </item>
        <? } ?>
</channel>
</rss>
