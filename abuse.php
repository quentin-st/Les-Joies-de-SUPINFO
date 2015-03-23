<?php
require_once('ljs-includes.php');

if (isset($_POST['id'])) {

    $gif = getGif($_POST['id']);

    if ($gif) {
        if ($gif->gifStatus == GifState::REPORTED) {
            echo "Ce gif a déjà été reporté par quelqu'un, nous y jetterons un œil dès que possible";
        } else {
            $gif->gifStatus = GifState::REPORTED;
            updateGif($gif);
            echo "Le gif a bien été signalé, nous allons vérifier ça !";
        }
    } else {
        echo "Error lors de la récupération du Gif";
    }
}