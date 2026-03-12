<?php

namespace App\Controller;

use App\Entity\Session;
use App\Enum\SessionType;
use App\Repository\SessionRepository;
use App\Service\SessionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la gestion des séances (vue membre).
 */
#[IsGranted('ROLE_MEMBER')]
#[Route('/seances')]
class SessionController extends AbstractController
{
    public function __construct(
        private readonly SessionServiceInterface $sessionService,
        private readonly SessionRepository $sessionRepository,
    ) {}

    /** Liste des prochaines séances */
    #[Route('', name: 'session_list', methods: ['GET'])]
    public function index(): Response
    {
        $sessions = $this->sessionRepository->findUpcoming(20);
        $user = $this->getUser();

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
            'user' => $user,
        ]);
    }

    /** Détail d'une séance */
    #[Route('/{id}', name: 'session_show', methods: ['GET'])]
    public function show(Session $session): Response
    {
        $user = $this->getUser();
        $canAccess = $this->sessionService->canAccess($session, $user);

        return $this->render('session/show.html.twig', [
            'session' => $session,
            'canAccess' => $canAccess,
            'isRegistered' => $session->isRegistered($user),
        ]);
    }

    /** Inscription à une séance */
    #[Route('/{id}/inscription', name: 'session_register', methods: ['POST'])]
    public function register(Session $session, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MEMBER');

        if (!$this->isCsrfTokenValid('session_register_' . $session->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('session_show', ['id' => $session->getId()]);
        }

        $user = $this->getUser();

        try {
            $this->sessionService->register($session, $user);
            $this->addFlash('success', 'Vous êtes inscrit(e) à la séance.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('session_show', ['id' => $session->getId()]);
    }

    /** Désinscription d'une séance */
    #[Route('/{id}/desinscription', name: 'session_unregister', methods: ['POST'])]
    public function unregister(Session $session, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_MEMBER');

        if (!$this->isCsrfTokenValid('session_unregister_' . $session->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('session_show', ['id' => $session->getId()]);
        }

        $user = $this->getUser();
        $this->sessionService->unregister($session, $user);
        $this->addFlash('success', 'Vous êtes désinscrit(e) de la séance.');

        return $this->redirectToRoute('session_show', ['id' => $session->getId()]);
    }

    /** Page spéciale pour proposer une séance du dimanche */
    #[Route('/dimanche/proposer', name: 'session_sunday_propose', methods: ['GET', 'POST'])]
    public function proposeSunday(Request $request): Response
    {
        return $this->render('session/sunday.html.twig');
    }
}
