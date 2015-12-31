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
            'submitters' => FacebookHelper::getFacebookLikesGroupedBySubmitter($gifs, $this->get('router'))
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
