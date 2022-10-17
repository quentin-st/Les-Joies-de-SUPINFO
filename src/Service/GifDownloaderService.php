<?php

namespace App\Service;

use Doctrine\ORM\EntityManager;
use App\Entity\Gif;
use App\Helper\Util;
use Symfony\Component\Routing\RouterInterface;

class GifDownloaderService
{
    public function __construct(
        private readonly string $projectDir,
        private readonly RouterInterface $router,
    )
    {
    }

    /**
     * Downloads a gif on the server. Useful if our domain is not accepted as referrer (avoids hotlinking)
     * @param  Gif    $gif
     * @return string
     */
    public function download(Gif $gif)
    {
        $downloadDir = $this->getDownloadDir();
        $gifUrl = $gif->getGifUrl();

        // Generate filename
        $fileName = $gif->getPermalink().'.'.Util::getFileExtension($gifUrl);

        $i = 0;
        while (file_exists($downloadDir.$fileName)) {
            $fileName = $gif->getPermalink().'_'.$i.'.'.Util::getFileExtension($gifUrl);
            ++$i;
        }

        // Download file
        file_put_contents($downloadDir.$fileName, fopen($gifUrl, 'r'));

        // Check if file has been successfully downloaded (permissions issues)
        if (!file_exists($downloadDir.$fileName)) {
            return false;
        }

        // Generate client-side URL
        $context = $this->router->getContext();
        $url = $context->getScheme().'://'. // http://
            $context->getHost().$context->getBaseUrl()
            .'/gifs/'.$fileName;
        $gif->setOriginalGifUrl($gifUrl);
        $gif->setGifUrl($url);

        return $url;
    }

    /**
     * Deletes the downloaded gif once the original gifs gets deleted
     * @param  Gif  $gif
     * @return bool
     */
    public function delete(Gif $gif)
    {
        $downloadDir = $this->getDownloadDir();

        // Get file path from gif URL
        $fileName = basename($gif->getGifUrl());

        if (file_exists($downloadDir.$fileName)) {
            unlink($downloadDir.$fileName);
            return true;
        } else {
            return false;
        }
    }

    private function getDownloadDir()
    {
        return $this->projectDir.'/../public/gifs/';
    }
}
