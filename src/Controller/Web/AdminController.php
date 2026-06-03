<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Repository\PostRepository;
use App\Repository\ReviewRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableau de bord admin — 3 KPIs d'évaluation formateur :
 *   1. Inscriptions (comptes créés + vérifiés)
 *   2. Niveau d'utilisation (sessions actives, connexions 30j)
 *   3. Mises en relation réalisées via le matching
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly UserRepository    $userRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly PostRepository    $postRepository,
        private readonly ReviewRepository  $reviewRepository,
    ) {}

    #[Route('', name: 'dashboard')]
    public function dashboard(): Response
    {
        $kpis = [
            // KPI 1 — Inscriptions
            'inscriptions' => [
                'total'        => $this->userRepository->countTotal(),
                'verified'     => $this->userRepository->countVerified(),
                'last30days'   => $this->userRepository->countLastDays(30),
                'last7days'    => $this->userRepository->countLastDays(7),
            ],

            // KPI 2 — Niveau d'utilisation
            'utilisation' => [
                'sessionsActives'  => $this->sessionRepository->countActive(),
                'sessionsLast30j'  => $this->sessionRepository->countLastDays(30),
                'postsTotal'       => $this->postRepository->countTotal(),
                'reviewsTotal'     => count($this->reviewRepository->findAll()),
            ],

            // KPI 3 — Mises en relation réalisées (priorité absolue formateur)
            'mises_en_relation' => [
                'total'      => $this->sessionRepository->countMatchingMadeConnections(),
                'completees' => $this->sessionRepository->countCompleted(),
                'proposees'  => $this->sessionRepository->countTotal(),
                'taux'       => $this->computeTauxConversion(),
            ],
        ];

        $topUsers = $this->userRepository->findLeaderboard(5);

        return $this->render('admin/dashboard.html.twig', [
            'kpis'     => $kpis,
            'topUsers' => $topUsers,
        ]);
    }

    private function computeTauxConversion(): float
    {
        $total    = $this->sessionRepository->countTotal();
        $complete = $this->sessionRepository->countCompleted();

        if ($total === 0) return 0.0;

        return round(($complete / $total) * 100, 1);
    }
}
