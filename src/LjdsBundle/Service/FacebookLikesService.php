<?php

namespace LjdsBundle\Service;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Helper\Util;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class FacebookLikesService
{
    /** @var string[] */
    private $domains;
    /** @var Router */
    private $router;
    /** @var EntityManager */
    private $em;
    /** @var Cache */
    private $cache;

    private $facebookAppId;
    private $facebookAppSecret;
    private $facebookAccessToken;

    const MAX_URLS_PER_API_CALL = 30;

    public function __construct(array $domains, Router $router, EntityManager $em, Cache $memcached,
                                $facebookAppId, $facebookAppSecret, $facebookAccessToken)
    {
        $this->domains = $domains;
        $this->router = $router;
        $this->em = $em;
        $this->cache = $memcached;

        $this->facebookAppId = $facebookAppId;
        $this->facebookAppSecret = $facebookAppSecret;
        $this->facebookAccessToken = $facebookAccessToken;
    }

    /**
     * Returns a sorted list of the 20 most liked gifs
     * @return Gif[]
     */
    public function getTop()
    {
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $this->em->getRepository('LjdsBundle:Gif');
        /** @var Gif[] $gifs */
        $gifs = $gifsRepo->findByGifState(GifState::PUBLISHED);

        // Fetch likes count for these gifs
        $this->fetchLikes($gifs);

        // Sort array
        usort($gifs, function (Gif $gif1, Gif $gif2) {
            return $gif2->getLikes() - $gif1->getLikes();
        });

        // Take the 20 first ones
        return array_slice($gifs, 0, 20);
    }

    /**
     * Returns all gifs, grouped by submitters and sorted by gifs likes sum count
     * @return Gif[]
     */
    public function getTopSubmitters()
    {
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $this->em->getRepository('LjdsBundle:Gif');
        /** @var Gif[] $gifs */
        $gifs = $gifsRepo->findByGifState(GifState::PUBLISHED);

        // Fetch likes count for these gifs
        $this->fetchLikes($gifs);

        // Build submitters list
        $submitters = [];

        foreach ($gifs as $gif) {
            if (!array_key_exists($gif->getSubmittedBy(), $submitters)) {
                $submitters[$gif->getSubmittedBy()] = [
                    'gifs' => [],
                    'likes' => 0
                ];
            }

            $submitters[$gif->getSubmittedBy()]['gifs'][] = $gif;
            $submitters[$gif->getSubmittedBy()]['likes'] += $gif->getLikes();
        }

        // Sort array
        $submittersIndexed = [];
        foreach ($submitters as $submitter => $infos) {
            $submittersIndexed[] = [
                'submitter' => $submitter,
                'gifs' => $infos['gifs'],
                'likes' => $infos['likes']
            ];
        }
        usort($submittersIndexed, function ($a, $b) {
            return $b['likes'] - $a['likes'];
        });
        return $submittersIndexed;
    }

    /**
     * Returns the like count for a submitter
     * @param $submitter
     * @return int
     */
    public function getLikesCountForSubmitter($submitter)
    {
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $this->em->getRepository('LjdsBundle:Gif');
        /** @var Gif[] $gifs */
        $gifs = $gifsRepo->findBySubmitter($submitter);

        $this->fetchLikes($gifs);

        $count = 0;
        foreach ($gifs as $gif) {
            $count += $gif->getLikes();
        }

        return $count;
    }

    /**
     * Sets the likes attribute of each gif in the gifs list passed as parameter
     * @param $gifsList Gif[]
     */
    public function fetchLikes($gifsList)
    {
        // Check which gifs needs an up-to-date likes count
        /** @var Gif[] $gifs */
        $gifs = [];
        foreach ($gifsList as $gif) {
            // We already checked likes count for this one
            if ($gif->getLikes() > 0) {
                continue;
            }

            // Cache hit
            $key = 'gif#'.$gif->getId().'_likes';
            if ($this->cache->contains($key)) {
                $gif->setLikes((int) ($this->cache->fetch($key)));
                continue;
            }

            $gifs[] = $gif;
        }

        // Cache hit for all list items: don't call API
        if (count($gifs) == 0) {
            return;
        }

        // Save router context host to set it back afterwards
        $currentScheme = $this->router->getContext()->getScheme();
        $currentHost = $this->router->getContext()->getHost();

        // Get a list of all URLs for those gifs
        $urls = $this->getURLsForGifs($gifs);
        // Chunk the array to run X batchs
        $urls_chunks = array_chunk($urls, self::MAX_URLS_PER_API_CALL, true);

        // Set back host & scheme
        $this->router->getContext()->setScheme($currentScheme);
        $this->router->getContext()->setHost($currentHost);

        foreach ($urls_chunks as $chunk) {
            // Call API
            $likes = $this->getLikesFromFacebookAPI($chunk);

            // Read API return array
            foreach ($likes as $url => $likesCount) {
                /** @var Gif $gif */
                $gif = $chunk[$url];

                // Add this count to the gifs likes count
                $gif->setLikes($gif->getLikes() + $likesCount);
            }

            // Save likes counts in cache
            foreach ($gifs as $gif) {
                $this->cache->save(
                    'gif#'.$gif->getId().'_likes',
                    $gif->getLikes(),
                    $gif->getCacheLifeTime()
                );
            }
        }

        // That's it!
    }

    /**
     * Returns the likes count for each of these URLs
     * @param  array $urls
     * @return array
     */
    public function getLikesFromFacebookAPI(array $urls)
    {
        $fb = new Facebook([
            'app_id' => $this->facebookAppId,
            'app_secret' => $this->facebookAppSecret,
            'default_graph_version' => 'v2.7'
        ]);

        // Generate Facebook graph requests
        $batch = [];
        foreach ($urls as $url => $gif) {
            $batch[] = $fb->request('GET', '/', [
                'id' => $url,
                'fields' => 'og_object{engagement{count}}'
            ]);
        }

        // Execute requests
        try {
            $responses = $fb->sendBatchRequest($batch, $this->facebookAccessToken);
        } catch (FacebookSDKException $e) {
            //var_dump($e->getMessage());
            return null;
        }

        // Create likes array (url => likesCount)
        $likes = [];

        /** @var FacebookResponse $response */
        foreach ($responses as $key => $response) {
            if (!$response->isError()) {
                $graph = $response->getGraphNode();

                $url = $graph->getField('id');
                $count = $graph->getField('og_object')->getField('engagement')->getProperty('count');

                $likes[$url] = $count;
            }
        }

        return $likes;
    }

    /**
     * Returns all the URLs we know for these gifs (one for each domain we have)
     * @param  array $gifs
     * @return array
     */
    public function getURLsForGifs(array $gifs)
    {
        $urls = [];

        foreach ($this->domains as $domain) {
            $scheme = 'http';

            // domain contains scheme: let's split it
            if (preg_match('/(http|https):\\/\\/(.*?)$/i', $domain, $matches) > 0) {
                $scheme = $matches[1];
                $domain = $matches[2];
            }

            $this->router->getContext()->setScheme($scheme);
            $this->router->getContext()->setHost($domain);

            /** @var Gif $gif */
            foreach ($gifs as $gif) {
                $url = $this->router->generate('gif', [
                    'permalink' => $gif->getPermalink()
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                $url = Util::fixSymfonyGeneratedURLs($url);

                $urls[$url] = $gif;
            }
        }

        return $urls;
    }
}
