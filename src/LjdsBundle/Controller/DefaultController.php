<?php

namespace LjdsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/rulesOfTheGame", name="rulesOfTheGame", options={"sitemap"=true})
     */
    public function rulesOfTheGameAction()
    {
        return $this->render('LjdsBundle:Default:rulesOfTheGame.html.twig');
    }

    /**
     * @Route("/cookies", name="cookies", options={"sitemap"=true})
     */
    public function cookiesInfosAction()
    {
        return $this->render('LjdsBundle:Default:cookies.html.twig');
    }
}
