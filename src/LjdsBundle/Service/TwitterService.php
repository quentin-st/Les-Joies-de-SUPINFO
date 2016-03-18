<?php
namespace LjdsBundle\Service;

use Codebird\Codebird;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Helper\Util;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

class TwitterService
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
		$gifUrl = $this->router->generate('gif', ['permalink' => $gif->getPermalink()], UrlGeneratorInterface::ABSOLUTE_URL);
		$gifUrl = Util::fixSymfonyGeneratedURLs($gifUrl);

        $tweetContent = $gif->generateTweet($gifUrl);

		return $this->postTweet($tweetContent);
	}

	private function postTweet($text)
	{
		Codebird::setConsumerKey(
			$this->container->getParameter('twitter_consumer_key'),
			$this->container->getParameter('twitter_consumer_secret')
		);

		$cb = Codebird::getInstance();

		$cb->setToken(
			$this->container->getParameter('twitter_access_token'),
			$this->container->getParameter('twitter_access_token_secret')
		);

		$reply = $cb->statuses_update('status='.$text);

		return $reply;
	}
}
