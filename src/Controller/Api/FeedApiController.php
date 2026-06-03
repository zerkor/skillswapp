<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_feed_')]
class FeedApiController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface $validator,
    ) {}

    #[Route('/feed', name: 'feed', methods: ['GET'])]
    public function feed(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $page    = max(1, (int) $request->query->get('page', 1));
        $perPage = 10;
        $posts   = $this->postRepository->findFeed($page, $perPage);
        $total   = $this->postRepository->countTotal();

        return new JsonResponse([
            'success' => true,
            'data'    => [
                'posts'   => array_map(fn($p) => $p->toArray($user), $posts),
                'total'   => $total,
                'page'    => $page,
                'pages'   => (int) ceil($total / $perPage),
            ],
            'message' => '',
        ]);
    }

    #[Route('/posts', name: 'post_create', methods: ['POST'])]
    public function createPost(Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->error('Données invalides.', 400);
        }

        $post = new Post();
        $post->setUser($user)
             ->setContenu((string) ($data['contenu'] ?? ''))
             ->setCompetenceTag($data['competence_tag'] ? (string) $data['competence_tag'] : null);

        $errors = $this->validator->validate($post);
        if (count($errors) > 0) {
            return $this->error((string) $errors->get(0)->getMessage(), 400);
        }

        $this->em->persist($post);
        $this->em->flush();

        return $this->success($post->toArray($user), 'Post publié.', 201);
    }

    #[Route('/posts/{id}/like', name: 'post_like', methods: ['POST'])]
    public function likePost(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $post = $this->postRepository->find($id);
        if (!$post) {
            return $this->error('Post introuvable.', 404);
        }

        if ($post->isLikedBy($user)) {
            $post->removeLike($user);
            $liked = false;
        } else {
            $post->addLike($user);
            $liked = true;
        }

        $this->em->flush();

        return $this->success(['liked' => $liked, 'likesCount' => $post->getLikes()->count()]);
    }

    /**
     * Réaction multi-types — v1.1 (like, heart, idea, celebrate)
     */
    #[Route('/posts/{id}/react', name: 'post_react', methods: ['POST'])]
    public function reactPost(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $post = $this->postRepository->find($id);
        if (!$post) {
            return $this->error('Post introuvable.', 404);
        }

        $data = json_decode($request->getContent(), true);
        $type = (string) ($data['type'] ?? 'like');

        $allowed = ['like', 'heart', 'idea', 'celebrate'];
        if (!in_array($type, $allowed, true)) {
            return $this->error('Type de réaction invalide.', 400);
        }

        $post->addReaction($type);
        $this->em->flush();

        return $this->success([
            'reactionCounts' => $post->getReactionCounts(),
        ]);
    }

    #[Route('/posts/{id}/comments', name: 'post_comment', methods: ['POST'])]
    public function addComment(int $id, Request $request, #[CurrentUser] User $user): JsonResponse
    {
        $post = $this->postRepository->find($id);
        if (!$post) {
            return $this->error('Post introuvable.', 404);
        }

        $data    = json_decode($request->getContent(), true);
        $comment = new Comment();
        $comment->setPost($post)
                ->setUser($user)
                ->setContenu((string) ($data['contenu'] ?? ''));

        $errors = $this->validator->validate($comment);
        if (count($errors) > 0) {
            return $this->error((string) $errors->get(0)->getMessage(), 400);
        }

        $this->em->persist($comment);
        $this->em->flush();

        return $this->success($comment->toArray(), 'Commentaire ajouté.', 201);
    }

    #[Route('/posts/{id}', name: 'post_delete', methods: ['DELETE'])]
    public function deletePost(int $id, #[CurrentUser] User $user): JsonResponse
    {
        $post = $this->postRepository->find($id);
        if (!$post) {
            return $this->error('Post introuvable.', 404);
        }

        if ($post->getUser()?->getId() !== $user->getId()) {
            return $this->error('Accès refusé.', 403);
        }

        $this->em->remove($post);
        $this->em->flush();

        return $this->success([], 'Post supprimé.');
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
