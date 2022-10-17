<?php

namespace App\Controller;

use App\Repository\GifRepository;
use App\Entity\Gif;
use App\Entity\GifState;
use App\Entity\ReportState;
use App\Helper\Util;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;

class GifsController extends AbstractController
{
    /**
     * @Route("/", name="index", options={"sitemap"=true}, defaults={"page": 1})
     * @Route("/page/{page}", name="page", defaults={"page": 1}, requirements={"page": "\d+"})
     */
    public function pageAction(EntityManagerInterface $em, PaginatorInterface $paginator, int $page, string $_route)
    {
        // Create query
        $qb = $em->createQueryBuilder();
        $qb->select('g')
            ->from(Gif::class, 'g')
            ->where('g.gifStatus = '.GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC');

        // Pagination
        $page = (int) $page;
        $gifsPerPage = (int) ($this->getParameter('gifs_per_page'));

        // Redirect /page to /
        if ($page == 1 && $_route === 'page') {
            return $this->redirectToRoute('index');
        }

        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $page,
            $gifsPerPage
        );
        $pagination->setUsedRoute('page');

        // Redirect when trying to hit wrong page
        $totalCount = Util::getPaginationTotalCount($pagination);
        $pagesCount = ceil($totalCount / $gifsPerPage);

        if ($pagesCount === 0) {
            throw new NotFoundHttpException();
        } elseif ($page < 1) {
            return $this->redirectToRoute('page', ['page' => 1]);
        } elseif ($page > $pagesCount) {
            return $this->redirectToRoute('page', ['page' => $pagesCount]);
        }

        return $this->render('/Gifs/gifsList.html.twig', [
            'gifs' => $pagination,
            'pagination' => true,
            'page' => $page,
        ]);
    }

    /**
     * @Route(
     *     "/{route}/random",
     *     name="randomGif",
     *     requirements={
     *         "route": "(gif|widget)"
     *     },
     *     defaults={
     *         "route": "gif"
     *     }
     * )
     */
    public function randomAction(EntityManagerInterface $em, string $route, Request $request)
    {
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository(Gif::class);

        /** @var Gif $gif */
        $gif = $gifsRepo->getRandomGif();

        if (!$gif) {
            throw new NotFoundHttpException();
        }
        // Get back current GET params and pass them to the next page
        $params = array_merge($request->query->all(), [
            'permalink' => $gif->getPermalink(),
            'route' => $route
        ]);

        return $this->redirectToRoute('gif', $params);
    }

    /**
     * @Route("/gif/{permalink}", name="gif")
     */
    public function gifAction(EntityManagerInterface $em, $permalink, Request $request)
    {
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository(Gif::class);

        /** @var Gif $gif */
        $gif = $gifsRepo->findOneBy([
            'permalink' => $permalink
        ]);

        if (!$gif) {
            throw new NotFoundHttpException();
        }
        // Check if gif has been published
        if ($gif->getGifStatus() != GifState::PUBLISHED) {
            throw new NotFoundHttpException();
        }

        return $this->render('/Gifs/gifPage.html.twig', [
            'gif' => $gif,
        ]);
    }

    /**
     * @Route("/submit", name="submit", options={"sitemap"=true})
     */
    public function submitAction(Request $request, EntityManagerInterface $em)
    {
        $response = new Response();

        $gifSubmitted = false;
        $gifSubmittedError = false;

        // Form is submitted
        $post = $request->request;
        if ($post->has('caption')) {
            // Check if mandatory fields are filled up
            if (trim($post->get('submittedBy')) == ''
                || trim($post->get('caption')) == ''
                || trim($post->get('gifUrl')) == '') {
                $gifSubmittedError = "un des champs requis n'est pas renseigné, veuillez rééssayer.";
            }

            // Check if URL is a gif/mp4 video
            $gifUrl = $post->get('gifUrl');
            $gifSubmitted = true;
            $submittedBy = $post->get('submittedBy');
            $caption = $post->get('caption');
            $source = $post->get('source');
            $label = $post->get('label');
            $email = $post->get('email');
            $email = $email == '' ? null : $email;

            // Validate email
            if ($email !== null) {
                $validator = $this->get('validator');

                $errors = $validator->validateValue($email, [new Email()]);

                if (count($errors) > 0) {
                    $gifSubmittedError = 'l\'adresse mail n\'est pas valide.';
                }
            }

            $expire = time() + 60 * 60 * 24 * 30;
            // Create cookie with submittedBy value
            $cookie = new Cookie('submittedBy', $submittedBy, $expire);
            $response->headers->setCookie($cookie);

            // Create cookie with email value
            $cookie = new Cookie('email', $email, $expire);
            $response->headers->setCookie($cookie);

            if ($gifSubmittedError === false) {
                $gif = new Gif();
                $gif->setCaption($caption);
                $gif->setGifUrl($gifUrl);
                $gif->setReportStatus(ReportState::NONE);
                $gif->setGifStatus(GifState::SUBMITTED);
                $gif->generateUrlReadyPermalink();
                $gif->setSubmissionDate(new \DateTime());
                $gif->setSubmittedBy($submittedBy);
                $gif->setSource($source);
                $gif->setLabel($label);
                $gif->setEmail($email);

                $em->persist($gif);
                $em->flush();

                /** @var GifRepository $gifRepo */
                $gifRepo = $em->getRepository(Gif::class);
                $params['estimatedPublishDate'] = $gifRepo->getEstimatedPublicationDate();
            } else {
                $params['submitError'] = $gifSubmittedError;
            }
        }

        $params['submittedBy'] = $request->cookies->has('submittedBy')
            ? $request->cookies->get('submittedBy')
            : '';
        $params['email'] = $request->cookies->has('email')
            ? $request->cookies->get('email')
            : '';
        $params['submitted'] = $gifSubmitted;

        $response->setContent(
            $this->renderView('/Gifs/submit.html.twig', $params)
        );

        return $response;
    }

    /**
     * @Route("/feed/", name="feed")
     * @Route("/feed")
     */
    public function feedAction(EntityManagerInterface $em)
    {
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository(Gif::class);

        $params = [
            'gifs' => $gifsRepo->getForFeed()
        ];

        $response = new Response(
            $this->renderView('/Default/feed.html.twig', $params)
        );
        $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');

        return $response;
    }

    /**
     * This is endpoint is used by the /submit page to fetch trending gifs & search results
     * @Route("/giphyProxy/", name="giphyProxy", methods={"POST"})
     */
    public function giphyApiProxyAction(Request $request)
    {
        $post = $request->request;

        if (!$post->has('action')) {
            return new JsonResponse(['error' => 'Invalid action'], 500);
        }

        $giphy_api_key = $this->getParameter('giphy_api_key');
        $giphy_gifs_limit = 48;

        $offset = $post->get('offset', 0);

        // Generate API call URL depending on action (trending gifs / search)
        $action = $post->get('action');
        switch ($action) {
            case 'getTrendingGifs':
                $url = 'http://api.giphy.com/v1/gifs/trending'
                    .'?api_key='.$giphy_api_key
                    .'&limit='.$giphy_gifs_limit
                    .'&offset='.$offset;

                break;
            case 'search':
                if (!$post->has('keywords')) {
                    return new JsonResponse(['error' => 'Missing keywords'], 500);
                }

                $keywords = $post->get('keywords');
                $url = 'http://api.giphy.com/v1/gifs/search'
                    .'?q='.urlencode($keywords)
                    .'&api_key='.$giphy_api_key
                    .'&limit='.$giphy_gifs_limit
                    .'&offset='.$offset;

                break;
            default:
                return new JsonResponse(['error' => 'Invalid action'], 500);
                break;
        }

        // Fetch result
        $apiResult = file_get_contents($url);

        if ($apiResult === false) {
            return new JsonResponse(['error' => 'Invalid Giphy response'], 500);
        }

        // Decode response, build gifs list
        $json = json_decode($apiResult, true);
        $gifs = [];

        $httpToHttps = function ($url) {
            return preg_replace('/^http:/i', 'https:', $url);
        };

        foreach ($json['data'] as $giphyGif) {
            $images = $giphyGif['images'];

            $gifs[] = [
                'preview_downsampled' => $httpToHttps($images['fixed_width_downsampled']['url']),
                'preview' => $httpToHttps($images['fixed_width']['url']),
                'image' => $httpToHttps($images['downsized']['url']),
                'url' => $giphyGif['bitly_url']
            ];
        }

        // Compute pagination infos
        $data_pagination = $json['pagination'];
        $count = $data_pagination['count'];
        $offset = $data_pagination['offset'];
        $pagination = [
            'count' => $count,
            'offset' => $offset,
            'has_more' => true
        ];
        // total_count may be missing (for trending search for example)
        if (array_key_exists('total_count', $data_pagination)) {
            $totalCount = $data_pagination['total_count'];

            $pagination['total_count'] = $totalCount;
            $pagination['has_more'] = $totalCount > $count + $offset;
        }

        return new JsonResponse([
            'gifs' => $gifs,
            'pagination' => $pagination,
            'success' => true
        ]);
    }

    /**
     * @Route("/abuse")
     */
    public function abuseAction(Request $request, EntityManagerInterface $em)
    {
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository(Gif::class);

        $post = $request->request;

        if (!$post->has('id')) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid request'
            ], 500);
        }

        /** @var Gif $gif */
        $gif = $gifsRepo->find($post->get('id'));

        if (!$gif) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Gif not found'
            ], 404);
        }

        switch ($gif->getReportStatus()) {
            case ReportState::REPORTED:
                $message = "Ce gif a déjà été reporté par quelqu'un, nous y jetterons un œil dès que possible";
                $class = 'alert-warning';
                break;
            case ReportState::IGNORED:
                $message = 'La modération a décidé de ne pas supprimer ce gif malgré un précédent signalement.';
                $class = 'alert-danger';
                break;
            default:
                $gif->setReportStatus(ReportState::REPORTED);
                $em->flush();
                $message = "Merci d'avoir signalé ce gif, nous y jetterons un œil dès que possible";
                $class = 'alert-info';
                break;
        }

        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'class' => $class
        ]);
    }
}
