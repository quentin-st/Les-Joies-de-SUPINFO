<?php

namespace LjdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LjdsBundle\Helper\Util;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="GifRepository")
 */
class Gif
{
    /**
     * @var integer
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
     * @var integer
     * @ORM\Column(name="gifStatus", type="integer")
     */
    private $gifStatus;

    /**
     * @var integer
     * @ORM\Column(name="reportStatus", type="integer")
     */
    private $reportStatus;

    /**
     * @var string
     * @ORM\Column(name="fileName", type="string", length=255)
     */
    private $gifUrl;

    /**
     * @var string
     * @ORM\Column(name="permalink", type="string", length=255)
     */
    private $permalink;

    /**
     * @var string
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;

    /**
     * @var string
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     */
    private $label;


    /**
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $caption
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
     * @param \DateTime $submissionDate
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
     * @param string $submittedBy
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
     * @param \DateTime $publishDate
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
     * @param integer $gifStatus
     * @return Gif
     */
    public function setGifStatus($gifStatus)
    {
        $this->gifStatus = $gifStatus;
        return $this;
    }

    /**
     * @return integer 
     */
    public function getGifStatus()
    {
        return $this->gifStatus;
    }

    /**
     * @param integer $reportStatus
     * @return Gif
     */
    public function setReportStatus($reportStatus)
    {
        $this->reportStatus = $reportStatus;
        return $this;
    }

    /**
     * @return integer 
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
     * @param string $gifUrl
     * @return $this
     */
    public function setGifUrl($gifUrl)
    {
        $this->gifUrl = $gifUrl;
        return $this;
    }

    /**
     * @param string $permalink
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
     * @param string $source
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


    public function generateUrlReadyPermalink()
    {
        $permalink = $this->getCaption();
        // Replace spaces with -
        $permalink = str_replace(' ', '-', $permalink);
        // Translate accents to non-accent chars
        $permalink = Util::replaceAccentedCharacters($permalink);
        // Remove all non-alphabetic chars
        $permalink = preg_replace('/[^A-Za-z0-9\-]/', '', $permalink);
        // Tolowerize permalink
        $permalink = strtolower($permalink);

        $permalink = urlencode($permalink);
        $this->permalink = $permalink;
        return $permalink;
    }


	public function getFileType()
	{
		return Util::getFileExtension($this->getGifUrl());
	}
}
