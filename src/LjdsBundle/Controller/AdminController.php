<?php

namespace LjdsBundle\Controller;

use DateTime;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Entity\ReportState;
use LjdsBundle\Service\FacebookService;
use LjdsBundle\Service\TwitterService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminController extends Controller
{
	/**
	 * @Route("/admin/api", name="adminApi")
	 */
	public function adminApiAction(Request $request)
	{
		$post = $request->request;

		// Request integrity check
		if (!$post->has('api_key'))
			return $this->apiError('missing_api_key');

		if (!$this->checkApiKey($post->get('api_key')))
			return $this->apiError('wrong_api_key');

		if (!$post->has('action'))
			return $this->apiError('missing_action');


		$em = $this->getDoctrine()->getManager();
		$gifRepo = $em->getRepository('LjdsBundle:Gif');


		switch ($post->get('action'))
		{
			case 'change_gif_status':
				$check = $this->checkParameters($post, ['gif_id', 'new_gif_state', 'caption']);
				if ($check !== true)
					$this->apiError($check);

				/** @var Gif $gif */
				$gif = $gifRepo->find($post->get('gif_id'));

				if (!$gif)
					$this->apiError('unknown_gif');

				$caption = $post->get('caption');
				$gifState = GifState::fromName($post->get('new_gif_state'));

				if ($gifState == -1)
					return new JsonResponse(['success' => false]);

				$gif->setCaption($caption);
				$gif->setGifStatus($gifState);
                // Regenerate permalink in case of caption changed
                $gif->generateUrlReadyPermalink();

				$em->flush();

				if ($gifState == GifState::PUBLISHED)
					$this->publishGif($gif);

				break;
			case 'change_report_status':
				$check = $this->checkParameters($post, ['gif_id']);
				if ($check !== true)
					$this->apiError($check);

				/** @var Gif $gif */
				$gif = $gifRepo->find($post->get('gif_id'));

				if (!$gif)
					$this->apiError('unknown_gif');

				$gif->setReportStatus(ReportState::IGNORED);
				$em->flush();
				break;
			case 'delete_gif':
				$check = $this->checkParameters($post, ['gif_id']);
				if ($check !== true)
					$this->apiError($check);

				$gif = $gifRepo->find($post->get('gif_id'));

				if (!$gif)
					$this->apiError('unknown_gif');

				$em->remove($gif);
				$em->flush();
				break;
			default:
				return $this->apiError('unknown_action');
				break;
		}

		return new JsonResponse(['success' => true]);
	}

	private static function checkParameters(ParameterBag $post, $params)
	{
		foreach ($params as $param) {
			if (!array_key_exists($param, $post->all()))
				return 'missing_parameter('.$param.')';
		}
		return true;
	}

	private function checkApiKey($apiKey)
	{
		return $apiKey == $this->getParameter('admin_api_key');
	}

	private static function apiError($error)
	{
		return new JsonResponse([
			'success' => false,
			'error' => $error
		], 500);
	}

	public function publishGif(Gif $gif)
	{
		$em = $this->getDoctrine()->getManager();

		if (!$gif)
			return false;

		if (!$gif->getGifStatus() == GifState::ACCEPTED)
			return false;

		$gif->setPublishDate(new DateTime());
		$gif->setGifStatus(GifState::PUBLISHED);
		$gif->generateUrlReadyPermalink();

		if ($this->getParameter('facebook_autopost')) {
			/** @var FacebookService $facebookService */
			$facebookService = $this->get('app.facebook');
			$facebookService->postGif($gif);
		}
		if ($this->getParameter('twitter_autopost')) {
			/** @var TwitterService $twitterService */
			$twitterService = $this->get('app.twitter');
			$twitterService->postGif($gif);
		}

		$em->flush();

		return true;
	}


    /**
	 * @Route("/admin/{type}", name="admin")
     * @Route("/admin/")
     */
    public function adminAction($type='submitted')
    {
		$em = $this->getDoctrine()->getManager();
		/** @var GifRepository $gifRepo */
		$gifRepo = $em->getRepository('LjdsBundle:Gif');

		$gifs = [];
		switch ($type) {
			case 'submitted':
			case 'accepted':
			case 'refused':
				$gifState = GifState::fromName($type);
				$gifs = $gifRepo->findByGifState($gifState);
				break;
			case 'reported':
				$gifs = $gifRepo->getReportedGifs();
				break;
			default:
				throw new NotFoundHttpException();
				break;
		}

        $params = [
			'gifs' => $gifs,
			'type' => $type,
            'admin_api_key' => $this->getParameter('admin_api_key')
        ];

        return $this->render('LjdsBundle:Admin:index.html.twig', $params);
    }

    /**
     * @Route("/stats/{state}")
     */
    public function statsAction($state)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifRepo */
        $gifRepo = $em->getRepository('LjdsBundle:Gif');

		$gifState = GifState::fromName($state);

		if ($gifState == -1)
			return new Response('unknown_action');

		return new Response($gifRepo->getCountByGifState($gifState));
    }

	/**
	 * @Route("/cron/publishCron")
	 */
	public function publishCronAction(Request $request)
	{
		if (!$request->request->has('admin_api_key')
			|| $request->request->get('admin_api_key') != $this->getParameter('admin_api_key'))
			return new Response('invalid_action');

		$em = $this->getDoctrine()->getManager();
		/** @var GifRepository $gifRepository */
		$gifRepository = $em->getRepository('LjdsBundle:Gif');

		// Find next gif to publish
		$acceptedGifs = $gifRepository->findByGifState(GifState::ACCEPTED);

		if (count($acceptedGifs) > 0) {
			// Publish the first one (oldest one = FIFO)
			$gif = $acceptedGifs[0];

			$res = $this->publishGif($gif);

			if (!$res)
				return new Response('publish_failed');

			return new Response('publish_succeeded');
		}

		return new Response('empty_publish_queue');
	}
}
