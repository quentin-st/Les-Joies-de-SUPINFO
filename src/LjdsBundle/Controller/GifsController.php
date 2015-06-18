<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\FileType;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Entity\ReportState;
use LjdsBundle\Helper\Util;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
		$gifsPerPage = intval($this->getParameter('gifs_per_page'));
        $pagesCount = $gifsRepo->getPaginationPagesCount(GifState::PUBLISHED, $gifsPerPage);

        if ($page <= 0)
            return $this->redirect($this->generateUrl('page', ['page' => 0]));
        else if ($page > $pagesCount)
            return $this->redirect($this->generateUrl('page', ['page' => $pagesCount]));

        $params = [
            'gifs' => $gifsRepo->findByGifState(GifState::PUBLISHED, $page, $gifsPerPage),
            'homePage' => $page == 1,
            'pagination' => [
                'page' => $page,
                'pageCount' => $pagesCount
            ]
        ];
        return $this->render('LjdsBundle:Gifs:gifsList.html.twig', $params);
    }

	/**
	 * @Route("/top", name="top")
	 */
	public function topGifsAction()
	{
		$em = $this->getDoctrine()->getManager();
		/** @var GifRepository $gifsRepo */
		$gifsRepo = $em->getRepository('LjdsBundle:Gif');

		$params = [
			'gifs' => $gifsRepo->getTop(20, $this->get('router'))
		];

		return $this->render('LjdsBundle:Gifs:gifsList.html.twig', $params);
	}

	/**
	 * @Route("/gif/{permalink}", name="gif")
	 */
	public function gifAction($permalink)
	{
		$em = $this->getDoctrine()->getManager();
		/** @var GifRepository $gifsRepo */
		$gifsRepo = $em->getRepository('LjdsBundle:Gif');

		$gif = $gifsRepo->findOneBy([
			'permalink' => $permalink
		]);

		if (!$gif)
			throw new NotFoundHttpException();

		$params = [
			'gif' => $gif
		];

		return $this->render('LjdsBundle:Gifs:gifPage.html.twig', $params);
	}

    /**
     * @Route("/submit", name="submit")
     */
    public function submitAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $response = new Response();

        $gifSubmitted = false;
        $gifSubmittedError = false;

        // Form is submitted
        if ($request->request->has('catchPhrase')) {
            $post = $request->request;

            // Check if mandatory fields are filled up
            if (trim($post->get('submittedBy')) == ''
                || trim($post->get('catchPhrase')) == ''
                || trim($post->get('gifUrl')) == '')
            {
                $gifSubmittedError = "un des champs requis n'est pas renseigné, veuillez rééssayer.";
            }

			// Check if URL is a gif/mp4 video
			$gifUrl = $post->get('gifUrl');
			if (!Util::extensionMatches($gifUrl, 'gif')
				&& !Util::extensionMatches($gifUrl, 'mp4'))
			{
				$gifSubmittedError = "l'URL ne semble pas être celle d'un fichier gif";
			}

            $gifSubmitted = true;
            $submittedBy = $post->get('submittedBy');
            $catchPhrase = $post->get('catchPhrase');
            $source = $post->get('source');
            $label = $post->get('label');

            // Create cookie with submittedBy value
            $cookie = new Cookie('submittedBy', $submittedBy, time()+60*60*24*30);
            $response->headers->setCookie($cookie);

            if ($gifSubmittedError === false) {
                $gif = new Gif();
                $gif->setCatchPhrase($catchPhrase);
                $gif->setGifUrl($gifUrl);
                $gif->setReportStatus(ReportState::NONE);
                $gif->setGifStatus(GifState::SUBMITTED);
                $gif->generateUrlReadyPermalink();
                $gif->setSubmissionDate(new \DateTime());
                $gif->setSubmittedBy($submittedBy);
                $gif->setSource($source);
                $gif->setLabel($label);

                $em->persist($gif);
                $em->flush();
            } else {
                $params['submitError'] = $gifSubmittedError;
            }
        }

        $params['submittedBy'] = $request->cookies->has('submittedBy')
            ? $request->cookies->get('submittedBy')
            : '';
        $params['submitted'] = $gifSubmitted;


        $response->setContent(
            $this->renderView('LjdsBundle:Gifs:submit.html.twig', $params)
        );

        return $response;
    }

    /**
     * @Route("/feed", name="feed")
     */
    public function feedAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        $params = [
            'gifs' => $gifsRepo->getForFeed()
        ];

        $response = new Response(
            $this->renderView('LjdsBundle:Default:feed.html.twig', $params)
        );
        $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');

        return $response;
    }

    /**
     * @Route("/giphyProxy/", name="giphyProxy")
     */
    public function giphyApiProxyAction(Request $request)
    {
        $post = $request->request;

        if (!$post->has('action'))
            return new JsonResponse([ 'error' => 'Invalid action' ], 500);

        $giphy_api_key = $this->getParameter('giphy_api_key');
        $giphy_gifs_limit = $this->getParameter('giphy_gifs_limit');

        $action = $post->get('action');
        switch ($action)
        {
            case 'getTrendingGifs':
                $url = 'http://api.giphy.com/v1/gifs/trending?api_key=' . $giphy_api_key . '&limit=' . $giphy_gifs_limit;
                break;
            case 'search':
                if (!$post->has('keywords'))
                    return new JsonResponse([ 'error' => 'Missing keywords' ], 500);

                $keywords = $post->get('keywords');
                $url = 'http://api.giphy.com/v1/gifs/search?q=' . urlencode($keywords) . '&api_key=' . $giphy_api_key . '&limit=' . $giphy_gifs_limit;
                break;
            default:
                return new JsonResponse([ 'error' => 'Invalid action' ], 500);
                break;
        }

        $apiResult = file_get_contents($url);

        if ($apiResult === false) {
            return new JsonResponse([ 'error' => 'Invalid Giphy response' ], 500);
        }

        $json = json_decode($apiResult, true);
        $gifs = [];

        foreach ($json['data'] as $giphyGif) {
            $gifs[] = [
                'image' => $giphyGif['images']['downsized']['url'],
                'url' => $giphyGif['bitly_url']
            ];
        }

        return new JsonResponse([
            'gifs' => $gifs,
            'success' => true
        ]);
    }

    /**
     * @Route("/abuse")
     */
    public function abuseAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

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
