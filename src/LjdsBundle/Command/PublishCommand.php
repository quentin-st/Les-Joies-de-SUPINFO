<?php

namespace LjdsBundle\Command;

use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Service\GifService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ljds:publish')
            ->setDescription('Publishes the oldest accepted Gif');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        /** @var GifRepository $gifRepository */
        $gifRepository = $em->getRepository('LjdsBundle:Gif');

        // Find next gif to publish
        $acceptedGifs = $gifRepository->findByGifState(GifState::ACCEPTED);

        if (empty($acceptedGifs)) {
            $output->writeln('Empty publish queue.');
            return;
        }

        // Publish the first one (oldest one = FIFO)
        $gif = $acceptedGifs[0];
        /** @var GifService $gifService */
        $gifService = $this->getContainer()->get('app.gif');

        if ($gifService->publish($gif))
            $output->writeln('Gif published!');
        else
            $output->writeln('Failed somewhere while publishing gif...');
    }
}
