<?php
require_once('ljs-includes.php');

// Pagination
$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
$pagesCount = getPagesCount();
if ($page < 1 || $page > $pagesCount)
    $page = 1;

// Set pageName and homePage for header.part.php template (empty for index)
global $pageName;   $pageName = '';
global $homePage;   $homePage = $page == 1;

include(ROOT_DIR.'/ljs-template/header.part.php');
?>
<div class="content">
    <? foreach (getGifs($page) as $gif) {
        echo $gif->getHTML();
    } ?>

    <? if ($pagesCount > 1) { ?>
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

<? include(ROOT_DIR.'/ljs-template/facebook-sdk.part.php'); ?>
<? include(ROOT_DIR.'/ljs-template/footer.part.php'); ?>
