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
        <div class="adminTabs">
            <ul class="nav nav-pills">
                <li class="mSubmitted"><a href="index.php?state=submitted">A modérer</a></li>
                <li class="mAccepted"><a href="index.php?state=accepted">Acceptés</a></li>
                <li class="mRefused"><a href="index.php?state=refused">Refusés</a></li>
            </ul>
        </div>
        <?
        if ( isset($_GET['state']) ){
            switch ($_GET['state']) {
                case 'submitted': require_once 'submitted.part.php'; break;
                case 'refused': require_once 'refused.part.php'; break;
                case 'accepted': require_once 'accepted.part.php'; break;
            }
        }else require_once 'submitted.part.php';
        ?>
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
