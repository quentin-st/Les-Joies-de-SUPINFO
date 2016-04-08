<?php

namespace LjdsBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
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
        // Redirect to main route
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
        // Redirect to main route
        if (!$this->isMainRoute($request, 'api_gif_latest'))
            return $this->redirectToRoute('api_gif_latest');

        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        /** @var Gif $gif */
        $gif = $gifsRepo->getLastPublishedGif();

        return new JsonResponse($this->getJsonForGif($gif));
    }

    /**
     * Returns X latest published gifs
     * @Route("/gif/list")
     * @Method({"GET"})
     */
    public function apiLatestGifsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $maxResults = $request->query->getInt('count', 20);

        // Create query
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $query = $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery();

        $query->execute();

        $gifs = [];
        foreach ($query->getResult() as $gif)
            $gifs[] = $this->getJsonForGif($gif);

        return new JsonResponse($gifs);
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
