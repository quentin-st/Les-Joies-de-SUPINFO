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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Email;

class GifsController extends Controller
{
	/**
	 * @Route("/", name="index", options={"sitemap"=true})
	 * @Route("/page/{page}", name="page")
	 */
	public function pageAction($page=1, Request $request)
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


		// Redirect when trying to hit wrong page
		$ref = new \ReflectionClass(get_class($pagination));
		$totalCountAttr = $ref->getProperty('totalCount');
		$totalCountAttr->setAccessible(true);
		$totalCount = $totalCountAttr->getValue($pagination);
		$pagesCount = ceil($totalCount/$gifsPerPage);

		if ($pagesCount == 0)
			throw new NotFoundHttpException();
		else if ($page < 1)
			return $this->redirect($this->generateUrl('page', ['page' => 1]));
		else if ($page > $pagesCount)
			return $this->redirect($this->generateUrl('page', ['page' => $pagesCount]));


		// Fetch likes count for those gifs
		$this->get('app.facebook_likes')->fetchLikes($pagination);


		return $this->render('LjdsBundle:Gifs:gifsList.html.twig', [
			'gifs' => $pagination,
			'pagination' => true,
			'trump' => $request->query->has('trump')
		]);
	}

	/**
	 * @Route("/top", name="top", options={"sitemap"=true})
	 */
	public function topGifsAction()
	{
		$gifs = $this->get('app.facebook_likes')->getTop();

		// Fetch likes count for those gifs
		$this->get('app.facebook_likes')->fetchLikes($gifs);

		return $this->render('LjdsBundle:Gifs:gifsList.html.twig', [
			'gifs' => $gifs,
			'pagination' => false
		]);
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

		return $this->redirectToRoute('gif', [
			'permalink' => $gif->getPermalink()
		]);
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

		// Fetch likes count for this gif
		$this->get('app.facebook_likes')->fetchLikes([$gif]);

		return $this->render('LjdsBundle:Gifs:gifPage.html.twig', [
			'gif' => $gif
		]);
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
			$email = $post->get('email');
			$email = $email == '' ? null : $email;

			// Validate email
			if ($email !== null) {
				$validator = $this->get('validator');

				$errors = $validator->validateValue($email, [new Email()]);

				if (count($errors) > 0)
					$gifSubmittedError = 'l\'adresse mail n\'est pas valide.';
			}

			$expire = time()+60*60*24*30;
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
				$gifRepo = $this->getDoctrine()->getRepository('LjdsBundle:Gif');
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
	 * This is endpoint is used by the /submit page to fetch trending gifs & search results
	 * @Route("/giphyProxy/", name="giphyProxy")
	 * @Method({"POST"})
	 */
	public function giphyApiProxyAction(Request $request)
	{
		$post = $request->request;

		if (!$post->has('action'))
			return new JsonResponse([ 'error' => 'Invalid action' ], 500);

		$giphy_api_key = $this->getParameter('giphy_api_key');
		$giphy_gifs_limit = $this->getParameter('giphy_gifs_limit');

		$offset = $post->get('offset', 0);

		// Generate API call URL depending on action (trending gifs / search)
		$action = $post->get('action');
		switch ($action)
		{
			case 'getTrendingGifs':
				$url = 'http://api.giphy.com/v1/gifs/trending'
					. '?api_key=' . $giphy_api_key
					. '&limit=' . $giphy_gifs_limit
					. '&offset=' . $offset;

				break;
			case 'search':
				if (!$post->has('keywords'))
					return new JsonResponse([ 'error' => 'Missing keywords' ], 500);

				$keywords = $post->get('keywords');
				$url = 'http://api.giphy.com/v1/gifs/search'
					. '?q=' . urlencode($keywords)
					. '&api_key=' . $giphy_api_key
					. '&limit=' . $giphy_gifs_limit
					. '&offset=' . $offset;

				break;
			default:
				return new JsonResponse([ 'error' => 'Invalid action' ], 500);
				break;
		}

		// Fetch result
		$apiResult = file_get_contents($url);

		if ($apiResult === false) {
			return new JsonResponse([ 'error' => 'Invalid Giphy response' ], 500);
		}

		// Decode response, build gifs list
		$json = json_decode($apiResult, true);
		$gifs = [];

		foreach ($json['data'] as $giphyGif) {
			$images = $giphyGif['images'];

			$gifs[] = [
				'preview_downsampled' => $images['fixed_width_downsampled']['url'],
				'preview' => $images['fixed_width']['url'],
				'image' => $images['downsized']['url'],
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
