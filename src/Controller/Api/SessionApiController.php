<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\GamificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/sessions', name: 'api_sessions_')]
class SessionApiController extends AbstractController
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly GamificationService $gamificationService,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        $sessions = $this->sessionRepository->findByUser($user);

        return $this->success(array_map(fn($s) => $s->toArray(), $sessions));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $currentUser): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->error('Données invalides.', 400);
        }

        $tuteurId = (int) ($data['tuteur_id'] ?? 0);
        $tuteur   = $this->userRepository->find($tuteurId);

        if (!$tuteur) {
            return $this->error('Tuteur introuvable.', 404);
        }

        $session = new Session();
        $session->setTuteur($tuteur)
                ->setApprenant($currentUser)
                ->setCompetence((string) ($data['competence'] ?? ''))
                ->setType((string) ($data['type'] ?? Session::TYPE_COURS_RAPIDE))
                ->setDate(new \DateTime((string) ($data['date'] ?? 'now')))
                ->setDureeMinutes((int) ($data['duree_minutes'] ?? 60))
                ->setLieuOuLien((string) ($data['lieu_ou_lien'] ?? ''));

        $errors = $this->validator->validate($session);
        if (count($errors) > 0) {
            return $this->error((string) $errors->get(0)->getMessage(), 400);
        }

        $this->em->persist($session);
        $this->em->flush();

        return $this->success($session->toArray(), 'Session proposée.', 201);
    }

    #[Route('/{id}/confirm', name: 'confirm', methods: ['PUT'])]
    public function confirm(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $session = $this->sessionRepository->find($id);
        if (!$session) {
            return $this->error('Session introuvable.', 404);
        }

        if ($session->getTuteur()?->getId() !== $user->getId()) {
            return $this->error('Seul le tuteur peut confirmer la session.', 403);
        }

        if ($session->getStatut() !== Session::STATUT_PROPOSEE) {
            return $this->error('La session ne peut pas être confirmée dans son état actuel.', 400);
        }

        $session->setStatut(Session::STATUT_CONFIRMEE);
        $this->em->flush();

        return $this->success($session->toArray(), 'Session confirmée.');
    }

    #[Route('/{id}/decline', name: 'decline', methods: ['PUT'])]
    public function decline(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $session = $this->sessionRepository->find($id);
        if (!$session) {
            return $this->error('Session introuvable.', 404);
        }

        $isTuteur   = $session->getTuteur()?->getId() === $user->getId();
        $isApprenant = $session->getApprenant()?->getId() === $user->getId();

        if (!$isTuteur && !$isApprenant) {
            return $this->error('Accès refusé.', 403);
        }

        $session->setStatut(Session::STATUT_ANNULEE);
        $this->em->flush();

        return $this->success($session->toArray(), 'Session refusée.');
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['PUT'])]
    public function complete(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $session = $this->sessionRepository->find($id);
        if (!$session) {
            return $this->error('Session introuvable.', 404);
        }

        $isTuteur    = $session->getTuteur()?->getId() === $user->getId();
        $isApprenant = $session->getApprenant()?->getId() === $user->getId();

        if (!$isTuteur && !$isApprenant) {
            return $this->error('Accès refusé.', 403);
        }

        if ($isTuteur) {
            $session->setTuteurCompleted(true);
        } else {
            $session->setApprenantCompleted(true);
        }

        if ($session->isTuteurCompleted() && $session->isApprenantCompleted()) {
            $session->setStatut(Session::STATUT_COMPLETEE);

            $tuteur    = $session->getTuteur();
            $apprenant = $session->getApprenant();

            if ($tuteur) {
                $this->gamificationService->addPoints($tuteur, 'session_completed_tutor');
                $this->gamificationService->checkAndAwardBadges($tuteur);
            }
            if ($apprenant) {
                $this->gamificationService->addPoints($apprenant, 'session_completed_learner');
                $this->gamificationService->checkAndAwardBadges($apprenant);
            }
        }

        $this->em->flush();

        return $this->success($session->toArray(), 'Session marquée complète.');
    }

    #[Route('/{id}/cancel', name: 'cancel', methods: ['PUT'])]
    public function cancel(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $session = $this->sessionRepository->find($id);
        if (!$session) {
            return $this->error('Session introuvable.', 404);
        }

        $isTuteur    = $session->getTuteur()?->getId() === $user->getId();
        $isApprenant = $session->getApprenant()?->getId() === $user->getId();

        if (!$isTuteur && !$isApprenant) {
            return $this->error('Accès refusé.', 403);
        }

        if (in_array($session->getStatut(), [Session::STATUT_COMPLETEE, Session::STATUT_ANNULEE], true)) {
            return $this->error('Impossible d\'annuler cette session.', 400);
        }

        $session->setStatut(Session::STATUT_ANNULEE);
        $this->em->flush();

        return $this->success($session->toArray(), 'Session annulée.');
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
