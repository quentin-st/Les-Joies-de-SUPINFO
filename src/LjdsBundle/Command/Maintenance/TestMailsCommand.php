<?php

namespace LjdsBundle\Command\Maintenance;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends a test mail to the specified email address
 */
class TestMailsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('ljds:mail:test')
			->setDescription('Tests mails')
			->addArgument('to');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$to = $input->getArgument('to');

		if (!$to) {
			$dialog = $this->getHelper('dialog');
			$to = $dialog->ask($output, 'Please enter "to" address: ');
		}

		$this->getContainer()->get('app.mail_service')->sendTestMail($to);
		$output->writeln('Mail sent.');
	}
}
