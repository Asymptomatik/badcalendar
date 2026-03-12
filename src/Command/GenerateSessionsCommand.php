<?php

namespace App\Command;

use App\Service\SessionSchedulerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande de génération automatique des séances récurrentes.
 * Idempotente : peut être appelée plusieurs fois sans duplication.
 */
#[AsCommand(
    name: 'app:generate-sessions',
    description: 'Génère les séances récurrentes pour les prochaines semaines.',
)]
class GenerateSessionsCommand extends Command
{
    public function __construct(
        private readonly SessionSchedulerService $sessionSchedulerService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('weeks', 'w', InputOption::VALUE_OPTIONAL, 'Nombre de semaines à générer', 4);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $weeks = (int) $input->getOption('weeks');

        $io->info(sprintf('Génération des séances pour les %d prochaines semaines...', $weeks));

        $created = $this->sessionSchedulerService->generateUpcomingSessions($weeks);

        $io->success(sprintf('%d séance(s) créée(s).', $created));
        return Command::SUCCESS;
    }
}
