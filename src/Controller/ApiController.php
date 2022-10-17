<?php

namespace App\Controller;

use App\Repository\GifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Gif;
use App\Entity\GifState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    const LIST_DEFAULT = 20;
    const LIST_MAX = 75;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface $router,
    )
    {
    }

    private function isMainRoute(Request $request, $mainRoute)
    {
        return $request->get('_route') === $mainRoute;
    }

    /**
     * @Route("/gif/random", name="api_gif_random", methods={"GET"})
     * @Route("/random", name="api_gif_random_old", methods={"GET"})
     */
    public function apiRandomGifAction(Request $request)
    {
        // Redirect to main route
        if (!$this->isMainRoute($request, 'api_gif_random')) {
            return $this->redirectToRoute('api_gif_random');
        }

        /** @var GifRepository $gifsRepo */
        $gifsRepo = $this->em->getRepository(Gif::class);

        /** @var Gif $gif */
        $gif = $gifsRepo->getRandomGif();

        return new JsonResponse($gif->toJson($this->router));
    }

    /**
     * Returns X latest published gifs
     * @Route("/gif/list", methods={"GET"})
     */
    public function apiLatestGifsAction(Request $request)
    {
        $maxResults = $request->query->get('count', self::LIST_DEFAULT);

        if (!filter_var($maxResults, FILTER_VALIDATE_INT) || $maxResults > self::LIST_MAX) {
            $maxResults = self::LIST_DEFAULT;
        }

        // Create query
        /** @var QueryBuilder $qb */
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select('g')
            ->from(Gif::class, 'g')
            ->where('g.gifStatus = '.GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery();

        $query->execute();

        $gifs = array_map(function (Gif $gif) {
            return $gif->toJson($this->router);
        }, $query->getResult());

        return new JsonResponse($gifs);
    }
}
