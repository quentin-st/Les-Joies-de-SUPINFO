<?php
require_once('ljs-includes.php');

if (isset($_POST['id'])) {

    $gif = getGif($_POST['id']);

    if ($gif) {
        if ($gif->reportStatus == ReportState::REPORTED) {
            echo "<div class='alert alert-warning reported'>Ce gif a déjà été reporté par quelqu'un, nous y jetterons un œil dès que possible</div>";
        } else if ($gif->reportStatus == ReportState::IGNORED) {
            echo "<div class='alert alert-danger reported'>La modération a décidé de ne pas supprimer ce gif malgré un précédent signalement.</div>";
        }
        else {
            $gif->reportStatus = ReportState::REPORTED;
            updateGif($gif);
            echo "<div class='alert alert-info reported'>Merci d'avoir signalé ce gif, nous y jetterons un œil dès que possible</div>";
        }
    } else {
        echo "Error lors de la récupération du Gif";
    }
}