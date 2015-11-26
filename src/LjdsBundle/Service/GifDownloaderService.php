<?php

namespace LjdsBundle\Service;

use Doctrine\ORM\EntityManager;
use LjdsBundle\Entity\Gif;
use LjdsBundle\Helper\Util;

class GifDownloaderService
{
    /** @var string */
    private $kernelRootDir;

    private $requestContextHost;
    private $requestContextScheme;
    private $requestContextBaseUrl;

    private $em;

    public function __construct($kernelRootDir, $requestContextHost, $requestContextScheme, $requestContextBaseUrl, EntityManager $em)
    {
        $this->kernelRootDir = $kernelRootDir;
        $this->requestContextHost = $requestContextHost;
        $this->requestContextScheme = $requestContextScheme;
        $this->requestContextBaseUrl = $requestContextBaseUrl;
        $this->em = $em;
    }

    /**
     * Downloads a gif on the server. Useful if our domain is not accepted as referrer (avoids hotlinking)
     * @param Gif $gif
     * @return string
     */
    public function download(Gif $gif)
    {
        $downloadDir = $this->kernelRootDir.'/../web/gifs/';
        $gifUrl = $gif->getGifUrl();

        // Keep original gif URL
        $gif->setOriginalGifUrl($gifUrl);

        // Generate filename
        $fileName = $gif->getPermalink() . '.' . Util::getFileExtension($gifUrl);

        $i=0;
        while (file_exists($downloadDir.$fileName)) {
            $fileName = $gif->getPermalink() . '_' . $i . '.' . Util::getFileExtension($gifUrl);
            $i++;
        }

        // Download file
        file_put_contents($downloadDir.$fileName, fopen($gifUrl, 'r'));

        // Generate client-side URL
        $url = $this->requestContextScheme . '://' . // http://
            $this->requestContextHost . $this->requestContextBaseUrl
            . 'gifs/' . $fileName;
        $gif->setGifUrl($url);

        return $url;
    }
}
