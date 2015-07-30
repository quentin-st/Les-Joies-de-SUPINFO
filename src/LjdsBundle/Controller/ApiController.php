<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/random")
     * @Method({"GET"})
     */
    public function apiRandomAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        /** @var Gif $gif */
        $gif = $gifsRepo->getRandomGif();

        return new JsonResponse($this->getJson($gif));
    }

    /**
     * @Route("/last")
     * @Method({"GET"})
     */
    public function apiLastAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        /** @var Gif $gif */
        $gif = $gifsRepo->getLastPublishedGif();

        return new JsonResponse($this->getJson($gif));
    }

    private function getJson(Gif $gif)
    {
        return [
            'caption' => $gif->getCaption(),
            'type' => $gif->getFileType(),
            'file' => $gif->getGifUrl(),
            'permalink' => $this->generateUrl('gif', ['permalink' => $gif->getPermalink()], true)
        ];
    }
}
