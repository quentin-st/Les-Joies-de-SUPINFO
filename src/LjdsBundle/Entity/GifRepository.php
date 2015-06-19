<?php

namespace LjdsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use LjdsBundle\Helper\FacebookHelper;
use PDO;
use Symfony\Component\Routing\Router;

/**
 * GifRepository
 */
class GifRepository extends EntityRepository
{
    public function findByGifState($gifState, $page=-1, $gifsPerPage=5)
    {
        $firstResult = $gifsPerPage * $page - $gifsPerPage;

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . $gifState)
            ->orderBy('g.publishDate', 'DESC');

		if ($page != -1) {
			$qb->setFirstResult($firstResult)
				->setMaxResults($gifsPerPage);
		}

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }

	public function getTop($amount, Router $router)
	{
		$gifs = $this->findByGifState(GifState::PUBLISHED);

        if (count($gifs) == 0)
            return [];

		$likes = FacebookHelper::getFacebookLikes($gifs, $router);

		$list = [];
		foreach ($likes as $like)
			$list[] = $like['gif'];

        // Take the $amount top ones
        return array_slice($list, 0, $amount);
	}

    public function getReportedGifs($ignored = false)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g');

        if ($ignored)
            $qb->where('g.reportStatus > 0');
        else
            $qb->where('g.reportStatus = 1');

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }

    public function findBySubmitter($submitter)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->andWhere('g.submittedBy = :submittedBy')
            ->setParameter('submittedBy', $submitter)
            ->orderBy('g.publishDate', 'DESC');

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }

    public function getPaginationPagesCount($gifState, $gifsPerPage)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(g.id)')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . $gifState);
        $query = $qb->getQuery();

        $gifsCount = intval($query->getSingleScalarResult());

        return ceil($gifsCount/$gifsPerPage);
    }

    public function getTopSubmitters()
    {
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare('SELECT submittedBy as name, COUNT(*) as gifsCount
                              FROM gif
                              WHERE gifStatus = ' . GifState::PUBLISHED . '
                              GROUP BY submittedBy ORDER BY gifsCount DESC');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getForFeed()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('g')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->orderBy('g.publishDate', 'DESC');

        $query = $qb->getQuery();
        $query->execute();
        return $query->getResult();
    }

    public function getCountByGifState($gifState)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(g.id)')
            ->from('LjdsBundle\Entity\Gif', 'g')
            ->where('g.gifStatus = ' . $gifState);
        $query = $qb->getQuery();

        return intval($query->getSingleScalarResult());
    }

    public function getRandomGif()
    {
        $count = $this->createQueryBuilder('g')
            ->select('COUNT(g)')
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->createQueryBuilder('g')
            ->setFirstResult(rand(0, $count - 1))
            ->setMaxResults(1)
            ->where('g.gifStatus = ' . GifState::PUBLISHED)
            ->getQuery()
            ->getSingleResult();
    }
}
