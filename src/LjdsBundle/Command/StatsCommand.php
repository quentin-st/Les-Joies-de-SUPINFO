<?php

namespace LjdsBundle\Command;

use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get some stats about gifs.
 * Queried by munin
 */
class StatsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ljds:gifs:stats')
            ->setDescription('Get some stats about gifs')
            ->addArgument('gifState', InputArgument::REQUIRED, 'Which gifState do you want to stat?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var GifRepository $gifRepo */
        $gifRepo = $em->getRepository('LjdsBundle:Gif');

        $gifState = GifState::fromName($input->getArgument('gifState'));

        if ($gifState == -1) {
            $output->writeln('Unknown gifState');
            return;
        }

        $count = $gifRepo->getCountByGifState($gifState);
        $output->write($count);
    }
}
