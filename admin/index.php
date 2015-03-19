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
    <div class="content fullWidth">
        <div class="gifsAdmin">
            <? foreach (getGifs(-1,GifState::SUBMITTED) as $gif) {
                ?>
                <div class="gifItemAdmin">
                        <img src="<?= $gif->getGifUrl() ?>" alt="<?= $gif->catchPhrase ?>" />

                        <div class="gifValidation">
                            <div><span><textarea id="caption<? echo $gif->id ?>" type="text" rows="2"><?= $gif->catchPhrase ?></textarea></span></div>
                            <div>
                                <span>
                                    <button type="button" data-state="accepted" data-gifid="<? echo $gif->id ?>" class="btn btn-success btnValidation">Valider</button>
                                    <button type="button" data-state="refused" data-gifid="<? echo $gif->id ?>" class="btn btn-danger btnValidation">Rejeter</button>
                                </span>
                            </div>
                            <div><span><?= $gif->publishDate->format('d-m-Y') ?> par <?= $gif->submittedBy ?></span></div>
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
    $('.btnValidation').click(function() {
        var domElement = $(this);
        var gifId = domElement.attr('data-gifid');
        var state = domElement.attr('data-state');
        var caption = $('#caption'+gifId).val();
        console.log (caption);
        console.log();

        $.ajax({
            url : 'ws.php',
            type : 'POST',
            data : {
                action : 'change_gif_status',
                gif_id : parseInt(gifId),
                caption : caption,
                new_gif_state : state,
                api_key : ''
            },
            success: function (data) {
                console.log(data);
                domElement.parent().parent().parent().parent().remove();
            },
            error:function(data){
                console.log(data);
            }
        });
        console.log(gifId + " " + state);
    });
</script>
</body>
</html>
