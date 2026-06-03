<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Availability;
use App\Entity\Skill;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;

/**
 * Calcule les scores de compatibilité entre utilisateurs pour le matching.
 *
 * Algorithme pondéré (priorité absolue MVP) :
 *   - Correspondance compétence enseignée ↔ recherchée : 50%
 *   - Compatibilité créneaux de disponibilité          : 30%
 *   - Réputation tuteur (moyenne des avis reçus)       : 20%
 */
class MatchingService
{
    public function __construct(
        private readonly SkillRepository  $skillRepository,
        private readonly UserRepository   $userRepository,
        private readonly ReviewRepository $reviewRepository,
    ) {}

    /**
     * Trouve les meilleurs matchs pour une compétence donnée.
     *
     * @param User   $seeker  L'étudiant qui cherche à apprendre
     * @param string $skill   Nom de la compétence recherchée
     * @param int    $level   Niveau minimum souhaité (0 = tous)
     *
     * @return array<array{user: User, score: float, scoreBreakdown: array, matchedSkill: Skill, commonSlots: int, avgRating: float}>
     */
    public function findMatches(User $seeker, string $skill, int $level = 0): array
    {
        $teachSkills = $this->skillRepository->findTeachSkillsMatchingQuery($skill);
        $results     = [];

        foreach ($teachSkills as $teachSkill) {
            $tutor = $teachSkill->getUser();

            if ($tutor === null || $tutor->getId() === $seeker->getId()) {
                continue;
            }

            [$score, $breakdown] = $this->computeScore($seeker, $tutor, $teachSkill, $level);
            $commonSlots = $this->countCommonSlots($seeker, $tutor);
            $avgRating   = $this->getAverageRating($tutor);

            $results[] = [
                'user'           => $tutor,
                'score'          => $score,
                'scoreBreakdown' => $breakdown,
                'matchedSkill'   => $teachSkill,
                'commonSlots'    => $commonSlots,
                'avgRating'      => $avgRating,
            ];
        }

        // Tri décroissant par score — le meilleur match en premier
        usort($results, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    /**
     * Calcule le score global et son détail pour une paire seeker ↔ tutor.
     *
     * @return array{float, array{skill: float, slots: float, reputation: float}}
     */
    private function computeScore(User $seeker, User $tutor, Skill $skill, int $requestedLevel): array
    {
        $skillScore = $this->computeSkillScore($skill, $requestedLevel);   // 0.0 – 1.0
        $slotScore  = $this->computeSlotScore($seeker, $tutor);            // 0.0 – 1.0
        $reputScore = $this->computeReputationScore($tutor);               // 0.0 – 1.0

        $total = round(
            ($skillScore * 0.50)
            + ($slotScore  * 0.30)
            + ($reputScore * 0.20),
            4
        );

        return [
            $total,
            [
                'skill'      => round($skillScore  * 100),
                'slots'      => round($slotScore   * 100),
                'reputation' => round($reputScore  * 100),
            ],
        ];
    }

    /**
     * Score de compétence (50%) :
     *   - Match exact de nom → base 1.0
     *   - Niveau tuteur ≥ niveau demandé → bonus proportionnel
     *   - Niveau tuteur < niveau demandé → pénalité
     */
    private function computeSkillScore(Skill $skill, int $requestedLevel): float
    {
        $base = 1.0;

        if ($requestedLevel > 0) {
            if ($skill->getNiveau() >= $requestedLevel) {
                // Bonus pour chaque niveau au-dessus du minimum
                $base = min(1.0, 1.0 + (($skill->getNiveau() - $requestedLevel) * 0.05));
            } else {
                // Pénalité si le tuteur est en dessous du niveau requis
                $base = max(0.2, 1.0 - (($requestedLevel - $skill->getNiveau()) * 0.25));
            }
        }

        return $base;
    }

    /**
     * Score de créneaux (30%) : fraction de créneaux communs / créneaux de l'apprenant.
     */
    private function computeSlotScore(User $seeker, User $tutor): float
    {
        $common = $this->countCommonSlots($seeker, $tutor);
        $total  = max(1, $seeker->getAvailabilities()->count());

        return min(1.0, $common / $total);
    }

    /**
     * Score de réputation (20%) : basé sur la moyenne réelle des avis (1-5).
     * Normalise la note sur 5 → 0.0–1.0.
     * Si aucun avis → 0.5 (score neutre, pour ne pas pénaliser les nouveaux tuteurs).
     */
    private function computeReputationScore(User $tutor): float
    {
        $avgRating = $this->getAverageRating($tutor);

        if ($avgRating === null) {
            return 0.5; // Neutre pour les nouveaux tuteurs sans avis
        }

        return min(1.0, $avgRating / 5.0);
    }

    /**
     * Retourne la note moyenne (1-5) du tuteur, ou null si aucun avis.
     */
    public function getAverageRating(User $tutor): ?float
    {
        $avg = $this->reviewRepository->getAverageRatingForUser($tutor);

        return $avg !== null ? round($avg, 2) : null;
    }

    /**
     * Compte les créneaux de disponibilité en commun (même jour + plage horaire se chevauchant).
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
                if (
                    $slotA->getJourSemaine() === $slotB->getJourSemaine()
                    && $this->timesOverlap($slotA, $slotB)
                ) {
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
        $aEnd   = $a->getHeureFin()?->getTimestamp()   ?? 0;
        $bStart = $b->getHeureDebut()?->getTimestamp() ?? 0;
        $bEnd   = $b->getHeureFin()?->getTimestamp()   ?? 0;

        return $aStart < $bEnd && $aEnd > $bStart;
    }
}
