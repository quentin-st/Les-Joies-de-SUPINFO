<?php

namespace LjdsBundle\Command\Maintenance;

use Doctrine\ORM\EntityManager;
use LjdsBundle\Entity\Gif;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HttpDownloadedGifsToHttpsCommand extends ContainerAwareCommand
{
    const REGEX_HTTP = '^http:\/\/';
    const REGEX_HTTPS = '^https:\/\/';

    protected function configure()
    {
        $this
            ->setName('ljds:gifs:http-to-https')
            ->setDescription('Migrates all local HTTP gifs to HTTPS');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // Find downloaded gifs that are still in HTTP
        $domains = $this->getContainer()->getParameter('domains');
        $io->writeln('Found 3 domains: '.count($domains).': '.implode(', ', $domains));
        $httpsDomain = null;
        $httpDomains = [];

        foreach ($domains as $domain) {
            if (preg_match('/'.self::REGEX_HTTPS.'/', $domain)) {
                $httpsDomain = $domain;
            } else {
                $isHttp = preg_match('/'.self::REGEX_HTTP.'/', $domain);

                if (!$isHttp) {
                    $io->warning('Unable to identify domain scheme for '.$domain.', assuming http.');
                    $domain = 'http://'.$domain;
                }

                $httpDomains[] = $domain;
            }
        }

        // Get all gifs
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $gifs = $em->getRepository('LjdsBundle:Gif')->findAll();
        $toBeUpdated = [];

        /** @var Gif $gif */
        foreach ($gifs as $gif) {
            $matches = $this->matchesAnyHttpDomain($gif->getGifUrl(), $httpDomains);

            if ($matches !== false) {
                $toBeUpdated[] = [
                    'gif' => $gif,
                    'urlInfos' => $matches
                ];
            }
        }

        // Ask for confirmation
        if (empty($toBeUpdated)) {
            $io->success('Good news! All local gifs seems to be HTTPS ones.');
            return;
        }

        $io->writeln('Here are the changes that will be made:');
        $io->writeln(implode('\n', array_map(function ($infos) use ($httpsDomain) {
            /** @var Gif $gif */
            $gif = $infos['gif'];

            return $gif->getGifUrl().' -> '.$this->generateHTTPSUrl($infos, $httpsDomain);
        }, $toBeUpdated)));

        if (!$io->confirm('Do you want to apply these changes? It would be the right time to do a database backup right now.', false)) {
            return;
        }

        foreach ($toBeUpdated as $infos) {
            /** @var Gif $gif */
            $gif = $infos['gif'];
            $gif->setGifUrl($this->generateHTTPSUrl($infos, $httpsDomain));
        }

        $em->flush();

        $io->success('Updated '.count($toBeUpdated).' gifs, good job!');
    }

    /**
     * Checks if this $gifUrl matches any HTTP domains passed as parameter
     * @param $gifUrl
     * @param $domains
     * @return bool string|false
     */
    private function matchesAnyHttpDomain($gifUrl, $domains)
    {
        // /^http:\/\/(joies-de-supinfo.s-quent.in|www.joies-de-supinfo.fr)\/(.*)/
        $regex = '/'.self::REGEX_HTTP.'('.implode('|', $this->getDomainsHosts($domains)).')\/(.*)/';

        if (preg_match($regex, $gifUrl, $results)) {
            return [
                'domain' => $results[1],
                'path' => $results[2]
            ];
        }

        return false;
    }

    /**
     * Returns the domain host (http://www.google.fr => www.google.fr) for each domain
     * @param $domains
     * @return array
     */
    private function getDomainsHosts($domains)
    {
        $hosts = [];
        $regex = '/'.self::REGEX_HTTP.'(.*)'.'/';

        foreach ($domains as $domain) {
            preg_match($regex, $domain, $results);
            $hosts[] = $results[1];
        }

        return $hosts;
    }

    private function generateHTTPSUrl($gifInfos, $httpsDomain)
    {
        return $httpsDomain.'/'.$gifInfos['urlInfos']['path'];
    }
}
