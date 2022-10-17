<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/rulesOfTheGame", name="rulesOfTheGame", options={"sitemap"=true})
     */
    public function rulesOfTheGameAction()
    {
        return $this->render('/Default/rulesOfTheGame.html.twig');
    }

    /**
     * @Route("/cookies", name="cookies", options={"sitemap"=true})
     */
    public function cookiesInfosAction()
    {
        return $this->render('/Default/cookies.html.twig');
    }
}
