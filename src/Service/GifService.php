<?php

namespace App\Service;

use App\Repository\GifRepository;
use DateTime;
use App\Entity\Gif;
use App\Entity\GifState;
use Doctrine\ORM\EntityManagerInterface;

class GifService
{
    /** @var EntityManagerInterface */
    private $em;
    /** @var MailService */
    private $mailService;

    public function __construct(EntityManagerInterface $em, MailService $mailService)
    {
        $this->em = $em;
        $this->mailService = $mailService;
    }

    /**
     * Publishes a gif and posts a link on social networks
     * @param  Gif  $gif
     * @return bool
     */
    public function publish(Gif $gif)
    {
        if (!$gif) {
            return false;
        }

        $gif->setPublishDate(new DateTime());
        $gif->setGifStatus(GifState::PUBLISHED);
        $gif->generateUrlReadyPermalink();

        // Check if permalink is unique
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $this->em->getRepository(Gif::class);
        $permalink = $gif->getPermalink();

        $i = 1;
        while (!empty($gifsRepo->findBy(['permalink' => $gif->getPermalink(), 'gifStatus' => GifState::PUBLISHED]))) {
            // Generate a new permalink
            $gif->setPermalink($permalink.$i);
            ++$i;
        }

        $this->em->flush();

        if ($gif->getEmail() != null) {
            $this->mailService->sendGifPublishedMail($gif);
        }

        return true;
    }
}
