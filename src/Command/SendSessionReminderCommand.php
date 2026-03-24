<?php

namespace App\Command;

use App\Repository\SessionRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Commande envoyant les rappels email aux responsables clés 24h avant la séance.
 * Idempotente : peut être appelée plusieurs fois sans duplication.
 */
#[AsCommand(
    name: 'app:send-session-reminder',
    description: 'Envoie les rappels email aux responsables clés 24h avant la séance.',
)]
class SendSessionReminderCommand extends Command
{
    public function __construct(
        private readonly SessionRepository $sessionRepository,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $sessions = $this->sessionRepository->findSessionsForReminder();

        if (empty($sessions)) {
            $io->info('Aucune séance avec responsable clés demain.');
            return Command::SUCCESS;
        }

        $sent = 0;
        foreach ($sessions as $session) {
            $responsable = $session->getResponsableKeys();
            if ($responsable === null || $responsable->getEmail() === null) {
                continue;
            }

            $email = (new Email())
                ->from('noreply@boisguibad.fr')
                ->to($responsable->getEmail())
                ->subject(sprintf(
                    '[BoisguiBad] Rappel : vous êtes responsable des clés demain (%s)',
                    $session->getDate()->format('d/m/Y H:i')
                ))
                ->html(sprintf(
                    '<p>Bonjour %s,</p><p>Rappel : vous êtes responsable des clés pour la séance <strong>%s du %s à %s</strong>.</p><p>Merci de vous assurer d\'être disponible pour ouvrir et fermer le gymnase.</p><p>L\'équipe BoisguiBad</p>',
                    htmlspecialchars($responsable->getFirstName(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    htmlspecialchars($session->getType()->label(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                    $session->getDate()->format('d/m/Y'),
                    $session->getDate()->format('H:i'),
                ));

            $this->mailer->send($email);
            $sent++;
        }

        $io->success(sprintf('%d rappel(s) envoyé(s).', $sent));
        return Command::SUCCESS;
    }
}
