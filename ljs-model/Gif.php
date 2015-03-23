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
        <div class="gifItem row">
            <div class="gifInfos">
                <div>
                    <?= getRelativeTime($this->publishDate->format('Y-m-d h:m:s')) ?> -
                    <a href="submitter.php?s=<?= $this->submittedBy ?>"><?= $this->submittedBy ?></a>
                </div>
                <div class="fb-like" data-href="<?= $this->getPermalink() ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
                <? if ($this->source != '') { ?>
                    <a href="<?= $this->source ?>" target="_blank" class="actionIconContainer">
                        <span class="actionIconText">Source du gif</span> <span class="gifSourceIcon"></span>
                    </a>
                <? } ?>
                <a href="#" class="actionIconContainer">
                    <span class="actionIconText report" id="<?= $this->id ?>">Signaler ce gif</span> <span class="reportGifIcon"></span>
                </a>
            </div>
            <div class="gifMain">
                <a href="<?= $this->getPermalink() ?>" class="gifLink">
                    <div class="catchPhrase"><?= $this->catchPhrase ?></div>
                    <img src="<?= $this->getGifUrl() ?>" alt="<?= $this->catchPhrase ?>" />
                </a>
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
