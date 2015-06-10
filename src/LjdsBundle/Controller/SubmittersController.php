<?php

namespace LjdsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SubmittersController extends Controller
{
    /**
     * @Route("/submitters", name="topSubmitters")
     */
    public function submittersTopAction()
    {

    }

    /**
     * @Route("/submitters/{submitter}", name="submitter")
     */
    public function submitterGifsAction($submitter)
    {

    }
}
