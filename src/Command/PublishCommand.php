<?php

namespace App\Command;

use App\Entity\GifState;
use App\Repository\GifRepository;
use App\Service\GifService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Publishes the oldest accepted Gif
 */
class PublishCommand extends Command
{
    public function __construct(
        private readonly GifRepository $gifRepo,
        private readonly GifService $gifService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('ljds:gifs:publish')
            ->setDescription('Publishes the oldest accepted Gif');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Find next gif to publish
        $acceptedGifs = $this->gifRepo->findByGifState(GifState::ACCEPTED);

        if (empty($acceptedGifs)) {
            $output->writeln('Empty publish queue.');
            return;
        }

        // Publish the first one (oldest one = FIFO)
        $gif = $acceptedGifs[0];
        if ($this->gifService->publish($gif)) {
            $output->writeln('Gif published!');
        } else {
            $output->writeln('Failed somewhere while publishing gif...');
        }
    }
}
