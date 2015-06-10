<?php

namespace LjdsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AdminController extends Controller
{
    /**
     * @Route("/admin/", name="admin")
     */
    public function adminAction()
    {
        return $this->render('LjdsBundle:Admin:index.html.twig');
    }
}
