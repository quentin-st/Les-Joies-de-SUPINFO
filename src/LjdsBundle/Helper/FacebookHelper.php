<?php

namespace LjdsBundle\Helper;

use LjdsBundle\Entity\Gif;
use SimpleXMLElement;
use Symfony\Component\Routing\Router;

class FacebookHelper
{
	public static function getFacebookLikes(/** @var Gif[] $gifs */$gifs, Router $router)
	{
        // TODO this should be enhanced

		// Build api call url
		$urls = '';
		foreach ($gifs as $gif)
			$urls .= $router->generate('gif', ['permalink' => $gif->getPermalink()], true).',';

		$apiUrl = 'http://api.facebook.com/restserver.php?method=links.getStats&urls=' . $urls;
		$result = file_get_contents($apiUrl);
		$xmlRes = new SimpleXMLElement($result);

		$likes = [];
		for ($i=0; $i<count($xmlRes[0]); $i++) {
			$url = $xmlRes->link_stat[$i]->url;
			$likesCount = (int)$xmlRes->link_stat[$i]->like_count;

            if ($likesCount > 0) {
                $likes[] = [
                    'url' => $url,
                    'likes' => $likesCount,
                    'gif' => FacebookHelper::findGif($gifs, $url)
                ];
            }
		}

		// Sort array
		usort($likes, function($a, $b) {
			return $b['likes'] - $a['likes'];
		});

		return $likes;
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
