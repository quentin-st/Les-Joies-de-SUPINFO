<?php
require_once('ljs-includes.php');

global $pageName;   $pageName = 'Top contributeurs';
include(ROOT_DIR.'/ljs-template/header.part.php');

?>
<div class="content topSubmitters">
    <h1>Top contributeurs</h1>
    <ol>
        <? foreach (getTopContributors() as $topCont) { ?>
        <li>
            <a href="submitter.php?s=<?= $topCont['contributor'] ?>"><?= $topCont['contributor'] ?></a>
            <span> - <?= $topCont['gifsCount'] ?> gif<?= intval($topCont['gifsCount']) > 1 ? 's' : '' ?></span>
        </li>
        <? } ?>
    </ol>
</div>
<? include(ROOT_DIR.'/ljs-template/footer.part.php'); ?>
