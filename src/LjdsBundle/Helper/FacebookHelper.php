<?php

namespace LjdsBundle\Helper;

use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
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
			$likesCount = intval($xmlRes->link_stat[$i]->like_count);

            // Each time a gif is published, it is posted on the Facebook page's wall
            // Se it has been liked if the likesCount is > than 1
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

	public static function publishLinkOnFacebook(Gif $gif, $facebookAppId, $facebookAppSecret, $facebookAccessToken, Router $router)
	{
		FacebookSession::setDefaultApplication($facebookAppId, $facebookAppSecret);

		// Open Facebook SDK session
		$session = FacebookSession::newAppSession();
		// To validate the session:
		try {
			$session->validate();
		} catch (FacebookRequestException $ex) {
			// Session not valid, Graph API returned an exception with the reason.
			//echo $ex->getMessage();
			return false;
		} catch (\Exception $ex) {
			// Graph API returned info, but it may mismatch the current app or have expired.
			//echo $ex->getMessage();
			return false;
		}

		$link = $router->generate('gif', ['permalink' => $gif->getPermalink()], true);

		try {
            $requestParaps = [
                'access_token' => $facebookAccessToken,
                'link' => $link,
                'message' => $gif->getCaption()
            ];

            // Only provide picture if this is a gif
            if ($gif->getFileType() == 'gif')
                $requestParaps['picture'] = $gif->getGifUrl();

            $facebookRequest = new FacebookRequest($session, 'POST', '/joiesDeSupinfo/feed', $requestParaps);

			/*$response = */$facebookRequest->execute()->getGraphObject();
			//echo "Posted with id: " . $response->getProperty('id');
		} catch(FacebookRequestException $e) {
			//echo "Exception occured, code: " . $e->getCode();
			//echo " with message: " . $e->getMessage();
			return false;
		}

		return true;
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
