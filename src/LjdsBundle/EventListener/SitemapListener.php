<?php

namespace LjdsBundle\EventListener;

use Doctrine\ORM\EntityManager;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifState;
use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Service\SitemapListenerInterface;
use Presta\SitemapBundle\Sitemap\Url\UrlConcrete;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class SitemapListener implements SitemapListenerInterface
{
    /** @var RouterInterface */
    private $router;
    /** @var EntityManager */
    private $em;

    public function __construct(RouterInterface $router, EntityManager $em)
    {
        $this->router = $router;
        $this->em = $em;
    }

    public function populateSitemap(SitemapPopulateEvent $event)
    {
        $gifs = $this->em->getRepository('LjdsBundle:Gif')->findByGifState(GifState::PUBLISHED);

        /** @var Gif $gif */
        foreach ($gifs as $gif) {
            $url = $this->router->generate('gif', ['permalink' => $gif->getPermalink()], UrlGeneratorInterface::ABSOLUTE_URL);

            $event->getGenerator()->addUrl(
                new UrlConcrete(
                    $url,
                    new \DateTime(),
                    UrlConcrete::CHANGEFREQ_WEEKLY,
                    0.8
                ),
                'default'
            );
        }
    }
}
