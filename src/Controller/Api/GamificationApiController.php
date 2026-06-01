<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\BadgeRepository;
use App\Repository\UserBadgeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_gamification_')]
class GamificationApiController extends AbstractController
{
    public function __construct(
        private readonly BadgeRepository $badgeRepository,
        private readonly UserBadgeRepository $userBadgeRepository,
        private readonly UserRepository $userRepository,
    ) {}

    #[Route('/badges', name: 'badges_list', methods: ['GET'])]
    public function listBadges(): JsonResponse
    {
        $badges = $this->badgeRepository->findAll();

        return new JsonResponse([
            'success' => true,
            'data'    => array_map(fn($b) => $b->toArray(), $badges),
            'message' => '',
        ]);
    }

    #[Route('/users/{id}/badges', name: 'user_badges', methods: ['GET'])]
    public function userBadges(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable.', 'code' => 404], 404);
        }

        $userBadges = $this->userBadgeRepository->findBy(['user' => $user]);

        return new JsonResponse([
            'success' => true,
            'data'    => array_map(fn($ub) => $ub->toArray(), $userBadges),
            'message' => '',
        ]);
    }

    #[Route('/leaderboard', name: 'leaderboard', methods: ['GET'])]
    public function leaderboard(Request $request): JsonResponse
    {
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));
        $users = $this->userRepository->findLeaderboard($limit);

        $data = array_map(function (object $user, int $rank) {
            $arr           = $user->toArray();
            $arr['rank']   = $rank + 1;
            $arr['badges'] = array_map(fn($ub) => $ub->toArray(), $user->getUserBadges()->toArray());
            return $arr;
        }, $users, array_keys($users));

        return new JsonResponse(['success' => true, 'data' => $data, 'message' => '']);
    }
}
