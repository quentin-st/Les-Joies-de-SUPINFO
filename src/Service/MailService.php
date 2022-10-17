<?php

namespace App\Service;

use App\Entity\Gif;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class MailService
{
    private const SENDER_NAME = 'Les Joies de SUPINFO';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
        private readonly string $senderEmail,
    ) {
    }

    public function sendGifApprovedMail(Gif $gif)
    {
        $gifPreview = $this->twig->render('/Snippets/gif.html.twig', [
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
        $body = $this->twig->render('/Snippets/mail.html.twig', [
            'content' => $content
        ]);

        $this->sendMail(
            $subject,
            $to,
            $body
        );
    }

    public function sendTestMail($to)
    {
        $this->sendMail(
            'Mails test succeeded',
            $to,
            'It works!'
        );
    }

    private function sendMail(string $subject, string $to, string $messageBody)
    {
        $email = (new Email())
            ->from(new Address($this->senderEmail, self::SENDER_NAME))
            ->to($to)
            ->subject($subject)
            ->html($messageBody);

        $this->mailer->send($email);
    }
}
