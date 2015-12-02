<?php

namespace LjdsBundle\Helper;

use LjdsBundle\Entity\Gif;
use Symfony\Component\Routing\Router;

class FacebookHelper
{
	/**
	 * Get a list of published Gifs sorted by Facebook likes (& shares & comments)
	 * @param $gifs Gif[]
	 * @param Router $router
	 * @return Gif[]
	 */
	public static function getFacebookLikes(array $gifs, Router $router)
	{
		$json = FacebookHelper::getLikes($gifs, $router);

		$likes = [];
		foreach ($json as $item) {
			$url = $item['url'];
			$likesCount = intval($item['total_count']);

            // Each time a gif is published, it is posted on the Facebook page's wall:
            // It has been liked if the likesCount is > 1
            if ($likesCount > 1) {
                $likes[] = [
                    'url' => $url,
                    'likes' => $likesCount,
                    'gif' => FacebookHelper::findGif($gifs, $url)
                ];
            }
		}

		// Sort array
		usort($likes, function($a, $b) {
			return intval($b['likes']) - intval($a['likes']);
		});

		return $likes;
	}

	public static function getFacebookLikesGroupedBySubmitter(array $gifs, Router $router)
	{
		$json = FacebookHelper::getLikes($gifs, $router);

		$submitters = [];
		foreach ($json as $item) {
			$url = $item['url'];
			$likesCount = intval($item['total_count']);
			/** @var Gif $gif */
			$gif = FacebookHelper::findGif($gifs, $url);

			if (!array_key_exists($gif->getSubmittedBy(), $submitters)) {
				$submitters[$gif->getSubmittedBy()] = [
					'gifs' => [],
					'likes' => 0
				];
			}

			$submitters[$gif->getSubmittedBy()]['gifs'][] = $gif;
			$submitters[$gif->getSubmittedBy()]['likes'] += $likesCount;
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
	 * Get Facebook likes information from Facebook API
	 * @param array $gifs
	 * @return mixed
	 */
	private static function getLikes(array $gifs, Router $router)
	{
		// Build api call url
		$urls = [];
		foreach ($gifs as $gif)
			$urls[] = urlencode($router->generate('gif', ['permalink' => $gif->getPermalink()], true));

		$apiUrl = 'http://api.facebook.com/restserver.php?method=links.getStats&urls=' . implode(',', $urls) . '&format=json';
		$result = file_get_contents($apiUrl);
		return json_decode($result, true);
	}

    /**
     * Find one gif from its URL in a Gif array
     * @param $gifs
     * @param $url
     * @return Gif|null
     */
	private static function findGif($gifs, $url)
	{
		/** @var Gif $gif */
		foreach ($gifs as $gif)
		{
            if (Util::endsWith($url, $gif->getPermalink()))
				return $gif;
		}

		return null;
	}
}
