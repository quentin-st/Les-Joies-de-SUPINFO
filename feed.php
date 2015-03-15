<?php
/**
 * Generate a RSS feed
 */

require_once('ljs-includes.php');
?>
<rss version="2.0">
    <channel>
        <title>Les Joies de Supinfo</title>
        <link><?php echo WEBSITE_URL ?></link>
        <description></description>
        <language>fr</language>
        <?php foreach (getGifs() as $gif) { ?>
            <item>
                <title><?php echo $gif->catchPhrase ?></title>
                <link><?php echo $gif->getPermalink() ?></link>
                <description><?php echo $gif->getGifUrl() ?></description>
                <pubDate><?php echo $gif->publicationDate->format('Y-m-d H:i:s') ?></pubDate>
            </item>
        <?php } ?>
</channel>
</rss>
