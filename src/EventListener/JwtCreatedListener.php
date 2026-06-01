<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

/**
 * Enrichit le payload JWT avec les données utilisateur.
 */
class JwtCreatedListener
{
    public function onJwtCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $payload             = $event->getData();
        $payload['id']       = $user->getId();
        $payload['nom']      = $user->getNom();
        $payload['prenom']   = $user->getPrenom();
        $payload['photo']    = $user->getPhoto();
        $payload['niveau']   = $user->getNiveau();
        $payload['score']    = $user->getScore();

        $event->setData($payload);
    }
}
