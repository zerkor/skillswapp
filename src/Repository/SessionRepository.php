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

    /**
     * Passe les sessions confirmées dont la date est passée en "en_cours".
     * Appelé au chargement de la liste pour garder les statuts à jour.
     */
    public function transitionStatuses(): void
    {
        $now = new \DateTime();

        $this->createQueryBuilder('s')
            ->update()
            ->set('s.statut', ':enCours')
            ->where('s.statut = :confirmee')
            ->andWhere('s.date <= :now')
            ->setParameter('enCours',   Session::STATUT_EN_COURS)
            ->setParameter('confirmee', Session::STATUT_CONFIRMEE)
            ->setParameter('now',       $now)
            ->getQuery()
            ->execute();
    }

    /**
     * Nombre de sessions complétées au total (pour les stats de la home).
     */
    public function countCompleted(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.statut = :statut')
            ->setParameter('statut', Session::STATUT_COMPLETEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sessions complétées en tant que tuteur pour un utilisateur.
     */
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
}
