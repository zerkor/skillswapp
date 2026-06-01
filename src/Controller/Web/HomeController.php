<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Repository\SessionRepository;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SessionRepository $sessionRepository,
        private readonly SkillRepository $skillRepository,
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'stats' => [
                'users'    => $this->userRepository->countTotal(),
                'sessions' => $this->sessionRepository->countCompleted(),
                'skills'   => count($this->skillRepository->findAll()),
            ],
        ]);
    }
}
