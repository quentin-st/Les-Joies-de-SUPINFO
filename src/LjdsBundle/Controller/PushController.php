<?php

namespace LjdsBundle\Controller;

use Doctrine\ORM\EntityRepository;
use LjdsBundle\Entity\PushRegistration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/push")
 */
class PushController extends Controller
{
	/**
	 * @Route("/register")
	 * @Method({"POST"})
	 */
	public function register(Request $request)
	{
		$post = $request->request;

		if (!$post->has('id')) {
			return self::response(false, 'Missing id');
		}

		$regId = $post->get('id');
		$em = $this->getDoctrine()->getManager();
		/** @var EntityRepository $registrationRepo */
		$registrationRepo = $em->getRepository('LjdsBundle:PushRegistration');

		if (!$registrationRepo->findOneBy([
			'registrationId' => $regId
		])) {
			$registration = PushRegistration::fromId($post->get('id'));
			$em->persist($registration);

			$em->flush();

			return self::response(true);
		}

		return self::response(false, 'This id is already registered');
	}

	/**
	 * @Route("/worker.js")
	 */
	public function workerAction()
	{
		return new Response($this->get('twig')->render('@Ljds/Snippets/push-notifications-worker.js.twig'), 200, [
			'Content-Type' => 'text/javascript; charset=UTF-8'
		]);
	}

	private function response($success=true, $message=null)
	{
		return new JsonResponse([
			'success' => $success,
			'message' => $message
		], $success ? 200 : 500);
	}
}
