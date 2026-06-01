<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Review;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Service\GamificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/reviews', name: 'api_reviews_')]
class ReviewApiController extends AbstractController
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly EntityManagerInterface $em,
        private readonly GamificationService $gamificationService,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->error('Données invalides.', 400);
        }

        $sessionId = (int) ($data['session_id'] ?? 0);
        $session   = $this->sessionRepository->find($sessionId);

        if (!$session) {
            return $this->error('Session introuvable.', 404);
        }

        $isTuteur    = $session->getTuteur()?->getId() === $user->getId();
        $isApprenant = $session->getApprenant()?->getId() === $user->getId();

        if (!$isTuteur && !$isApprenant) {
            return $this->error('Vous ne participez pas à cette session.', 403);
        }

        $review = new Review();
        $review->setSession($session)
               ->setAuteur($user)
               ->setNote((int) ($data['note'] ?? 5))
               ->setCommentaire((string) ($data['commentaire'] ?? ''));

        $errors = $this->validator->validate($review);
        if (count($errors) > 0) {
            return $this->error((string) $errors->get(0)->getMessage(), 400);
        }

        $this->em->persist($review);
        $this->gamificationService->addPoints($user, 'review_left');
        $this->em->flush();

        return $this->success($review->toArray(), 'Avis publié.', 201);
    }

    private function success(array $data, string $message = '', int $status = 200): JsonResponse
    {
        return new JsonResponse(['success' => true, 'data' => $data, 'message' => $message], $status);
    }

    private function error(string $message, int $code = 400): JsonResponse
    {
        return new JsonResponse(['success' => false, 'error' => $message, 'code' => $code], $code);
    }
}
