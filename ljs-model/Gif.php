<?php

class Gif {
    public $id;
    public $catchPhrase;
    public $submissionDate;
    public $submittedBy;
    public $gifStatus;
    public $fileName;
    public $permalink;

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
            <a href="<?php echo $this->getPermalink() ?>">
                <img src="<?php echo $this->getGifUrl() ?>" alt="<?php echo $this->catchPhrase ?>" />
                <div class="catchPhrase"><?php echo $this->catchPhrase ?></div>
                <div class="gifItemFooter">
                    <div>Posté le <span><?php echo $this->submissionDate ?></span></div>
                    <div>Proposé par <span><?php echo $this->submittedBy ?></span></div>
                    <div class="fb-like" data-href="<?php echo $this->getPermalink() ?>" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>
                </div>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
}

abstract class GifState {
    const SUBMITTED = 0;
    const ACCEPTED = 1;
    const REFUSED = 2;
}
