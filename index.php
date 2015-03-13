<?php
require_once('ljs-includes.php');

/*
 * This is the main page of the project
 */

// Set pageName for header.part.php template (empty for index)
global $pageName;
$pageName = '';

// Pagination
$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
$pagesCount = getPagesCount();
if ($page < 1 || $page > $pagesCount)
    $page = 1;

include('ljs-template/header.part.php');
?>
<div class="content">
    <?php foreach (getGifs($page) as $gif) {
        echo $gif->getHTML();
    } ?>

    <div class="pagination">
        <?php if ($page > 1) { ?>
        <a href="?p=<?php echo $page-1 ?>">&lt; Plus récents</a>
        <?php } ?>
        Page <?php echo $page ?> / <?php echo $pagesCount ?>
        <?php if ($page != $pagesCount) { ?>
        <a href="?p=<?php echo $page+1 ?>">Plus anciens &gt;</a>
        <?php } ?>
    </div>
</div>

<?php include('ljs-template/facebook-sdk.part.php'); ?>
<?php include('ljs-template/footer.part.php'); ?>
