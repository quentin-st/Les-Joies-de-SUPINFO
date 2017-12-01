<?php

namespace LjdsBundle\Command\Maintenance;

use LjdsBundle\Entity\Gif;
use LjdsBundle\Entity\GifRepository;
use LjdsBundle\Entity\GifState;
use LjdsBundle\Service\FacebookLikesService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Prints the likes count for one specific gif
 */
class DebugLikesCountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ljds:likes:debug')
            ->setDescription('Prints likes counts for one gif')
            ->addArgument(
                'permalink',
                InputArgument::OPTIONAL,
                'Which gif should we check?'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var GifRepository $gifRepo */
        $gifRepo = $em->getRepository('LjdsBundle:Gif');
        $gifs = $gifRepo->findByGifState(GifState::PUBLISHED);

        $permalink = $input->getArgument('permalink');

        if (!$permalink || !$gifRepo->findOneBy(['permalink' => $permalink])) {
            // Print some recent gifs and ask again
            $gifsTable = new Table($output);
            $gifsTable->setHeaders(['Id', 'Submitted by', 'Permalink']);

            for ($i = 0; $i < min(20, count($gifs)); ++$i) {
                /** @var Gif $gif */
                $gif = $gifs[$i];

                $gifsTable->addRow([
                    $gif->getId(),
                    $gif->getSubmittedBy(),
                    $gif->getPermalink()
                ]);
            }

            $gifsTable->render();

            // Ask for it
            $hints = [];
            foreach ($gifs as $gif) {
                $hints[] = $gif->getPermalink();
            }

            /** @var QuestionHelper $helper */
            $helper = $this->getHelper('question');
            $permalink = $helper->ask($input, $output, new ChoiceQuestion('Which permalink?', $hints));
        }

        $gif = $gifRepo->findOneBy(['permalink' => $permalink]);

        if (!$gif) {
            $output->writeln('No gif found for permalink '.$permalink);
            return;
        }

        // Get likes!
        /** @var FacebookLikesService $facebookLikesService */
        $facebookLikesService = $this->getContainer()->get('app.facebook_likes');

        // Check cached value
        $output->writeln('CACHED VALUE FOR "'.$gif->getPermalink().'":');
        $facebookLikesService->fetchLikes([$gif]);
        $output->writeln($gif->getLikes().' like(s)');

        // Fetch value by calling Facebook API
        $output->writeln('LIVE VALUE FOR "'.$gif->getPermalink().'":');
        // Generate URLs
        $urls = $facebookLikesService->getURLsForGifs([$gif]);
        $likes = $facebookLikesService->getLikesFromFacebookAPI($urls);

        foreach ($likes as $url => $likesCount) {
            $output->writeln($url.' => '.$likesCount.' like(s)');
        }

        // That's it!
    }
}
