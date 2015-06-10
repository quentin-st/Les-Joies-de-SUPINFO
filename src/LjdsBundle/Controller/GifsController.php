<?php

namespace LjdsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class GifsController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        return $this->render('LjdsBundle:Default:index.html.twig');
    }

    /**
     * @Route("/submit", name="submit")
     */
    public function submitAction()
    {

    }

    /**
     * @Route("/top", name="top")
     */
    public function topGifsAction()
    {

    }

    /**
     * @Route("/feed", name="feed")
     */
    public function feedAction()
    {

    }
}
