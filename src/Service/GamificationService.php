<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Badge;
use App\Entity\User;
use App\Entity\UserBadge;
use App\Repository\BadgeRepository;
use App\Repository\SessionRepository;
use App\Repository\UserBadgeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Gère les points, niveaux et badges de la gamification.
 */
class GamificationService
{
    private const POINTS = [
        'session_completed_tutor'   => 50,
        'session_completed_learner' => 30,
        'review_left'               => 10,
        'profile_completed'         => 20,
    ];

    private const LEVEL_THRESHOLDS = [
        User::NIVEAU_NOVICE    => 0,
        User::NIVEAU_APPRENTI  => 101,
        User::NIVEAU_MENTOR    => 301,
        User::NIVEAU_EXPERT    => 701,
        User::NIVEAU_LEGENDE   => 1500,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly BadgeRepository $badgeRepository,
        private readonly UserBadgeRepository $userBadgeRepository,
        private readonly SessionRepository $sessionRepository,
    ) {}

    /**
     * Ajoute des points à un utilisateur pour une action donnée.
     */
    public function addPoints(User $user, string $action): void
    {
        $points = self::POINTS[$action] ?? 0;
        if ($points === 0) {
            return;
        }
        $user->addScore($points);
        $this->updateLevel($user);
        $this->em->persist($user);
    }

    /**
     * Met à jour le niveau de l'utilisateur selon son score.
     */
    public function updateLevel(User $user): void
    {
        $score  = $user->getScore();
        $niveau = User::NIVEAU_NOVICE;

        foreach (self::LEVEL_THRESHOLDS as $lvl => $threshold) {
            if ($score >= $threshold) {
                $niveau = $lvl;
            }
        }

        $user->setNiveau($niveau);
    }

    /**
     * Vérifie et attribue les badges mérités.
     *
     * @return Badge[] les nouveaux badges obtenus
     */
    public function checkAndAwardBadges(User $user): array
    {
        $awarded       = [];
        $existingBadges = $this->userBadgeRepository->findBy(['user' => $user]);
        $existingIds   = array_map(
            fn(UserBadge $ub) => $ub->getBadge()?->getId(),
            $existingBadges
        );

        $allBadges = $this->badgeRepository->findAll();

        foreach ($allBadges as $badge) {
            if (in_array($badge->getId(), $existingIds, true)) {
                continue;
            }

            if ($this->userMeetsBadgeCondition($user, $badge)) {
                $userBadge = new UserBadge();
                $userBadge->setUser($user);
                $userBadge->setBadge($badge);
                $this->em->persist($userBadge);
                $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    /**
     * Vérifie si un utilisateur remplit la condition d'un badge.
     */
    private function userMeetsBadgeCondition(User $user, Badge $badge): bool
    {
        return match ($badge->getConditionType()) {
            'sessions_completed_total'  => $this->countCompletedSessions($user) >= $badge->getConditionValue(),
            'sessions_completed_tutor'  => $this->sessionRepository->countCompletedAsTutor($user) >= $badge->getConditionValue(),
            'first_session'             => $this->countCompletedSessions($user) >= 1,
            'registered_early'          => $user->getId() !== null && $user->getId() <= 50,
            default => false,
        };
    }

    /** Compte le total de sessions complétées (tuteur + apprenant). */
    private function countCompletedSessions(User $user): int
    {
        return $this->sessionRepository->countCompletedAsTutor($user);
    }

    /**
     * Retourne la progression vers le prochain niveau (0.0 à 1.0).
     */
    public function getLevelProgress(User $user): float
    {
        $score    = $user->getScore();
        $current  = self::LEVEL_THRESHOLDS[$user->getNiveau()] ?? 0;
        $niveaux  = array_keys(self::LEVEL_THRESHOLDS);
        $idx      = array_search($user->getNiveau(), $niveaux, true);
        $nextIdx  = ($idx !== false && isset($niveaux[$idx + 1])) ? $idx + 1 : null;

        if ($nextIdx === null) {
            return 1.0;
        }

        $next  = self::LEVEL_THRESHOLDS[$niveaux[$nextIdx]];
        $range = $next - $current;

        if ($range <= 0) {
            return 1.0;
        }

        return min(1.0, ($score - $current) / $range);
    }

    /**
     * Retourne le score du prochain niveau.
     */
    public function getNextLevelThreshold(User $user): ?int
    {
        $niveaux = array_keys(self::LEVEL_THRESHOLDS);
        $idx     = array_search($user->getNiveau(), $niveaux, true);
        $nextIdx = ($idx !== false && isset($niveaux[$idx + 1])) ? $idx + 1 : null;

        if ($nextIdx === null) {
            return null;
        }

        return self::LEVEL_THRESHOLDS[$niveaux[$nextIdx]];
    }
}
