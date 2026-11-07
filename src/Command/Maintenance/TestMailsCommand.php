<?php

namespace App\Command\Maintenance;

use App\Service\MailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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

    protected function configure(): void
    {
        $this
            ->setName('ljds:mail:test')
            ->setDescription('Tests mails')
            ->addArgument('to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $to = $input->getArgument('to');

        if (!$to) {
            $to = $this->getHelper('question')->ask($input, $output, new Question('Please enter "to" address: '));
        }

        $this->mailService->sendTestMail($to);
        $output->writeln('Mail sent.');
        return Command::SUCCESS;
    }
}
