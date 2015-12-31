<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Helper\FacebookHelper;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubmittersController extends Controller
{
    /**
     * @Route("/submitters", name="topSubmitters")
     */
    public function submittersTopAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifRepo */
        $gifRepo = $em->getRepository('LjdsBundle:Gif');

        $gifs = $gifRepo->findByGifState(GifState::PUBLISHED);

        $params = [
            //'submitters' => FacebookHelper::getFacebookLikesGroupedBySubmitter($gifs, $this->get('router')),
            'submitters' => [
                [
                    'submitter' => 'Frédéric',
                    'likes' => '34',
                    'gifs_count' => '68'
                ],
                [
                    'submitter' => 'chteuchteu',
                    'likes' => '19',
                    'gifs_count' => '30'
                ],
                [
                    'submitter' => 'Alex',
                    'likes' => '92',
                    'gifs_count' => '31'
                ],
                [
                    'submitter' => 'Lucas',
                    'likes' => '62',
                    'gifs_count' => '17'
                ],
                [
                    'submitter' => 'Koala',
                    'likes' => '43',
                    'gifs_count' => '10'
                ],
                [
                    'submitter' => 'vins',
                    'likes' => '38',
                    'gifs_count' => '2'
                ],
                [
                    'submitter' => 'Krhyt',
                    'likes' => '36',
                    'gifs_count' => '4'
                ],
                [
                    'submitter' => 'Napo',
                    'likes' => '30',
                    'gifs_count' => '6'
                ],
                [
                    'submitter' => 'Shabbollin',
                    'likes' => '23',
                    'gifs_count' => '5'
                ],
                [
                    'submitter' => 'Hop5',
                    'likes' => '22',
                    'gifs_count' => '3'
                ],
                [
                    'submitter' => 'David',
                    'likes' => '21',
                    'gifs_count' => '4'
                ],
            ]
        ];

        return $this->render('LjdsBundle:Submitters:top.html.twig', $params);
    }

    /**
     * @Route("/submitter/{submitter}", name="submitter")
     */
    public function submitterGifsAction($submitter)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifRepo */
        $gifRepo = $em->getRepository('LjdsBundle:Gif');

        $gifs = $gifRepo->findBySubmitter($submitter);

        // Don't serve pages for unknown persons
        if (count($gifs) == 0)
            throw new NotFoundHttpException();

        $params = [
            'submitter' => $submitter,
            'gifs' => $gifs
        ];

        return $this->render('LjdsBundle:Submitters:submitter.html.twig', $params);
    }
}
