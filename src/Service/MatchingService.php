<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Availability;
use App\Entity\Skill;
use App\Entity\User;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;

/**
 * Calcule les scores de compatibilité entre utilisateurs pour le matching.
 *
 * Algorithme : compétence (50%) + créneaux communs (30%) + réputation normalisée (20%)
 */
class MatchingService
{
    public function __construct(
        private readonly SkillRepository $skillRepository,
        private readonly UserRepository $userRepository,
    ) {}

    /**
     * Trouve les meilleurs matchs pour une compétence donnée.
     *
     * @return array<array{user: User, score: float, matchedSkill: Skill, commonSlots: int}>
     */
    public function findMatches(User $seeker, string $skill, int $level = 0): array
    {
        $teachSkills = $this->skillRepository->findTeachSkillsMatchingQuery($skill);
        $maxScore    = $this->getMaxScore();
        $results     = [];

        foreach ($teachSkills as $teachSkill) {
            $tutor = $teachSkill->getUser();

            if ($tutor === null || $tutor->getId() === $seeker->getId()) {
                continue;
            }

            $score = $this->computeScore($seeker, $tutor, $teachSkill, $level, $maxScore);

            $results[] = [
                'user'         => $tutor,
                'score'        => $score,
                'matchedSkill' => $teachSkill,
                'commonSlots'  => $this->countCommonSlots($seeker, $tutor),
            ];
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    /**
     * Retourne tous les tuteurs triés par score de réputation (page par défaut).
     *
     * @return array<array{user: User, score: float, matchedSkill: Skill, commonSlots: int}>
     */
    public function findAllTutors(User $seeker): array
    {
        $teachSkills = $this->skillRepository->findAllTeachSkills();
        $maxScore    = $this->getMaxScore();
        $seen        = [];
        $results     = [];

        foreach ($teachSkills as $teachSkill) {
            $tutor = $teachSkill->getUser();
            if ($tutor === null || $tutor->getId() === $seeker->getId()) {
                continue;
            }
            if (isset($seen[$tutor->getId()])) {
                continue;
            }
            $seen[$tutor->getId()] = true;

            $reputScore = $this->computeReputationScore($tutor, $maxScore);
            $slotScore  = $this->computeSlotScore($seeker, $tutor);

            $results[] = [
                'user'         => $tutor,
                'score'        => round(($reputScore * 0.60) + ($slotScore * 0.40), 2),
                'matchedSkill' => $teachSkill,
                'commonSlots'  => $this->countCommonSlots($seeker, $tutor),
            ];
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    /**
     * Calcule le score de compatibilité entre deux utilisateurs.
     */
    private function computeScore(User $seeker, User $tutor, Skill $skill, int $requestedLevel, float $maxScore): float
    {
        $skillScore = $this->computeSkillScore($skill, $requestedLevel);
        $slotScore  = $this->computeSlotScore($seeker, $tutor);
        $reputScore = $this->computeReputationScore($tutor, $maxScore);

        return round(($skillScore * 0.50) + ($slotScore * 0.30) + ($reputScore * 0.20), 2);
    }

    /**
     * Score de compétence : correspondance nom + niveau.
     */
    private function computeSkillScore(Skill $skill, int $requestedLevel): float
    {
        $base = 1.0;

        if ($requestedLevel > 0 && $skill->getNiveau() >= $requestedLevel) {
            $base = min(1.0, $base + (($skill->getNiveau() - $requestedLevel) * 0.1));
        }

        return $base;
    }

    /**
     * Score de créneaux : fraction de créneaux communs.
     */
    private function computeSlotScore(User $seeker, User $tutor): float
    {
        $common = $this->countCommonSlots($seeker, $tutor);
        $total  = max(1, $seeker->getAvailabilities()->count());

        return min(1.0, $common / $total);
    }

    /**
     * Score de réputation : score normalisé sur max connu.
     */
    private function computeReputationScore(User $tutor, float $maxScore): float
    {
        if ($maxScore <= 0) {
            return 0.0;
        }

        return min(1.0, $tutor->getScore() / $maxScore);
    }

    /**
     * Compte les créneaux de disponibilité en commun (même jour, plage horaire se chevauchant).
     */
    private function countCommonSlots(User $a, User $b): int
    {
        $slotsA = $a->getAvailabilities()->toArray();
        $slotsB = $b->getAvailabilities()->toArray();
        $common = 0;

        foreach ($slotsA as $slotA) {
            /** @var Availability $slotA */
            foreach ($slotsB as $slotB) {
                /** @var Availability $slotB */
                if ($slotA->getJourSemaine() === $slotB->getJourSemaine()
                    && $this->timesOverlap($slotA, $slotB)) {
                    $common++;
                    break;
                }
            }
        }

        return $common;
    }

    /**
     * Détermine si deux créneaux temporels se chevauchent.
     */
    private function timesOverlap(Availability $a, Availability $b): bool
    {
        $aStart = $a->getHeureDebut()?->getTimestamp() ?? 0;
        $aEnd   = $a->getHeureFin()?->getTimestamp() ?? 0;
        $bStart = $b->getHeureDebut()?->getTimestamp() ?? 0;
        $bEnd   = $b->getHeureFin()?->getTimestamp() ?? 0;

        return $aStart < $bEnd && $aEnd > $bStart;
    }

    /**
     * Récupère le score maximum parmi tous les utilisateurs pour la normalisation.
     */
    private function getMaxScore(): float
    {
        $top = $this->userRepository->findLeaderboard(1);
        return !empty($top) ? (float) $top[0]->getScore() : 1.0;
    }
}
