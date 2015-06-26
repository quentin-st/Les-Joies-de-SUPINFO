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

        // URLify parameters
        $postString = '';
        foreach ($postParameters as $key => $value)
            $postString .= $key . '=' . $value . '&';
        $postString = rtrim($postString, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, count($postString));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json')); // Assuming you're requesting JSON
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response);
        return $data['success'];
    }
}
