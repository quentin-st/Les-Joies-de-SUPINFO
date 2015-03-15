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
    <title><?php echo $pageName ?>Les Joies de Supinfo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Quentin Stoeckel, Frédéric Strebler, Thomas Nold">
    <link href="<?php echo WEBSITE_URL ?>inc/css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo WEBSITE_URL ?>inc/css/bootstrap-responsive.min.css" rel="stylesheet" />
    <link href="<?php echo WEBSITE_URL ?>inc/css/style.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:500,300' rel='stylesheet' type='text/css'>
    <script src="<?php echo WEBSITE_URL ?>inc/js/jquery-1.11.2.min.js"></script>
</head>
<body>
<div class="container">
    <div class="header <?php if($isHomePage) echo 'homeHeader'; ?>">
        <div class="row">
            <div class="span4">
                <a href="<?php echo WEBSITE_URL ?>"><h1>Les Joies de Supinfo</h1></a>
            </div>
            <div class="span4"></div>
            <div class="span4">
                <ul class="menu">
                    <li><a href="<?php echo WEBSITE_URL ?>submit.php">Proposer un gif</a></li>
                </ul>
            </div>
        </div>
        <?php if ($isHomePage) { ?>
        <div class="subHeader">
            <h2>Les situations de la vie quotidienne d'un(e) étudiant(e) de Supinfo</h2>
            <p>Suivre Les Joies de Supinfo - <a href="<?php echo WEBSITE_URL ?>feed/">feed</a></p>
        </div>
        <?php } ?>
    </div>
