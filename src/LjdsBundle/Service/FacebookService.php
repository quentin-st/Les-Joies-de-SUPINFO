<?php
namespace LjdsBundle\Service;

use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use LjdsBundle\Entity\Gif;

class FacebookService
{
	protected $container;
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

		FacebookSession::setDefaultApplication($appId, $appSecret);

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

		$link = $this->router->generate('gif', ['permalink' => $gif->getPermalink()], true);

		try {
			$requestParaps = [
				'access_token' => $accessToken,
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
}
