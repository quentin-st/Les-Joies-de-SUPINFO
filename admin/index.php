<?php
/**
 * Admin part
 * (login & password protected)
 */

require_once('../ljs-includes.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Joies de Supinfo</title>
<meta name="description" content="">
<meta name="author" content="Quentin Stoeckel, Frédéric Strebler, Thomas Nold">
<link href="<?= WEBSITE_URL ?>inc/css/bootstrap.min.css" rel="stylesheet" />
<link href="<?= WEBSITE_URL ?>inc/css/bootstrap-theme.min.css" rel="stylesheet" />
<link href="<?= WEBSITE_URL ?>inc/css/style.css" rel="stylesheet" />
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
</div>
<div class="footer">
    <a href="https://github.com/chteuchteu/Les-Joies-de-Supinfo">Contribuer</a> &bull; <a href="<?= WEBSITE_URL ?>rulesOfTheGame.php">Conditions d'utilisation</a>
</div>
<script src="<?= WEBSITE_URL ?>inc/js/bootstrap.min.js"></script>
</body>
</html>