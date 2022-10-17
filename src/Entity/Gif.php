<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use App\Helper\Util;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="App\Repository\GifRepository")
 */
class Gif
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="catchPhrase", type="string", length=255)
     */
    private $caption;

    /**
     * @var \DateTime
     * @ORM\Column(name="submissionDate", type="datetime")
     */
    private $submissionDate;

    /**
     * @var string
     * @ORM\Column(name="submittedBy", type="string", length=255)
     */
    private $submittedBy;

    /**
     * @var \DateTime
     * @ORM\Column(name="publishDate", type="datetime", nullable=true)
     */
    private $publishDate;

    /**
     * @var int
     * @ORM\Column(name="gifStatus", type="integer")
     */
    private $gifStatus;

    /**
     * @var int
     * @ORM\Column(name="reportStatus", type="integer")
     */
    private $reportStatus;

    /**
     * Gif final URL.
     * @var string
     * @ORM\Column(name="fileName", type="string", length=255)
     */
    private $gifUrl;

    /**
     * Original gif URL. Populated when locally downloading a gif from admin:
     *  gifUrl will contain the local URL while originalGifUrl will contain the original gif URL.
     * @var string
     * @ORM\Column(name="originalGifUrl", type="string", length=255, nullable=true)
     */
    private $originalGifUrl;

    /**
     * Permalink for this gif (URL). Auto-generated from gif caption
     * @var string
     * @ORM\Column(name="permalink", type="string", length=255)
     */
    private $permalink;

    /**
     * Source page for this link. Auto-generated when choosing using the Giphy widget
     * @var string
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;

    /**
     * Label associated with this gif. Currently, it allows us to know if student or SUPINFO staff
     * @var string
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * Submitter's e-mail address
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @deprecated
     * @var int
     */
    private $likes;

    public function __construct()
    {
        $this->likes = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string $caption
     * @return Gif
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param  \DateTime $submissionDate
     * @return Gif
     */
    public function setSubmissionDate($submissionDate)
    {
        $this->submissionDate = $submissionDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSubmissionDate()
    {
        return $this->submissionDate;
    }

    /**
     * @param  string $submittedBy
     * @return Gif
     */
    public function setSubmittedBy($submittedBy)
    {
        $this->submittedBy = $submittedBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubmittedBy()
    {
        return $this->submittedBy;
    }

    /**
     * @param  \DateTime $publishDate
     * @return Gif
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * @param  int $gifStatus
     * @return Gif
     */
    public function setGifStatus($gifStatus)
    {
        $this->gifStatus = $gifStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getGifStatus()
    {
        return $this->gifStatus;
    }

    /**
     * @param  int $reportStatus
     * @return Gif
     */
    public function setReportStatus($reportStatus)
    {
        $this->reportStatus = $reportStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getReportStatus()
    {
        return $this->reportStatus;
    }

    /**
     * @return string
     */
    public function getGifUrl()
    {
        return $this->gifUrl;
    }

    /**
     * @param  string $gifUrl
     * @return $this
     */
    public function setGifUrl($gifUrl)
    {
        $this->gifUrl = $gifUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalGifUrl()
    {
        return $this->originalGifUrl;
    }

    /**
     * @param  string $originalGifUrl
     * @return Gif
     */
    public function setOriginalGifUrl($originalGifUrl)
    {
        $this->originalGifUrl = $originalGifUrl;
        return $this;
    }

    /**
     * @param  string $permalink
     * @return Gif
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink;
        return $this;
    }

    /**
     * @return string
     */
    public function getPermalink()
    {
        return $this->permalink;
    }

    /**
     * @param  string $source
     * @return Gif
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param  string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function generateUrlReadyPermalink()
    {
        $permalink = (new Slugify())->slugify($this->caption);
        $permalink = urlencode($permalink);
        $this->permalink = $permalink;
        return $permalink;
    }

    /**
     * Generates a tweet with a link, the caption and the SUPINFO hashtag
     * @param $url string generated using Router
     * @return string
     */
    public function generateTweet($url)
    {
        $tweetMaxLength = 140;
        $linkStrLength = 22;
        $hashTag = ' - Les Joies de #SUPINFO ';

        if ((strlen($this->getCaption()) + strlen($hashTag) + $linkStrLength) <= $tweetMaxLength) {
            // Good news, we don't have to trim anything
            return $this->getCaption().$hashTag.$url;
        } else {
            // Trim caption
            $availableLength = $tweetMaxLength - (strlen($hashTag) + $linkStrLength + strlen('...'));

            return substr($this->getCaption(), 0, $availableLength).'...'.$hashTag.$url;
        }
    }

    public function getFileType()
    {
        return Util::getFileExtension($this->getGifUrl());
    }

    public function toJson(RouterInterface $router): array
    {
        return [
            'caption' => $this->getCaption(),
            'type' => $this->getFileType(),
            'file' => $this->getGifUrl(),
            'permalink' => $router->generate('gif', ['permalink' => $this->getPermalink()], UrlGeneratorInterface::ABSOLUTE_URL),
            'publishDate' => $this->getPublishDate()->format('Y-m-d H:i')
        ];
    }
}
