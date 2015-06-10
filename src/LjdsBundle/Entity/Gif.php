<?php

namespace LjdsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gif
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="LjdsBundle\Entity\GifRepository")
 */
class Gif
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="catchPhrase", type="string", length=255)
     */
    private $catchPhrase;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submissionDate", type="datetime")
     */
    private $submissionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="submittedBy", type="string", length=255)
     */
    private $submittedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="publishDate", type="datetime", nullable=true)
     */
    private $publishDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="gifStatus", type="integer")
     */
    private $gifStatus;

    /**
     * @var integer
     *
     * @ORM\Column(name="reportStatus", type="integer")
     */
    private $reportStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="fileName", type="string", length=255)
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="permalink", type="string", length=255)
     */
    private $permalink;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255)
     */
    private $source;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set catchPhrase
     *
     * @param string $catchPhrase
     * @return Gif
     */
    public function setCatchPhrase($catchPhrase)
    {
        $this->catchPhrase = $catchPhrase;

        return $this;
    }

    /**
     * Get catchPhrase
     *
     * @return string 
     */
    public function getCatchPhrase()
    {
        return $this->catchPhrase;
    }

    /**
     * Set submissionDate
     *
     * @param \DateTime $submissionDate
     * @return Gif
     */
    public function setSubmissionDate($submissionDate)
    {
        $this->submissionDate = $submissionDate;

        return $this;
    }

    /**
     * Get submissionDate
     *
     * @return \DateTime 
     */
    public function getSubmissionDate()
    {
        return $this->submissionDate;
    }

    /**
     * Set submittedBy
     *
     * @param string $submittedBy
     * @return Gif
     */
    public function setSubmittedBy($submittedBy)
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    /**
     * Get submittedBy
     *
     * @return string 
     */
    public function getSubmittedBy()
    {
        return $this->submittedBy;
    }

    /**
     * Set publishDate
     *
     * @param \DateTime $publishDate
     * @return Gif
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;

        return $this;
    }

    /**
     * Get publishDate
     *
     * @return \DateTime 
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    /**
     * Set gifStatus
     *
     * @param integer $gifStatus
     * @return Gif
     */
    public function setGifStatus($gifStatus)
    {
        $this->gifStatus = $gifStatus;

        return $this;
    }

    /**
     * Get gifStatus
     *
     * @return integer 
     */
    public function getGifStatus()
    {
        return $this->gifStatus;
    }

    /**
     * Set reportStatus
     *
     * @param integer $reportStatus
     * @return Gif
     */
    public function setReportStatus($reportStatus)
    {
        $this->reportStatus = $reportStatus;

        return $this;
    }

    /**
     * Get reportStatus
     *
     * @return integer 
     */
    public function getReportStatus()
    {
        return $this->reportStatus;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return Gif
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set permalink
     *
     * @param string $permalink
     * @return Gif
     */
    public function setPermalink($permalink)
    {
        $this->permalink = $permalink;

        return $this;
    }

    /**
     * Get permalink
     *
     * @return string 
     */
    public function getPermalink()
    {
        return $this->permalink;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return Gif
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string 
     */
    public function getSource()
    {
        return $this->source;
    }

    public function generateUrlReadyPermalink()
    {
        $permalink = $this->getCatchPhrase();
        $permalink = str_replace(' ', '-', $permalink);
        $permalink = preg_replace('/[^A-Za-z0-9\-]/', '', $permalink);
        $permalink = strtolower($permalink);
        $permalink = urlencode($permalink);
        $this->permalink = $permalink;
        return $permalink;
    }
}

abstract class GifState
{
    const SUBMITTED = 0;
    const ACCEPTED = 1;
    const REFUSED = 2;
    const PUBLISHED = 3;
}

abstract class ReportState
{
    const NONE = 0;
    const REPORTED = 1;
    const IGNORED = 2;
}
