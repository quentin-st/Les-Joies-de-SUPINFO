<?php

function getGifs($page = -1, $gifStatus = -1) {
    $sqlReq = 'SELECT * FROM gifs';
    if ($gifStatus != -1)
        $sqlReq .= ' WHERE gifStatus = ' . $gifStatus;
    $sqlReq .= ' ORDER BY publishDate DESC';
    if ($page != -1)
        $sqlReq .= ' LIMIT ' . ($page-1)*GIFS_PER_PAGE . ',' . GIFS_PER_PAGE;

    $stmt = getDb()->prepare($sqlReq);
    $stmt->execute();
    $dbGifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $gifs = [];
    foreach ($dbGifs as $dbGif)
        $gifs[] = Gif::createFromDb($dbGif);

    return $gifs;
}

function getGifsCountBySubmitter($submitter) {
    $stmt = getDb()->prepare('SELECT COUNT(*) FROM gifs WHERE submittedBy=:submitter');
    $stmt->bindParam(':submitter', $submitter);
    $stmt->execute();
    return $stmt->fetchAll()[0][0];
}

function getGifsBySubmitter($submitter, $page) {
    $stmt = getDb()->prepare('SELECT * FROM gifs WHERE submittedBy=:submitter
                ORDER BY publishDate DESC LIMIT ' . ($page-1)*GIFS_PER_PAGE . ',' . GIFS_PER_PAGE);
    $stmt->bindParam(':submitter', $submitter);
    $stmt->execute();
    $dbGifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $gifs = [];
    foreach ($dbGifs as $dbGif)
        $gifs[] = Gif::createFromDb($dbGif);

    return $gifs;
}

function getGif($id) {
    $stmt = getDb()->prepare('SELECT * FROM gifs WHERE id = :gifId');
    $stmt->bindParam(':gifId', $id);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($res) == 0)
        return null;

    return Gif::createFromDb($res[0]);
}

function getGifFromPermalink($permalink) {
    $stmt = getDb()->prepare('SELECT * FROM gifs WHERE permalink = :permalink');
    $stmt->bindParam(':permalink', $permalink);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($res) == 0)
        return null;

    return Gif::createFromDb($res[0]);
}

function getPagesCount() {
    $stmt = getDb()->prepare('SELECT CEIL(COUNT(*)/' . GIFS_PER_PAGE . ') as count FROM gifs');
    $stmt->execute();
    return intval($stmt->fetchAll(PDO::FETCH_ASSOC)[0]['count']);
}

function insertGif(Gif $gif) {
    $db = getDb();
    $stmt = $db->prepare(
        'INSERT INTO gifs
            (catchPhrase, submissionDate, submittedBy, publishDate, gifStatus, fileName, permalink, source)
            VALUES (:catchPhrase, :submissionDate, :submittedBy, :publishDate, :gifStatus, :fileName, :permalink, :source)');
    $stmt->bindParam(':catchPhrase', $gif->catchPhrase);
    $submissionDate = $gif->submissionDate->format('Y-m-d H:i:s');
    $stmt->bindParam(':submissionDate', $submissionDate);
    $stmt->bindParam(':submittedBy', $gif->submittedBy);
    $publishDate = $gif->publishDate->format('Y-m-d H:i:s');
    $stmt->bindParam(':publishDate', $publishDate);
    $stmt->bindParam(':gifStatus', $gif->gifStatus);
    $stmt->bindParam(':fileName', $gif->fileName);
    $stmt->bindParam(':permalink', $gif->permalink);
    $stmt->bindParam(':source', $gif->source);
    $stmt->execute();
    $gif->id = $db->lastInsertId();
    return $gif->id;
}

function updateGif($gif) {
    $stmt = getDb()->prepare('UPDATE gifs SET catchPhrase=:catchPhrase, submissionDate=:submissionDate, submittedBy=:submittedBy,
                                publishDate=:publishDate, gifStatus=:gifStatus, fileName=:fileName, permalink=:permalink, source=:source
                                WHERE id=:gifId');
    $stmt->bindParam(':catchPhrase', $gif->catchPhrase);
    $submissionDate = $gif->submissionDate->format('Y-m-d H:i:s');
    $stmt->bindParam(':submissionDate', $submissionDate);
    $stmt->bindParam(':submittedBy', $gif->submittedBy);
    $publishDate = $gif->publishDate->format('Y-m-d H:i:s');
    $stmt->bindParam(':publishDate', $publishDate);
    $stmt->bindParam(':gifStatus', $gif->gifStatus);
    $stmt->bindParam(':fileName', $gif->fileName);
    $stmt->bindParam(':permalink', $gif->permalink);
    $stmt->bindParam(':source', $gif->source);
    $stmt->bindParam(':gifId', $gif->id);
    $stmt->execute();
}

function getTopContributors() {
    $stmt = getDb()->prepare('SELECT submittedBy as contributor, COUNT(*) as gifsCount
                              FROM gifs GROUP BY submittedBy ORDER BY gifsCount DESC');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function insertSampleData() {
    $gif1 = new Gif();
    $gif1->gifStatus = GifState::PUBLISHED;
    $gif1->catchPhrase = 'Quand je vois les specs du nouveau projet';
    $gif1->fileName = 'sample.gif';
    $gif1->submissionDate = new DateTime();
    $gif1->submittedBy = 'fredlopi';
    $gif1->publishDate = new DateTime();
    $gif1->source='http://lesjoiesducode.fr';
    $gif1->permalink = getUrlReadyPermalink($gif1->catchPhrase);
    insertGif($gif1);

    $gif2 = new Gif();
    $gif2->gifStatus = GifState::PUBLISHED;
    $gif2->catchPhrase = 'Quand le chef cherche quelqu’un pour taffer sur un vieux projet avec lui';
    $gif2->fileName = 'sample.gif';
    $gif2->submissionDate = new DateTime();
    $gif2->submittedBy = 'fredlopi';
    $gif2->publishDate = new DateTime();
    $gif2->source='http://lesjoiesducode.fr';
    $gif2->permalink = getUrlReadyPermalink($gif2->catchPhrase);
    insertGif($gif2);

    $gif3 = new Gif();
    $gif3->gifStatus = GifState::SUBMITTED;
    $gif3->catchPhrase = 'Quand je déplace mon projet et que j’ai oublié de copier ses fichiers de référence';
    $gif3->fileName = 'sample.gif';
    $gif3->submissionDate = new DateTime();
    $gif3->submittedBy = 'fredlopi';
    $gif3->publishDate = new DateTime();
    $gif3->source='http://lesjoiesducode.fr';
    $gif3->permalink = getUrlReadyPermalink($gif3->catchPhrase);
    insertGif($gif3);

    $gif4 = new Gif();
    $gif4->gifStatus = GifState::SUBMITTED;
    $gif4->catchPhrase = 'Quand je laisse le stagiaire faire sa première mise en prod';
    $gif4->fileName = 'sample.gif';
    $gif4->submissionDate = new DateTime();
    $gif4->submittedBy = 'fredlopi';
    $gif4->publishDate = new DateTime();
    $gif4->source='http://lesjoiesducode.fr';
    $gif4->permalink = getUrlReadyPermalink($gif4->catchPhrase);
    insertGif($gif4);
}
