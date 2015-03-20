<?php
require_once('ljs-includes.php');

if (!isset($_GET['s']))
    header('Location: index.php');
$submitter = $_GET['s'];

// Pagination
$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
$pagesCount = getGifsCountBySubmitter($submitter);
if ($page < 1 || $page > $pagesCount)
    $page = 1;

$gifs = getGifsBySubmitter($submitter, $page);

global $pageName;   $pageName = $submitter;
include(ROOT_DIR.'/ljs-template/header.part.php');
?>
<div class="content">
    <h1>Contributions par <?= $submitter ?></h1>
    <? foreach ($gifs as $i => $gif) {
        echo $gif->getHTML();
        if ($i != count($gifs)-1)
            echo '<hr />';
    }

    if (count($gifs) == 0) {
        ?><div class="alert alert-info">Aucun gif n'a été publié par <?= $submitter ?></div><?
    }

    if ($pagesCount > 1) { ?>
        <div class="pagination">
            <? if ($page > 1) { ?>
                <a href="?p=<?= $page-1 ?>">&lt; Plus récents</a>
            <? } ?>
            Page <?= $page ?> / <?= $pagesCount ?>
            <? if ($page != $pagesCount) { ?>
                <a href="?p=<?= $page+1 ?>">Plus anciens &gt;</a>
            <? } ?>
        </div>
    <? } ?>
</div>
