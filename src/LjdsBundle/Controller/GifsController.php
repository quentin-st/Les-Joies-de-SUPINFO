<?php

namespace LjdsBundle\Controller;

use Doctrine\ORM\EntityManager;
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
     * @Route("/", name="index", options={"sitemap"=true})
     * @Route("/page/{page}", name="page")
     */
    public function pageAction($page=1)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        // Create query
        $qb = $em->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC');

        // Pagination
        $page = intval($page);
        $gifsPerPage = intval($this->getParameter('gifs_per_page'));

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $qb->getQuery(),
            $page,
            $gifsPerPage
        );
        $pagination->setUsedRoute('page');


        // Redirect on wrong page
        $ref = new \ReflectionClass(get_class($pagination));
        $totalCountAttr = $ref->getProperty('totalCount');
        $totalCountAttr->setAccessible(true);
        $totalCount = $totalCountAttr->getValue($pagination);
        $pagesCount = ceil($totalCount/$gifsPerPage);

        if ($page < 1)
            return $this->redirect($this->generateUrl('page', ['page' => 1]));
        else if ($page > $pagesCount)
            return $this->redirect($this->generateUrl('page', ['page' => $pagesCount]));

        $params = [
            'gifs' => $pagination,
            'pagination' => true
        ];
        return $this->render('LjdsBundle:Gifs:gifsList.html.twig', $params);
    }

	/**
	 * @Route("/top", name="top", options={"sitemap"=true})
	 */
	public function topGifsAction()
	{
		$em = $this->getDoctrine()->getManager();
		/** @var GifRepository $gifsRepo */
		$gifsRepo = $em->getRepository('LjdsBundle:Gif');

		$params = [
			'gifs' => $gifsRepo->getTop(20, $this->get('router')),
            'pagination' => false
		];

		return $this->render('LjdsBundle:Gifs:gifsList.html.twig', $params);
	}

    /**
     * @Route("/gif/random", name="randomGif")
     */
    public function randomAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifsRepo */
        $gifsRepo = $em->getRepository('LjdsBundle:Gif');

        /** @var Gif $gif */
        $gif = $gifsRepo->getRandomGif();

        if (!$gif)
            throw new NotFoundHttpException();

        return $this->redirect($this->generateUrl('gif', ['permalink' => $gif->getPermalink()]));
    }

	/**
	 * @Route("/gif/{permalink}", name="gif")
	 */
	public function gifAction($permalink)
	{
		$em = $this->getDoctrine()->getManager();
		/** @var GifRepository $gifsRepo */
		$gifsRepo = $em->getRepository('LjdsBundle:Gif');

		/** @var Gif $gif */
		$gif = $gifsRepo->findOneBy([
			'permalink' => $permalink
		]);

		if (!$gif)
			throw new NotFoundHttpException();

		// Check if gif has been published
		if ($gif->getGifStatus() != GifState::PUBLISHED)
			throw new NotFoundHttpException();

		$params = [
			'gif' => $gif
		];

		return $this->render('LjdsBundle:Gifs:gifPage.html.twig', $params);
	}

    /**
     * @Route("/submit", name="submit", options={"sitemap"=true})
     */
    public function submitAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $response = new Response();

        $gifSubmitted = false;
        $gifSubmittedError = false;

        // Form is submitted
        $post = $request->request;
        if ($post->has('caption')) {
            // Check if mandatory fields are filled up
            if (trim($post->get('submittedBy')) == ''
                || trim($post->get('caption')) == ''
                || trim($post->get('gifUrl')) == '')
            {
                $gifSubmittedError = "un des champs requis n'est pas renseigné, veuillez rééssayer.";
            }

			// Check if URL is a gif/mp4 video
			$allowedFilesTypes = ['gif', 'mp4', 'webm', 'ogg'];
			$gifUrl = $post->get('gifUrl');
			if (!in_array(Util::getFileExtension($gifUrl), $allowedFilesTypes))
			{
				$gifSubmittedError = "l'URL ne semble pas être celle d'un fichier gif. Les types autorisés sont : gif, mp4, webm et ogg.";
			}

            $gifSubmitted = true;
            $submittedBy = $post->get('submittedBy');
            $caption = $post->get('caption');
            $source = $post->get('source');
            $label = $post->get('label');

            // Create cookie with submittedBy value
            $cookie = new Cookie('submittedBy', $submittedBy, time()+60*60*24*30);
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

                $em->persist($gif);
                $em->flush();

                $gifRepo = $this->getDoctrine()->getRepository('LjdsBundle:Gif');
                $params['estimatedPublishDate'] = $gifRepo->getEstimatedPublicationDate();
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
     * @Route("/feed/", name="feed")
     * @Route("/feed")
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
