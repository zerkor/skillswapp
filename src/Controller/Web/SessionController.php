<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Entity\User;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class SessionController extends AbstractController
{
    public function __construct(private readonly SessionRepository $sessionRepository) {}

    #[Route('/sessions', name: 'app_sessions')]
    public function index(): Response
    {
        /** @var User $user */
        $user     = $this->getUser();
        $sessions = $this->sessionRepository->findByUser($user);

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    #[Route('/sessions/new', name: 'app_sessions_new')]
    public function new(): Response
    {
        return $this->render('session/new.html.twig');
    }
}
