<?php
require_once('ljs-includes.php');

include(ROOT_DIR.'/ljs-template/header.part.php');

?>
<div class="content">
    <h1>Top contributeurs</h1>
    <ol>
        <? foreach (getTopContributors() as $topContributor) ?>
        <li><?= $topContributor['contributor'] ?> (<?= $topContributor['gifsCount'] ?> gif(s))</li>
    </ol>

</div>
<? include(ROOT_DIR.'/ljs-template/footer.part.php'); ?>
