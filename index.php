<?php
require_once('ljs-includes.php');

/*
 * This is the main page of the project
 */

// Set pageName for header.part.php template (empty for index)
global $pageName;
$pageName = '';

include('ljs-template/header.part.php');
?>
<div class="content">
    <?php foreach (getGifs() as $gif) {
        echo $gif->getHTML();
    } ?>
</div>

<?php include('ljs-template/facebook-sdk.part.php'); ?>
<?php include('ljs-template/footer.part.php'); ?>
