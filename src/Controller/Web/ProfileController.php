<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\GamificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly GamificationService $gamificationService,
    ) {}

    #[Route('/profile/{id}', name: 'app_profile_show', requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        return $this->render('profile/show.html.twig', [
            'profile'   => $user,
            'isSelf'    => $currentUser->getId() === $user->getId(),
            'progress'  => $this->gamificationService->getLevelProgress($user),
            'nextLevel' => $this->gamificationService->getNextLevelThreshold($user),
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
        ]);
    }
}
