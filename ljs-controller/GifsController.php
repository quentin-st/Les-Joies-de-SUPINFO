<?php
/**
 * @thomson: do your magic, that's just sample code
 */

function getGifs($page = -1) {
    $gif1 = new Gif();
    $gif1->gifStatus = GifState::PUBLISHED;
    $gif1->catchPhrase = 'Quand je vois les specs du nouveau projet';
    $gif1->fileName = 'cbel57q.gif';
    $gif1->submissionDate = new DateTime();
    $gif1->submittedBy = 'fredlopi';
    $gif1->publishDate = new DateTime();
    $gif1->source='http://lesjoiesducode.fr';
    $gif1->permalink = getUrlReadyPermalink($gif1->catchPhrase);

    $gif2 = new Gif();
    $gif2->gifStatus = GifState::PUBLISHED;
    $gif2->catchPhrase = 'Quand le chef cherche quelqu’un pour taffer sur un vieux projet avec lui';
    $gif2->fileName = 'CuNH79E.gif';
    $gif2->submissionDate = new DateTime();
    $gif2->submittedBy = 'fredlopi';
    $gif2->publishDate = new DateTime();
    $gif2->source='http://lesjoiesducode.fr';
    $gif2->permalink = getUrlReadyPermalink($gif2->catchPhrase);

    $gif3 = new Gif();
    $gif3->gifStatus = GifState::PUBLISHED;
    $gif3->catchPhrase = 'Quand je déplace mon projet et que j’ai oublié de copier ses fichiers de référence';
    $gif3->fileName = 'sk2J56x.gif';
    $gif3->submissionDate = new DateTime();
    $gif3->submittedBy = 'fredlopi';
    $gif3->publishDate = new DateTime();
    $gif3->source='http://lesjoiesducode.fr';
    $gif3->permalink = getUrlReadyPermalink($gif3->catchPhrase);

    return [ $gif1, $gif2, $gif3 ];
}

function getGifsByState($state) {
    return getGifs();
}

function getGif($id) {
    return getGifs()[0];
}

function getGifFromPermalink($permalink) {
    return getGifs()[0];
}

function getPagesCount() {
    // (int) (SELECT COUNT(*) / GIFS_PER_PAGE)
    return 3;
}

function insertGif($gif) {
    // TODO
}

function updateGif($gif) {
    // TODO
}
