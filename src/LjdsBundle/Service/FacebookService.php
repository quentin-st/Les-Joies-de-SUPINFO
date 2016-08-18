<?php
namespace LjdsBundle\Service;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Facebook;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Helper\Util;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class FacebookService
{
	/** @var Container */
	protected $container;
	/** @var Router */
	protected $router;

	public function __construct($container, $router)
	{
		$this->container = $container;
		$this->router = $router;
	}

	public function postGif(Gif $gif)
	{
		$appId = $this->container->getParameter('facebook_app_id');
		$appSecret = $this->container->getParameter('facebook_app_secret');
		$accessToken = $this->container->getParameter('facebook_access_token');

        $fb = new Facebook([
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v2.7'
        ]);

		$link = $this->router->generate('gif', ['permalink' => $gif->getPermalink()], UrlGeneratorInterface::ABSOLUTE_URL);
		$link = Util::fixSymfonyGeneratedURLs($link);

		try {
			$requestParams = [
				'access_token' => $accessToken,
				'link' => $link,
				'message' => $gif->getCaption()
			];

			// Only provide picture if this is a gif
			if ($gif->getFileType() == 'gif')
                $requestParams['picture'] = $gif->getGifUrl();

            $request = $fb->post('/joiesDeSupinfo/feed', $requestParams, $accessToken);

			/*$response = */$request->getGraphNode();
			//echo "Posted with id: " . $response->getProperty('id');
		} catch(FacebookResponseException $e) {
			//echo "Exception occured, code: " . $e->getCode();
			//echo " with message: " . $e->getMessage();
			return false;
		}

		return true;
	}
}
