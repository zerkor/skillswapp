<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\MatchingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/matching', name: 'api_matching_')]
class MatchingApiController extends AbstractController
{
    public function __construct(private readonly MatchingService $matchingService) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $skill = (string) $request->query->get('skill', '');
        $level = (int) $request->query->get('level', 0);

        if ($skill === '') {
            return new JsonResponse(['success' => false, 'error' => 'Le paramètre skill est requis.', 'code' => 400], 400);
        }

        $matches = $this->matchingService->findMatches($user, $skill, $level);

        $data = array_map(function (array $match) {
            return [
                'user'         => $match['user']->toArray(),
                'score'        => round($match['score'] * 100),
                'matchedSkill' => $match['matchedSkill']->toArray(),
                'commonSlots'  => $match['commonSlots'],
            ];
        }, $matches);

        return new JsonResponse(['success' => true, 'data' => $data, 'message' => '']);
    }
}
