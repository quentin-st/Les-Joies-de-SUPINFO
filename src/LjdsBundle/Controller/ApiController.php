<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    private function isMainRoute(Request $request, $mainRoute)
    {
        return $request->get('_route') === $mainRoute;
    }

    /**
     * @Route("/gif/random", name="api_gif_random")
     * @Route("/random", name="api_gif_random_old")
     * @Method({"GET"})
     */
    public function apiRandomGifAction(Request $request)
    {
        if (!$this->isMainRoute($request, 'api_gif_random'))
            return $this->redirectToRoute('api_gif_random');

        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        /** @var Gif $gif */
        $gif = $gifsRepo->getRandomGif();

        return new JsonResponse($this->getJsonForGif($gif));
    }

    /**
     * @Route("/gif/latest", name="api_gif_latest")
     * @Route("/last", name="api_gif_latest_old")
     * @Method({"GET"})
     */
    public function apiLatestGifAction(Request $request)
    {
        if (!$this->isMainRoute($request, 'api_gif_latest'))
            return $this->redirectToRoute('api_gif_latest');

        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        /** @var Gif $gif */
        $gif = $gifsRepo->getLastPublishedGif();

        return new JsonResponse($this->getJsonForGif($gif));
    }

    private function getJsonForGif(Gif $gif)
    {
        return [
            'caption' => $gif->getCaption(),
            'type' => $gif->getFileType(),
            'file' => $gif->getGifUrl(),
            'permalink' => $this->generateUrl('gif', ['permalink' => $gif->getPermalink()], UrlGeneratorInterface::ABSOLUTE_URL)
        ];
    }
}
