<?php
namespace LjdsBundle\Service;

use Codebird\Codebird;
use LjdsBundle\Entity\Gif;

class TwitterService
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
		$gifUrl = $this->router->generate('gif', ['permalink' => $gif->getPermalink()], true);

        $tweetMaxLength = 140;
        $linkStrLength = 22;
        $hashTag = " - Les Joies de #SUPINFO ";

        if ((strlen($gif->getCaption()) + strlen($hashTag) + $linkStrLength) <= $tweetMaxLength) {
            // Good news, we don't have to trim anything
            $tweetContent = $gif->getCaption() . $hashTag . $gifUrl;
        } else {
            // Trim caption
            $availableLength = $tweetMaxLength - (strlen($hashTag) + $linkStrLength + strlen("..."));

            $tweetContent = substr($gif->getCaption(), 0, $availableLength) . " - Les Joies de #Supinfo " . $gifUrl;
        }

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
