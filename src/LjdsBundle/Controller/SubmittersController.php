<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\GifRepository;
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
        return $this->render('LjdsBundle:Submitters:top.html.twig', [
            'submitters' => $this->get('app.facebook_likes')->getTopSubmitters()
        ]);
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

        return $this->render('LjdsBundle:Submitters:submitter.html.twig', [
            'submitter' => $submitter,
            'gifs' => $gifs
        ]);
    }
}
