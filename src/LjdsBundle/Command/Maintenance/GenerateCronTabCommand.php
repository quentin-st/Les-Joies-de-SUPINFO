<?php

namespace LjdsBundle\Command\Maintenance;

use LjdsBundle\Helper\AutoPostHelper;
use LjdsBundle\Helper\WeekPart;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Prints crontab instructions for the cron, according to publication times
 *  defined in AutoPostHelper
 */
class GenerateCronTabCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ljds:cron:generate-cron-tab')
            ->setDescription('Generates instructions to be put in the server cron file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = '/var/www/joies-de-supinfo/app/console ljds:publish';

        $cronDow = [
            WeekPart::WEEK_DAYS => '1-5',
            WeekPart::WEEK_END => '6-7'
        ];
        $labels = [
            WeekPart::WEEK_DAYS => 'Week days',
            WeekPart::WEEK_END => 'Weekend'
        ];

        $output->writeln('# Joies de SUPINFO');
        foreach ([WeekPart::WEEK_DAYS, WeekPart::WEEK_END] as $weekPart) {
            $output->writeln('# '.$labels[$weekPart]);

            foreach (AutoPostHelper::getPublicationTimes($weekPart) as $job) {
                $hours = explode(':', $job)[0];
                $minutes = explode(':', $job)[1];

                $output->writeln($minutes.' '.$hours.' * * '.$cronDow[$weekPart].' '.$command);
            }

            $output->writeln('');
        }
    }
}
