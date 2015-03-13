<?php
require_once('ljs-includes.php');

/*
 * This is the main page of the project
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Les Joies de Supinfo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Quentin Stoeckel, Frédéric Strebler, Thomas Nold">
    <link href="inc/css/bootstrap.min.css" rel="stylesheet" />
    <link href="inc/css/bootstrap-responsive.min.css" rel="stylesheet" />
    <link href="inc/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:500,300' rel='stylesheet' type='text/css'>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Les Joies de Supinfo</h1>
        </div>
        <div class="content">
            <?php foreach (getGifs() as $gif) { ?>
                <div class="gifItem">
                    <img src="<?php echo $gif->getGifUrl() ?>" alt="<?php echo $gif->catchPhrase ?>" />
                    <div class="catchPhrase"><?php echo $gif->catchPhrase ?></div>
                    <div class="gifItemFooter">
                        <div>Posté le <span><?php echo $gif->submissionDate ?></span></div>
                        <div>Proposé par <span><?php echo $gif->submittedBy ?></span></div>
                    </div>
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

    <script src="inc/js/bootstrap.min.js" ></script>
    <script src="inc/js/jquery-1.11.2.min.js" ></script>
</body>
</html>
