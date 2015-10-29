<?php

namespace LjdsBundle\Twig;

use Doctrine\ORM\EntityManager;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Helper\Util;

class LjdsExtension extends \Twig_Extension
{
	/** @var EntityManager $em */
	protected $em;

	public function __construct($em)
	{
		$this->em = $em;
	}


    public function getFunctions()
    {
        return [
			new \Twig_SimpleFunction(
				'publicationCountdown',
				[$this, 'publicationCountdown'],
				['needs_environment' => true, 'is_safe' => ['html']]
			)
		];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('relativeDate', [$this, 'relativeDate'])
        ];
    }

	public function publicationCountdown(\Twig_Environment $twig_environment)
	{
		/** @var GifRepository $gifRepo */
		$gifRepo = $this->em->getRepository('LjdsBundle:Gif');
		$dateTime = $gifRepo->getUpcomingPublication();

		if ($dateTime !== false)
			return $twig_environment->render('LjdsBundle:Snippets:countdown.html.twig', ['datetime' => $dateTime]);
		else
			return '';
	}

    public function relativeDate(\DateTime $datetime)
    {
        return Util::relativeTime($datetime);
    }

    public function getName() { return 'ljds_extension'; }
}
