<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\GifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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

        $params = [
            'submitters' => $gifRepo->getTopSubmitters()
        ];

        return $this->render('LjdsBundle:Submitters:top.html.twig', $params);
    }

    /**
     * @Route("/submitters/{submitter}", name="submitter")
     */
    public function submitterGifsAction($submitter)
    {

    }
}
