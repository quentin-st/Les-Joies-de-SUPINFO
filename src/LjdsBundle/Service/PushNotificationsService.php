<?php

namespace LjdsBundle\Service;

use Buzz\Browser;
use Buzz\Client\FileGetContents;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Doctrine\ORM\EntityManager;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\PushRegistration;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class PushNotificationsService
{
    const GCM_ENDPOINT_URI = 'https://android.googleapis.com/gcm/send';
    const GCM_ENDPOINT_RESOURCE = '/gcm/send';
    const GCM_ENDPOINT_HOST = 'https://android.googleapis.com';

    /** @var EntityManager */
    private $em;
    /** @var Browser */
    private $browser;
    /** @var Router */
    private $router;
    /** @var string */
    private $apiKey;

    public function __construct(EntityManager $em, Browser $browser, Router $router, $apiKey)
    {
        $this->em = $em;
        $this->browser = $browser;
        $this->router = $router;
        $this->apiKey = $apiKey;
    }

    /**
     * Sends a notification when a new gif is published
     * @return array
     */
    public function notify(Gif $gif)
    {
        return $this->doRequest($this->getRegistrationIds(), [
            'gif' => $gif->toJson($this->router)
        ]);
    }

    /**
     * Tests notification. It isn't quite different from notify for now
     * @return array
     */
    public function test()
    {
        return $this->doRequest($this->getRegistrationIds(), [
            'success' => true,
            'message' => 'It works! :-)'
        ]);
    }

    private function getRegistrationIds()
    {
        $pushRegistrationRepo = $this->em->getRepository('LjdsBundle:PushRegistration');
        $registrations = $pushRegistrationRepo->findAll();

        return array_map(function (PushRegistration $registration) {
            return $registration->getRegistrationId();
        }, $registrations);
    }

    /**
     * Send a notification
     * @param  array $registrationIds
     * @param  array $data            payload
     * @return array
     */
    private function doRequest(array $registrationIds, array $data = [])
    {
        $request = new Request(Request::METHOD_POST, self::GCM_ENDPOINT_RESOURCE, self::GCM_ENDPOINT_HOST);
        $request->addHeader('Authorization: key='.$this->apiKey);
        $request->addHeader('Content-Type: application/json');
        $request->setContent(json_encode([
            'registration_ids' => $registrationIds,
            'data' => $data
        ]));

        $response = new Response();

        $client = new FileGetContents();
        $client->send($request, $response);

        return [
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'content' => $response->getContent()
        ];
    }
}
