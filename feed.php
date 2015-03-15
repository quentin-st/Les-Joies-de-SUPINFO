<?php
/**
 * Generate a RSS feed
 */

require_once('ljs-includes.php');
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
                <pubDate><?= $gif->publicationDate->format('Y-m-d H:i:s') ?></pubDate>
            </item>
        <? } ?>
</channel>
</rss>
