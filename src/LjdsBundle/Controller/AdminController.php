<?php

namespace LjdsBundle\Controller;

use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Entity\ReportState;
use LjdsBundle\Service\GifDownloaderService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminController extends Controller
{
	const GIFS_PER_PAGE = 9;

	private function getQueryBuilderByType($type)
	{
		$em = $this->getDoctrine()->getManager();
		/** @var GifRepository $gifRepo */
		$gifRepo = $em->getRepository('LjdsBundle:Gif');

		switch ($type) {
			case 'submitted':
			case 'accepted':
			case 'refused':
			case 'published':
				$gifState = GifState::fromName($type);
				return $gifRepo->findByGifState_queryBuilder($gifState);
				break;
			case 'reported':
				return $gifRepo->getReportedGifs_queryBuilder();
				break;
			default:
				throw new NotFoundHttpException();
				break;
		}
	}

	/**
	 * @Route("/admin/{type}/{page}", name="admin")
	 * @Route("/admin/")
	 */
	public function adminAction($type='submitted', $page=1)
	{
		$queryBuilder = self::getQueryBuilderByType($type);

		// Prepare counts
		$counts = [];
		foreach (GifState::getAll() as $gifType) {
			$query = $this->getQueryBuilderByType($gifType)->getQuery();
			$query->execute();

			$counts[$gifType] = count($query->getResult());
		}

		// Prepare pagination
		$page = intval($page);
		$paginator = $this->get('knp_paginator');
		$pagination = $paginator->paginate(
			$queryBuilder->getQuery(),
			$page,
			self::GIFS_PER_PAGE
		);
		$pagination->setUsedRoute('admin');

		$params = [
			'gifs' => $pagination,
			'page' => $page,
			'type' => $type,
			'typeLabel' => GifState::getLabel($type),
			'counts' => $counts,
			'admin_api_key' => $this->getParameter('admin_api_key')
		];

		return $this->render('LjdsBundle:Admin:index.html.twig', $params);
	}

	/**
	 * @Route("/admin/api", name="adminApi")
	 * @Method({"POST"})
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
		/** @var GifRepository $gifRepo */
		$gifRepo = $em->getRepository('LjdsBundle:Gif');

		// Result array returned to client
		$result = [];

		switch ($post->get('action')) {
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

				// Post-update actions
				switch ($gifState) {
					case GifState::ACCEPTED:
						if ($gif->getEmail() != null)
							$this->get('app.mail_service')->sendGifApprovedMail($gif);
						break;
					case GifState::PUBLISHED:
						if ($gifState == GifState::PUBLISHED)
							$this->get('app.gif')->publish($gif);
						break;
				}

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

				/** @var Gif $gif */
				$gif = $gifRepo->find($post->get('gif_id'));

				if (!$gif)
					$this->apiError('unknown_gif');

				// Delete downloaded gif if there is one
				if ($gif->getOriginalGifUrl() != null) {
					/** @var GifDownloaderService $gifDownloader */
					$gifDownloader = $this->get('app.gif_downloader');

					$gifDownloader->delete($gif);
				}

				$em->remove($gif);
				$em->flush();
				break;
			case 'download_gif':
				$check = $this->checkParameters($post, ['gif_id']);
				if ($check !== true)
					$this->apiError($check);

				/** @var Gif $gif */
				$gif = $gifRepo->find($post->get('gif_id'));

				if (!$gif)
					$this->apiError('unknown_gif');

				// Downloads a gif locally if the referrer (our domain) is blocked by the gif host
				/** @var GifDownloaderService $gifDownloader */
				$gifDownloader = $this->get('app.gif_downloader');

				$res = $gifDownloader->download($gif);

				if ($res !== false) {
					$em->flush();

					$result['gifUrl'] = $res;
				} else {
					$this->apiError('download_failed');
				}
				break;
			default:
				return $this->apiError('unknown_action');
				break;
		}

		return new JsonResponse(array_merge($result, ['success' => true]));
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
}
