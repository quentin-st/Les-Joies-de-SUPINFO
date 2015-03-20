<?php
require_once('ljs-includes.php');
require_once('ljs-helper/facebookHelper.php');

global $pageName;   $pageName = 'Top gifs';
include(ROOT_DIR.'/ljs-template/header.part.php');

?>
<div class="content topSubmitters">
    <h1>Top gifs</h1>
    <ol>
        <?
        $likes = getFacebookLikes();

        for ($i=0; $i<min(15, count($likes)); $i++) {
            echo $likes[$i]['gif']->getHTML();
            if ($i != count($likes)-1)
                echo '<hr />';
        } ?>
    </ol>
</div>
<? include(ROOT_DIR.'/ljs-template/facebook-sdk.part.php'); ?>
<? include(ROOT_DIR.'/ljs-template/footer.part.php'); ?>
