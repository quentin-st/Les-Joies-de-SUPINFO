<?php
/**
 * Header template.
 *  Uses following global variables:
 *      * pageName
 */
global $pageName;
if ($pageName != '')
    $pageName .= ' - ';
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
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Les Joies de Supinfo</h1>
    </div>
