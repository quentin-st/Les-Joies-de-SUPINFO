<?php

namespace App\EventListener;

use App\Entity\GifState;
use App\Repository\GifRepository;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsEventListener]
class SitemapListener
{
    public function __construct(
        private readonly GifRepository $gifRepo,
    )
    {
    }

    public function __invoke(SitemapPopulateEvent $event): void
    {
        foreach ($this->gifRepo->findByGifState(GifState::PUBLISHED) as $gif) {
            $url = $event->getUrlGenerator()->generate('gif', ['permalink' => $gif->getPermalink()], UrlGeneratorInterface::ABSOLUTE_URL);

            $event->getUrlContainer()->addUrl(
                new UrlConcrete($url, new \DateTime(), UrlConcrete::CHANGEFREQ_WEEKLY, 0.8),
                'default'
            );
        }
    }
}
