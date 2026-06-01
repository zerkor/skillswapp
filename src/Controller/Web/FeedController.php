<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FeedController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository) {}

    #[Route('/feed', name: 'app_feed')]
    public function index(): Response
    {
        $topUsers = $this->userRepository->findLeaderboard(5);

        return $this->render('feed/index.html.twig', [
            'topUsers' => $topUsers,
        ]);
    }

    #[Route('/leaderboard', name: 'app_leaderboard')]
    public function leaderboard(): Response
    {
        $users = $this->userRepository->findLeaderboard(50);

        return $this->render('leaderboard/index.html.twig', [
            'users' => $users,
        ]);
    }
}
