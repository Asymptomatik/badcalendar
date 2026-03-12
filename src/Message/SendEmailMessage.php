<?php

namespace App\Message;

/**
 * Message asynchrone pour l'envoi d'emails.
 */
final class SendEmailMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $htmlContent,
        public readonly string $from = 'noreply@boisguibad.fr',
    ) {}
}
