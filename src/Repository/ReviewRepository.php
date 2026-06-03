<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Review> */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Retourne la note moyenne (1-5) d'un tuteur basée sur les avis de ses sessions.
     * Utilisé par l'algorithme de matching (composante réputation 20%).
     *
     * @return float|null null si aucun avis
     */
    public function getAverageRatingForUser(User $user): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.note) as avgNote')
            ->join('r.session', 's')
            ->where('s.tuteur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (float) $result : null;
    }

    /**
     * Compte le nombre total d'avis reçus par un tuteur.
     */
    public function countForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.session', 's')
            ->where('s.tuteur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
