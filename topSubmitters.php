<?php
require_once('ljs-includes.php');

include(ROOT_DIR.'/ljs-template/header.part.php');

?>
<div class="content topSubmitters">
    <h1>Top contributeurs</h1>
    <ol>
        <? foreach (getTopContributors() as $topCont) { ?>
        <li><?= $topCont['contributor'] ?> <span> - <?= $topCont['gifsCount'] ?> gif<?= intval($topCont['gifsCount']) > 1 ? 's' : '' ?></span></li>
        <? } ?>
    </ol>
</div>
<? include(ROOT_DIR.'/ljs-template/footer.part.php'); ?>
