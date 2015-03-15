<?php

class Gif {
    public $id;
    public $catchPhrase;
    public $submissionDate;
    public $submittedBy;
    public $publicationDate;
    public $gifStatus;
    public $fileName;
    public $permalink;
    public $source;

    function __construct() {
        $this->id = -1;
        $this->gifStatus = GifState::SUBMITTED; // default value
    }

    function getPermalink() {
        return WEBSITE_URL . $this->permalink;
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
                <div>Posté le <span><?= $this->submissionDate->format('d-m-Y') ?></span></div>
                <div>Proposé par <span><?= $this->submittedBy ?></span></div>
                <div class="fb-like" data-href="<?= $this->getPermalink() ?>" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
            </div>
        </div>
        <?
        return ob_get_clean();
    }
}

abstract class GifState {
    const SUBMITTED = 0;
    const ACCEPTED = 1;
    const REFUSED = 2;
}
