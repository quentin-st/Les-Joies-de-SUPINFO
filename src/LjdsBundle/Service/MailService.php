<?php

namespace LjdsBundle\Service;

use LjdsBundle\Entity\Gif;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MailService
{
    /** @var Swift_Mailer */
    private $mailer;
    /** @var \Twig_Environment */
    private $twig;
    /** @var Router */
    private $router;
    /** @var string */
    private $senderEmail;

    private $senderName = 'Les Joies de SUPINFO';

    public function __construct($mailer, $twig, $router, $sender_email)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->router = $router;
        $this->senderEmail = $sender_email;
    }

    public function sendGifApprovedMail(Gif $gif)
    {
        $gifPreview = $this->twig->render('LjdsBundle:Snippets:gif.html.twig', [
            'gif' => $gif,
            'class' => null
        ]);

        $this->sendFormattedMail(
            $gif->getEmail(),
            'Un de vos gifs été accepté !',
            '<p>Bonjour '.$gif->getSubmittedBy().',</p>'.
            '<p>Bonne nouvelle : votre gif "'.$gif->getCaption().'" a été accepté ! Il ne devrait pas tarder à être publié.</p>'.
            '<p>'.$gifPreview.'</p>'.
            '<p>Merci pour votre contribution !</p>'
        );
    }

    public function sendGifPublishedMail(Gif $gif)
    {
        $target = $this->router->generate('gif', ['permalink' => $gif->getPermalink()], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->sendFormattedMail(
            $gif->getEmail(),
            'Un de vos gifs vient d\'être publié !',
            '<p>Bonjour '.$gif->getSubmittedBy().',</p>'.
            '<p>Bonne nouvelle : votre gif "<a href="'.$target.'">'.$gif->getCaption().'</a>" vient d\'être publié !</p>'.
            '<p>Merci pour votre contribution !</p>'
        );
    }

    public function sendFormattedMail($to, $subject, $content)
    {
        $body = $this->twig->render('LjdsBundle:Snippets:mail.html.twig', [
            'content' => $content
        ]);

        $this->sendMail(
            $subject,
            $this->senderEmail,
            $this->senderName,
            $to,
            $body
        );
    }

    public function sendTestMail($to)
    {
        $this->sendMail(
            'Mails test succeeded',
            $this->senderEmail,
            $this->senderName,
            $to,
            'It works!'
        );
    }

    private function sendMail($subject, $fromMail, $fromName, $to, $messageBody)
    {
        $message = \Swift_Message::newInstance();
        $message
            ->setSubject($subject)
            ->setFrom([$fromMail => $fromName])
            ->setTo($to)
            ->setBody($messageBody, 'text/html', 'utf-8');

        $this->mailer->send($message);
    }
}
