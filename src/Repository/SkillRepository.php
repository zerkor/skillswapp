<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Skill>
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    /**
     * Recherche de compétences avec filtres optionnels.
     *
     * @return Skill[]
     */
    public function search(string $query = '', ?int $level = null, ?string $type = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->addSelect('u');

        if ($query !== '') {
            $qb->andWhere('LOWER(s.nom) LIKE :q OR LOWER(s.categorie) LIKE :q')
               ->setParameter('q', '%' . mb_strtolower($query) . '%');
        }

        if ($level !== null) {
            $qb->andWhere('s.niveau = :level')
               ->setParameter('level', $level);
        }

        if ($type !== null) {
            $qb->andWhere('s.type = :type')
               ->setParameter('type', $type);
        }

        return $qb->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les compétences de type teach pour le matching.
     *
     * @return Skill[]
     */
    public function findTeachSkillsMatchingQuery(string $skillName): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->addSelect('u')
            ->where('s.type = :type')
            ->andWhere('LOWER(s.nom) LIKE :q')
            ->setParameter('type', Skill::TYPE_TEACH)
            ->setParameter('q', '%' . mb_strtolower($skillName) . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne toutes les compétences teach (une par user, la première).
     *
     * @return Skill[]
     */
    public function findAllTeachSkills(): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->addSelect('u')
            ->where('s.type = :type')
            ->setParameter('type', Skill::TYPE_TEACH)
            ->orderBy('u.score', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
