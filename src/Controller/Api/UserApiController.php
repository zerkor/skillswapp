<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\GamificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/users', name: 'api_users_')]
class UserApiController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly GamificationService $gamificationService,
    ) {}

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->error('Utilisateur introuvable.', 404);
        }

        $data             = $user->toArray();
        $data['skills']   = array_map(fn($s) => $s->toArray(), $user->getSkills()->toArray());
        $data['badges']   = array_map(fn($ub) => $ub->toArray(), $user->getUserBadges()->toArray());
        $data['progress'] = $this->gamificationService->getLevelProgress($user);
        $data['nextLvl']  = $this->gamificationService->getNextLevelThreshold($user);

        return $this->success($data);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, #[CurrentUser] User $currentUser): JsonResponse
    {
        if ($currentUser->getId() !== $id) {
            return $this->error('Accès refusé.', 403);
        }

        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->error('Utilisateur introuvable.', 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->error('Données invalides.', 400);
        }

        if (isset($data['nom']))       $user->setNom((string) $data['nom']);
        if (isset($data['prenom']))    $user->setPrenom((string) $data['prenom']);
        if (isset($data['bio']))       $user->setBio((string) $data['bio']);
        if (isset($data['formation'])) $user->setFormation((string) $data['formation']);
        if (isset($data['promotion'])) $user->setPromotion((string) $data['promotion']);

        $this->em->flush();

        return $this->success($user->toArray(), 'Profil mis à jour.');
    }

    #[Route('/{id}/avatar', name: 'avatar', methods: ['POST'])]
    public function uploadAvatar(int $id, Request $request, #[CurrentUser] User $currentUser): JsonResponse
    {
        if ($currentUser->getId() !== $id) {
            return $this->error('Accès refusé.', 403);
        }

        $file = $request->files->get('avatar');
        if (!$file) {
            return $this->error('Aucun fichier fourni.', 400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes, true)) {
            return $this->error('Format non supporté. Utilisez JPEG, PNG ou WebP.', 400);
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return $this->error('Fichier trop volumineux (max 2 Mo).', 400);
        }

        $filename = sprintf('%d_%s.%s', $id, uniqid(), $file->guessExtension());
        $uploadDir = __DIR__ . '/../../../public/assets/images/avatars';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file->move($uploadDir, $filename);

        $user = $this->userRepository->find($id);
        $user?->setPhoto('/assets/images/avatars/' . $filename);
        $this->em->flush();

        return $this->success(['photo' => $user?->getPhoto()], 'Avatar mis à jour.');
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
