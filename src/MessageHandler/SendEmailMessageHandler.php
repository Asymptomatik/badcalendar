<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

/**
 * Handler pour l'envoi asynchrone d'emails.
 */
#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
    ) {}

    public function __invoke(SendEmailMessage $message): void
    {
        $email = (new Email())
            ->from($message->from)
            ->to($message->to)
            ->subject($message->subject)
            ->html($message->htmlContent);

        $this->mailer->send($email);
    }
}
