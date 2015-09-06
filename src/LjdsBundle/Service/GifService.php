<?php

namespace LjdsBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifState;

class GifService
{
    /** @var EntityManager */
    private $em;
    /** @var boolean */
    private $facebookAutopost;
    /** @var FacebookService */
    private $facebookService;
    /** @var boolean */
    private $twitterAutopost;
    /** @var TwitterService */
    private $twitterService;

    public function __construct(EntityManager $em,
                                $facebookAutopost, FacebookService $facebookService,
                                $twitterAutopost, TwitterService $twitterService)
    {
        $this->em = $em;
        $this->facebookAutopost = $facebookAutopost;
        $this->facebookService = $facebookService;
        $this->twitterAutopost = $twitterAutopost;
        $this->twitterService = $twitterService;
    }

    public function publish(Gif $gif)
    {
        if (!$gif)
            return false;

        if (!$gif->getGifStatus() == GifState::ACCEPTED)
            return false;

        $gif->setPublishDate(new DateTime());
        $gif->setGifStatus(GifState::PUBLISHED);
        $gif->generateUrlReadyPermalink();

        $this->em->flush();

        if ($this->facebookAutopost)
            $this->facebookService->postGif($gif);

        if ($this->twitterAutopost)
            $this->twitterService->postGif($gif);

        return true;
    }
}
