<?php

namespace LjdsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Posts a link to this gif one more time on Facebook and Twitter
 * Useful if one or another failed
 */
class RepublishCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('ljds:gifs:republish')
			->setDescription('Posts a link to this gif one more time on Facebook and Twitter')
			->addArgument('permalink', InputArgument::REQUIRED, 'Which gif do you want to republish?');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$em = $this->getContainer()->get('doctrine')->getManager();

		$permalink = $input->getArgument('permalink');
		$gif = $em->getRepository('LjdsBundle:Gif')->findOneBy(['permalink' => $permalink]);

		if (!$gif) {
			$output->writeln('There\'s no gif with this permalink ('.$permalink.').');
			return;
		}

		if ($this->getContainer()->getParameter('facebook_autopost')) {
			$output->writeln('Posting on Facebook...');
			if ($this->getContainer()->get('app.facebook')->postGif($gif))
				$output->writeln('    Done!');
			else
				$output->writeln('    Fail...');
		}
		else
			$output->writeln('Facebook autopost is disabled, skipping this one');

		if ($this->getContainer()->getParameter('twitter_autopost')) {
			$output->writeln('Posting on Twitter...');
			if ($this->getContainer()->get('app.twitter')->postGif($gif))
				$output->writeln('    Done!');
			else
				$output->writeln('    Fail...');
		}
		else
			$output->writeln('Twitter autopost is disabled, skipping this one');
	}
}
