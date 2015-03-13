<?php
/**
 * @thomson: do your magic, that's just sample code
 */
function getGifs() {
    $gif1 = new Gif();
    $gif1->gifStatus = GifState::ACCEPTED;
    $gif1->catchPhrase = 'Quand je vois les specs du nouveau projet';
    $gif1->fileName = 'cbel57q.gif';
    $gif1->submissionDate = '01/02/2015';
    $gif1->submittedBy = 'fredlopi';
    $gif1->permalink = getUrlReadyPermalink($gif1->catchPhrase);

    $gif2 = new Gif();
    $gif2->gifStatus = GifState::ACCEPTED;
    $gif2->catchPhrase = 'Quand le chef cherche quelqu’un pour taffer sur un vieux projet avec lui';
    $gif2->fileName = 'CuNH79E.gif';
    $gif2->submissionDate = '01/02/2015';
    $gif2->submittedBy = 'fredlopi';
    $gif2->permalink = getUrlReadyPermalink($gif2->catchPhrase);

    $gif3 = new Gif();
    $gif3->gifStatus = GifState::ACCEPTED;
    $gif3->catchPhrase = 'Quand je déplace mon projet et que j’ai oublié de copier ses fichiers de référence';
    $gif3->fileName = 'sk2J56x.gif';
    $gif3->submissionDate = '01/02/2015';
    $gif3->submittedBy = 'fredlopi';
    $gif3->permalink = getUrlReadyPermalink($gif3->catchPhrase);

    return [ $gif1, $gif2, $gif3 ];
}

function getGifFromPermalink($permalink) {
    $gif = new Gif();
    $gif->gifStatus = GifState::ACCEPTED;
    $gif->catchPhrase = 'Quand je vois les specs du nouveau projet';
    $gif->fileName = 'cbel57q.gif';
    $gif->submissionDate = '01/02/2015';
    $gif->submittedBy = 'fredlopi';
    $gif->permalink = getUrlReadyPermalink($gif->catchPhrase);
    return $gif;
}
