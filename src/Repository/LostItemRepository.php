<?php

namespace App\Repository;

use App\Entity\LostItem;
use App\Enum\LostItemStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LostItem>
 */
class LostItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LostItem::class);
    }

    /** Retourne les objets trouvés non rendus */
    public function findNotReturned(): array
    {
        return $this->createQueryBuilder('li')
            ->andWhere('li.status = :status')
            ->setParameter('status', LostItemStatus::TROUVE)
            ->orderBy('li.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** Retourne les objets trouvés récents */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('li')
            ->orderBy('li.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
