<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Service d'envoi d'emails transactionnels.
 */
class MailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $appName = 'SkillSwap',
    ) {}

    /**
     * Envoie l'email de vérification du compte.
     */
    public function sendVerificationEmail(User $user, string $verificationUrl): void
    {
        $email = (new Email())
            ->from('no-reply@skillswap.fr')
            ->to((string) $user->getEmail())
            ->subject('[SkillSwap] Vérifiez votre adresse email')
            ->html($this->buildVerificationHtml($user, $verificationUrl))
            ->text($this->buildVerificationText($user, $verificationUrl));

        $this->mailer->send($email);
    }

    private function buildVerificationHtml(User $user, string $url): string
    {
        $name = htmlspecialchars($user->getFullName(), ENT_QUOTES, 'UTF-8');
        $url  = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
          <h2 style="color: #2E75B6;">Bienvenue sur SkillSwap, {$name} !</h2>
          <p>Pour activer votre compte, cliquez sur le lien ci-dessous :</p>
          <a href="{$url}" style="display:inline-block;background:#2E75B6;color:white;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:600;">
            Vérifier mon email
          </a>
          <p style="color:#64748B;font-size:14px;">Ce lien est valable 24 heures.</p>
        </div>
        HTML;
    }

    private function buildVerificationText(User $user, string $url): string
    {
        return sprintf(
            "Bienvenue sur SkillSwap, %s !\n\nVérifiez votre email : %s\n\nCe lien est valable 24 heures.",
            $user->getFullName(),
            $url
        );
    }
}
