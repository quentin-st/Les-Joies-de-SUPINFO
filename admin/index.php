<?php
/**
 * Admin part
 * (login & password protected)
 */

require_once('../ljs-includes.php');

// Pagination
$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
$pagesCount = getPagesCount();
if ($page < 1 || $page > $pagesCount)
    $page = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Les Joies de Supinfo</title>
    <meta name="description" content="">
    <meta name="author" content="Quentin Stoeckel, Frédéric Strebler, Thomas Nold">
    <link href="<?= WEBSITE_URL ?>inc/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?= WEBSITE_URL ?>inc/css/bootstrap-theme.min.css" rel="stylesheet" />
    <link href="<?= WEBSITE_URL ?>inc/css/style.css" rel="stylesheet" />
    <link href="<?= WEBSITE_URL ?>inc/css/style_admin.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:500,300' rel='stylesheet' type='text/css'>
    <script src="<?= WEBSITE_URL ?>inc/js/jquery-1.11.2.min.js"></script>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="row">
            <div class="col-md-6">
                <a href="<?= WEBSITE_URL ?>"><h1>Les Joies de Supinfo</h1></a>
            </div>
            <div class="col-md-2"></div>
            <div class="col-md-4">
                <ul class="menu">
                    <li><a href="<?= WEBSITE_URL ?>submit.php">Proposer un gif</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="gifsAdmin">
            <? foreach (getGifs(-1,GifState::SUBMITTED) as $gif) {
                ?>
                <div class="gifItemAdmin">
                        <img src="<?= $gif->getGifUrl() ?>" alt="<?= $gif->catchPhrase ?>" />
                        <div class="catchPhrase"><span><?= $gif->catchPhrase ?></span></div>

                        <div class="gifValidation">
                            <button type="button" data-gifid="<? echo $gif->id ?>" class="btn btn-success acceptGif">Valider</button>
                            <button type="button" data-gifid="<? echo $gif->id ?>" class="btn btn-danger rejectGif">Rejeter</button>
                        </div>

                        <div class="gifItemFooter">
                            <div>Posté le <span><?= $gif->publishDate->format('d-m-Y') ?></span></div>
                            <div>Proposé par <span><?= $gif->submittedBy ?></span></div>
                            <div class="fb-like" data-href="<?= $gif->getPermalink() ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
                        </div>
                </div>
                <?
            } ?>
        </div>
    </div>
</div>
<div class="footer">
    <a href="https://github.com/chteuchteu/Les-Joies-de-Supinfo">Contribuer</a> &bull; <a href="<?= WEBSITE_URL ?>rulesOfTheGame.php">Conditions d'utilisation</a>
</div>
<script src="<?= WEBSITE_URL ?>inc/js/bootstrap.min.js"></script>
<script>
    $('.acceptGif').click(function() {
        var gifId = $(this).attr('data-gifid');
        var domElement = $(this);
        $.ajax({
            url : 'ws.php',
            type : 'POST',
            data : {
                action : 'change_gif_status',
                gif_id : parseInt(gifId),
                new_gif_state : 'accepted',
                api_key : ''
            },
            success: function (data) {
                domElement.parent().parent().remove();
            }
        });
        console.log(gifId)
    });
</script>
</body>
</html>
