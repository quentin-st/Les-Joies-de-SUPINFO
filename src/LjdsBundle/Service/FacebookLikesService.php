<?php

namespace LjdsBundle\Service;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
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


	public function __construct(array $domains, Router $router, EntityManager $em, Cache $memcached)
	{
		$this->domains = $domains;
		$this->router = $router;
		$this->em = $em;
		$this->cache = $memcached;
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
		usort($gifs, function(Gif $gif1, Gif $gif2) {
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

		foreach ($gifs as $gif)
		{
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
		usort($submittersIndexed, function($a, $b) {
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
		foreach ($gifs as $gif)
			$count += $gif->getLikes();

		return $count;
	}

	/**
	 * Sets the likes attribute of each gif in the gifs list passed as parameter
	 * @param $gifsList Gif[]
	 */
	private function fetchLikes($gifsList)
	{
		// Check which gifs needs an up-to-date likes count
		/** @var Gif[] $gifs */
		$gifs = [];
		foreach ($gifsList as $gif)
		{
			// We already checked likes count for this one
			if ($gif->getLikes() > 0)
				continue;

			// Cache hit
			$key = 'gif#' . $gif->getId() . '_likes';
			if ($this->cache->contains($key)) {
				$gif->setLikes(intval($this->cache->fetch($key)));
				continue;
			}

			$gifs[] = $gif;
		}

		// Cache hit for all list items: don't call API
		if (count($gifs) == 0)
			return;


		// Build API call URL
		$urls = [];

		// Save router context host to set it back afterwards
		$currentHost = $this->router->getContext()->getHost();

		foreach ($this->domains as $domain)
		{
			$this->router->getContext()->setHost($domain);

			foreach ($gifs as $gif)
			{
				$url = $this->router->generate('gif', [
					'permalink' => $gif->getPermalink()
				], true);

				$urls[$url] = $gif;
			}
		}

		// Set back host
		$this->router->getContext()->setHost($currentHost);

		// Call API
		$urlsList = urlencode(implode(',', array_keys($urls)));
		$apiUrl = 'http://api.facebook.com/restserver.php?method=links.getStats&urls=' . $urlsList . '&format=json';
		$result = file_get_contents($apiUrl);

		// Read API call result
		$json = json_decode($result, true);

		foreach ($json as $item)
		{
			$url = $item['url'];
			$likesCount = intval($item['total_count']);

			/** @var Gif $gif */
			$gif = $urls[$url];

			// Add this count to the gifs likes count
			$gif->setLikes($gif->getLikes() + $likesCount);
		}

		// Save likes counts in cache
		foreach ($gifs as $gif)
		{
			$this->cache->save(
				'gif#' . $gif->getId() . '_likes',
				$gif->getLikes(),
				$gif->getCacheLifeTime()
			);
		}

		// That's it!
	}
}
