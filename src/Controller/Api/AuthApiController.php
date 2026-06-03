<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthApiController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $userRepository,
        private readonly MailService $mailService,
        private readonly RateLimiterFactory $loginLimiterFactory,
        private readonly string $allowedEmailDomain = '',
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->error('Données JSON invalides.', 400);
        }

        $email    = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $nom      = trim((string) ($data['nom'] ?? ''));
        $prenom   = trim((string) ($data['prenom'] ?? ''));
        $pseudo   = trim((string) ($data['pseudo'] ?? ''));

        if ($this->allowedEmailDomain !== '' && !str_ends_with($email, '@' . $this->allowedEmailDomain)) {
            return $this->error(
                sprintf('Seules les adresses @%s sont autorisées.', $this->allowedEmailDomain),
                400
            );
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            return $this->error('Cet email est déjà utilisé.', 409);
        }

        // Vérifier unicité du pseudo
        if ($pseudo !== '' && $this->userRepository->findOneBy(['pseudo' => $pseudo])) {
            return $this->error('Ce pseudo est déjà pris.', 409);
        }

        $user = new User();
        $user->setEmail($email)
             ->setNom($nom)
             ->setPrenom($prenom)
             ->setPseudo($pseudo !== '' ? $pseudo : null)
             ->setPassword($this->passwordHasher->hashPassword($user, $password))
             ->setVerificationToken(bin2hex(random_bytes(32)));

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->error((string) $errors->get(0)->getMessage(), 400);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->success(['user' => $user->toArray()], 'Compte créé. Vérifiez votre email.', 201);
    }

    #[Route('/verify/{token}', name: 'verify', methods: ['GET'])]
    public function verify(string $token): JsonResponse
    {
        $user = $this->userRepository->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            return $this->error('Token invalide ou expiré.', 404);
        }

        $user->setIsVerified(true)->setVerificationToken(null);
        $this->em->flush();

        return $this->success([], 'Email vérifié avec succès.');
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
