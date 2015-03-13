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
}

abstract class GifState {
    const SUBMITTED = 0;
    const ACCEPTED = 1;
    const REFUSED = 2;
}
