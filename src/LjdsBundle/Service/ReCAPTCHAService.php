<?php
namespace LjdsBundle\Service;

class ReCAPTCHAService
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function checkCaptcha($captchaResponse)
    {
        $secret = $this->container->getParameter('recaptcha_secretkey');

        $postParameters = [
            'secret' => $secret,
            'response' => $captchaResponse
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        var_dump($response);

        curl_close($ch);

        $data = json_decode($response, true);
        return $data['success'];
    }
}
