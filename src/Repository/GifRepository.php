<?php

namespace App\Repository;

use App\Entity\Gif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Helper\AutoPostHelper;
use App\Helper\WeekPart;
use App\Entity\GifState;
use Doctrine\Persistence\ManagerRegistry;

/**
 * GifRepository
 */
class GifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gif::class);
    }

    public function findByGifState($gifState)
    {
        $query = $this->findByGifState_queryBuilder($gifState)->getQuery();
        $query->execute();
        return $query->getResult();
    }

    public function findByGifState_queryBuilder($gifState)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb->select('g')
            ->from('App\Entity\Gif', 'g')
            ->where('g.gifStatus = '.$gifState)
            ->orderBy('g.publishDate', 'DESC')
            ->addOrderBy('g.submissionDate', 'ASC');
    }

    public function getReportedGifs_queryBuilder($ignored = false)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('App\Entity\Gif', 'g');

        if ($ignored) {
            $qb->where('g.reportStatus > 0');
        } else {
            $qb->where('g.reportStatus = 1');
        }

        return $qb;
    }

    public function getForFeed()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('App\Entity\Gif', 'g')
            ->where('g.gifStatus = '.GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC');

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }

    public function getRandomGif()
    {
        $count = $this->createQueryBuilder('g')
            ->select('COUNT(g)')
            ->where('g.gifStatus = '.GifState::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->createQueryBuilder('g')
            ->setFirstResult(rand(0, $count - 1))
            ->setMaxResults(1)
            ->where('g.gifStatus = '.GifState::PUBLISHED)
            ->getQuery()
            ->getSingleResult();
    }

    public function getLastPublishedGif()
    {
        return $this->createQueryBuilder('g')
            ->where('g.gifStatus = '.GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Returns when the recently submitted gif will be published
     */
    public function getEstimatedPublicationDate()
    {
        $remainingGifs = count($this->findByGifState(GifState::ACCEPTED));

        $currentDate = new \DateTime();
        while ($remainingGifs >= 0) {
            $dow = (int) ($currentDate->format('w'));
            switch ($dow) {
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                    // Week days
                    $remainingGifs -= count(AutoPostHelper::getPublicationTimes(WeekPart::WEEK_DAYS));
                    break;
                case 6:
                case 0:
                    // Weekend
                    $remainingGifs -= count(AutoPostHelper::getPublicationTimes(WeekPart::WEEK_END));

                    break;
            }

            $currentDate->modify('+1 day');
        }

        return $currentDate;
    }
}
