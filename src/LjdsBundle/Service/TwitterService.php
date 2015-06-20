<?php
namespace LjdsBundle\Service;

use Codebird\Codebird;
use LjdsBundle\Entity\Gif;

class TwitterService
{
	protected $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function postGif($gifCaption, $gifUrl)
	{
		return $this->postTweet($gifCaption . ' ' . $gifUrl);
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
