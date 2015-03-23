<?php
/**
 * Header template.
 *  Uses following global variables:
 *      * pageName
 */
global $pageName;
if ($pageName != '')
    $pageName .= ' - ';

$isHomePage = isset($homePage) && $homePage;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageName ?>Les Joies de Supinfo</title>
    <meta name="description" content="">
    <meta name="author" content="Quentin Stoeckel, Frédéric Strebler, Thomas Nold">
    <link href="<?= WEBSITE_URL ?>inc/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?= WEBSITE_URL ?>inc/css/bootstrap-theme.min.css" rel="stylesheet" />
    <link href="<?= WEBSITE_URL ?>inc/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:500,300' rel='stylesheet' type='text/css'>
    <script src="<?= WEBSITE_URL ?>inc/js/jquery-1.11.2.min.js"></script>
    <script src="<?= WEBSITE_URL ?>inc/js/script.js"></script>
</head>
<body>
<div class="container">
    <div class="header <? if($isHomePage) echo 'homeHeader'; ?>">
        <div class="row">
            <div class="col-md-6">
                <a href="<?= WEBSITE_URL ?>"><h1>Les Joies de Supinfo</h1></a>
            </div>
            <div class="col-md-6">
                <ul class="menu">
                    <li><a href="topGifs.php">Top gifs</a></li>
                    <li><a href="topSubmitters.php">Top contributeurs</a></li>
                    <li><a href="<?= WEBSITE_URL ?>submit.php">Proposer un gif</a></li>
                </ul>
            </div>
        </div>
        <? if ($isHomePage) { ?>
        <div class="subHeader">
            <h2>Les situations de la vie quotidienne d'un(e) étudiant(e) de Supinfo</h2>
            <p>Suivre Les Joies de Supinfo - <a href="<?= WEBSITE_URL ?>feed/">feed</a></p>
        </div>
        <? } ?>
    </div>
