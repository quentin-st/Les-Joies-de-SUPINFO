<?php
/**
 * Generate a RSS feed
 */

require_once(ROOT_DIR.'/ljs-includes.php');
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
            </item>
        <?php } ?>
</channel>
</rss>
