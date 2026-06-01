<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Availability;
use App\Entity\Skill;
use App\Entity\User;
use App\Repository\AvailabilityRepository;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_skill_')]
class SkillApiController extends AbstractController
{
    public function __construct(
        private readonly SkillRepository $skillRepository,
        private readonly AvailabilityRepository $availabilityRepository,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/skills/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $q     = (string) $request->query->get('q', '');
        $level = $request->query->has('level') ? (int) $request->query->get('level') : null;
        $type  = $request->query->get('type');

        $skills = $this->skillRepository->search($q, $level, $type);

        return $this->success(array_map(fn($s) => $s->toArray(), $skills));
    }

    #[Route('/skills', name: 'create', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->error('Données invalides.', 400);
        }

        $skill = new Skill();
        $skill->setUser($user)
              ->setNom((string) ($data['nom'] ?? ''))
              ->setCategorie((string) ($data['categorie'] ?? ''))
              ->setNiveau((int) ($data['niveau'] ?? 1))
              ->setType((string) ($data['type'] ?? Skill::TYPE_TEACH));

        $errors = $this->validator->validate($skill);
        if (count($errors) > 0) {
            return $this->error((string) $errors->get(0)->getMessage(), 400);
        }

        $this->em->persist($skill);
        $this->em->flush();

        return $this->success($skill->toArray(), 'Compétence ajoutée.', 201);
    }

    #[Route('/skills/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $skill = $this->skillRepository->find($id);

        if (!$skill) {
            return $this->error('Compétence introuvable.', 404);
        }

        if ($skill->getUser()?->getId() !== $user->getId()) {
            return $this->error('Accès refusé.', 403);
        }

        $this->em->remove($skill);
        $this->em->flush();

        return $this->success([], 'Compétence supprimée.');
    }

    #[Route('/users/{id}/availabilities', name: 'availabilities_list', methods: ['GET'])]
    public function listAvailabilities(int $id): JsonResponse
    {
        $avails = $this->availabilityRepository->findBy(['user' => $id]);

        return $this->success(array_map(fn($a) => $a->toArray(), $avails));
    }

    #[Route('/availabilities', name: 'availability_create', methods: ['POST'])]
    public function createAvailability(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->error('Données invalides.', 400);
        }

        $avail = new Availability();
        $avail->setUser($user)
              ->setJourSemaine((string) ($data['jour_semaine'] ?? ''))
              ->setHeureDebut(new \DateTimeImmutable((string) ($data['heure_debut'] ?? '08:00')))
              ->setHeureFin(new \DateTimeImmutable((string) ($data['heure_fin'] ?? '10:00')));

        $errors = $this->validator->validate($avail);
        if (count($errors) > 0) {
            return $this->error((string) $errors->get(0)->getMessage(), 400);
        }

        $this->em->persist($avail);
        $this->em->flush();

        return $this->success($avail->toArray(), 'Disponibilité ajoutée.', 201);
    }

    #[Route('/availabilities/{id}', name: 'availability_delete', methods: ['DELETE'])]
    public function deleteAvailability(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $avail = $this->availabilityRepository->find($id);

        if (!$avail) {
            return $this->error('Disponibilité introuvable.', 404);
        }

        if ($avail->getUser()?->getId() !== $user->getId()) {
            return $this->error('Accès refusé.', 403);
        }

        $this->em->remove($avail);
        $this->em->flush();

        return $this->success([], 'Disponibilité supprimée.');
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
