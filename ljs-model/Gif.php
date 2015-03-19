<?php

class Gif {
    public $id;
    public $catchPhrase;
    public $submissionDate;
    public $submittedBy;
    public $publishDate;
    public $gifStatus;
    public $fileName;
    public $permalink;
    public $source;

    function __construct() {
        $this->id = -1;
        $this->gifStatus = GifState::SUBMITTED; // default value
    }

    function getPermalink() {
        if (PRETTY_URLS)
            return WEBSITE_URL . $this->permalink;
        else
            return WEBSITE_URL . 'gif.php?gif='.$this->permalink;
    }

    function getGifUrl() {
        return WEBSITE_URL . 'uploads/' . $this->fileName;
    }

    function getHTML() {
        ob_start();
        ?>
        <div class="gifItem">
            <? if ($this->source != '') { ?>
            <a href="<?= $this->source ?>" class="gifSource topRightIcon" title="Source du gif" target="_blank"></a>
            <? } ?>
            <a href="#" class="reportGif topRightIcon" title="Signaler ce gif"></a>
            <a href="<?= $this->getPermalink() ?>" class="gifLink">
                <img src="<?= $this->getGifUrl() ?>" alt="<?= $this->catchPhrase ?>" />
                <div class="catchPhrase"><?= $this->catchPhrase ?></div>
            </a>
            <div class="gifItemFooter">
                <div>Posté le <span><?= $this->publishDate->format('d-m-Y') ?></span></div>
                <div>Proposé par <span><?= $this->submittedBy ?></span></div>
                <div class="fb-like" data-href="<?= $this->getPermalink() ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
            </div>
        </div>
        <?
        return ob_get_clean();
    }

    static function createFromDb($dbGif) {
        $gif = new Gif();
        $gif->id = $dbGif['id'];
        $gif->gifStatus = $dbGif['gifStatus'];
        $gif->catchPhrase = $dbGif['catchPhrase'];
        $gif->fileName = $dbGif['fileName'];
        $gif->submissionDate = new DateTime($dbGif['submissionDate']);
        $gif->submittedBy = $dbGif['submittedBy'];
        $gif->publishDate = new DateTime($dbGif['publishDate']);
        $gif->source = $dbGif['source'];
        $gif->permalink = $dbGif['permalink'];
        return $gif;
    }
}

abstract class GifState {
    const SUBMITTED = 0;
    const ACCEPTED = 1;
    const REFUSED = 2;
    const PUBLISHED = 3;
}
