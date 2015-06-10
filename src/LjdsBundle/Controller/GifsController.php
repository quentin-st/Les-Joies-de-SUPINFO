<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class GifsController extends Controller
{
    /**
     * @Route("/", name="index")
     * @Route("/page/{page}", name="page")
     */
    public function pageAction($page=1)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        // Pagination
        $page = intval($page);

        $params = [
            'gifs' => $gifsRepo->findByGifState(GifState::PUBLISHED, $page),
            'homePage' => $page == 1,
            'pagination' => [
                'page' => $page,
                'pageCount' => $gifsRepo->getPaginationPagesCount(GifState::PUBLISHED)
            ]
        ];
        return $this->render('LjdsBundle:Gifs:index.html.twig', $params);
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
