<?php

namespace LjdsBundle\Controller;

use DateTime;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Entity\ReportState;
use LjdsBundle\Helper\FacebookHelper;
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

				if ($gifState == GifState::PUBLISHED)
				{
					// Publish link to Facebook page
					FacebookHelper::publishLinkOnFacebook(
						$gif,
						$this->getParameter('facebook_app_id'), $this->getParameter('facebook_app_secret'), $this->getParameter('facebook_access_token'),
						$this->get('router')
					);
				}

				$gif->setCatchPhrase($caption);
				$gif->setGifStatus($gifState);
				if ($gifState == GifState::PUBLISHED)
					$gif->setPublishDate(new DateTime());

				$em->flush();
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
     * @Route("/stats/{type}")
     */
    public function statsAction($type)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var GifRepository $gifRepo */
        $gifRepo = $em->getRepository('LjdsBundle:Gif');

        $response = '';
        switch ($type)
        {
            case 'publish_queue': // How many gifs are waiting to be published
                $response = $gifRepo->getCountByGifState(GifState::ACCEPTED);
                break;
            case 'waiting_for_approval': // How many gifs are submitted and are waiting for approval
                $response = $gifRepo->getCountByGifState(GifState::SUBMITTED);
                break;
            default:
                $response = 'unknown_action';
                break;
        }

        return new Response($response);
    }
}
