<?php

namespace LjdsBundle\Service;

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


	public function __construct(array $domains, Router $router, EntityManager $em)
	{
		$this->domains = $domains;
		$this->router = $router;
		$this->em = $em;
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
			return $gif1->getLikes() - $gif2->getLikes();
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
	 * Sets the likes attribute of each gif in the gifs list passed as parameter
	 * @param $gifs Gif[]
	 */
	private function fetchLikes($gifs)
	{
		// Reset likes count for these gifs
		foreach ($gifs as $gif)
		{
			$gif->setLikes(0);
		}

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

		// That's it!
	}
}
