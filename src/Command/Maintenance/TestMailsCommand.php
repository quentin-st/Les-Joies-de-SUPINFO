<?php

namespace App\Command\Maintenance;

use App\Service\MailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends a test mail to the specified email address
 */
class TestMailsCommand extends Command
{
    public function __construct(
        private readonly MailService $mailService,
    )
    {
        parent::__construct();
    }

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

        $this->mailService->sendTestMail($to);
        $output->writeln('Mail sent.');
    }
}
