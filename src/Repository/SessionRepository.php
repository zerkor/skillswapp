<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Session;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Session> */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * Toutes les sessions d'un utilisateur (tuteur ou apprenant).
     *
     * @return Session[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.tuteur', 't')
            ->leftJoin('s.apprenant', 'a')
            ->addSelect('t', 'a')
            ->where('s.tuteur = :user OR s.apprenant = :user')
            ->setParameter('user', $user)
            ->orderBy('s.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** Nombre de sessions complétées au total. */
    public function countCompleted(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.statut = :statut')
            ->setParameter('statut', Session::STATUT_COMPLETEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Sessions complétées en tant que tuteur pour un utilisateur. */
    public function countCompletedAsTutor(User $user): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.tuteur = :user')
            ->andWhere('s.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', Session::STATUT_COMPLETEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Nombre total de sessions créées (proposées + au-delà). */
    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * KPI Admin — Nombre de mises en relation réalisées via le matching.
     * = Sessions créées avec un statut autre qu'annulées.
     */
    public function countMatchingMadeConnections(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.statut != :annulee')
            ->setParameter('annulee', Session::STATUT_ANNULEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sessions actives (confirmées ou en cours) — indicateur d'utilisation.
     */
    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.statut IN (:statuts)')
            ->setParameter('statuts', [Session::STATUT_CONFIRMEE, Session::STATUT_EN_COURS])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sessions créées ces 30 derniers jours.
     */
    public function countLastDays(int $days = 30): int
    {
        $since = new \DateTimeImmutable("-{$days} days");

        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.createdAt >= :since')
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
