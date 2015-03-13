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

<div class="container">
    <div class="header">
        <h1>Les Joies de Supinfo</h1>
    </div>
    <div class="content">
        <?php foreach (getGifs() as $gif) { ?>
            <div class="gifItem">
                <a href="<?php echo $gif->getPermalink() ?>">
                    <img src="<?php echo $gif->getGifUrl() ?>" alt="<?php echo $gif->catchPhrase ?>" />
                    <div class="catchPhrase"><?php echo $gif->catchPhrase ?></div>
                    <div class="gifItemFooter">
                        <div>Posté le <span><?php echo $gif->submissionDate ?></span></div>
                        <div>Proposé par <span><?php echo $gif->submittedBy ?></span></div>
                        <div class="fb-like" data-href="<?php echo $gif->getPermalink() ?>" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
                    </div>
                </a>
            </div>
        <?php } ?>

        <div class="gifItem">
            <img src="http://ljdchost.com/CuNH79E.gif" alt="Quand le chef cherche quelqu’un pour taffer sur un vieux projet avec lui" />
            <div class="catchPhrase">Quand le chef cherche quelqu’un pour taffer sur un vieux projet avec lui</div>
            <div class="gifItemFooter">
                <div>Posté le <span>01/02/2015</span></div>
                <div>Proposé par <span>louim</span></div>
            </div>
        </div>
        <div class="gifItem">
            <img src="http://ljdchost.com/sk2J56x.gif" alt="Quand je déplace mon projet et que j’ai oublié de copier ses fichiers de référence" />
            <div class="catchPhrase">Quand je déplace mon projet et que j’ai oublié de copier ses fichiers de référence</div>
            <div class="gifItemFooter">
                <div>Posté le <span>01/02/2015</span></div>
                <div>Proposé par <span>louim</span></div>
            </div>
        </div>
    </div>
</div>

<?php include('ljs-template/facebook-sdk.part.php'); ?>
<?php include('ljs-template/footer.part.php'); ?>
