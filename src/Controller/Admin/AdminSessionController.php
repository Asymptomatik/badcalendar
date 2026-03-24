<?php

namespace App\Controller\Admin;

use App\Entity\Session;
use App\Entity\User;
use App\Form\SessionFormType;
use App\Repository\SessionRepository;
use App\Service\SessionServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des séances par l'administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/seances')]
class AdminSessionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SessionServiceInterface $sessionService,
        private readonly SessionRepository $sessionRepository,
    ) {}

    #[Route('', name: 'admin_session_list', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/sessions/index.html.twig', [
            'sessions' => $this->sessionRepository->findUpcoming(30),
        ]);
    }

    #[Route('/{id}/modifier', name: 'admin_session_edit', methods: ['GET', 'POST'])]
    public function edit(Session $session, Request $request): Response
    {
        $form = $this->createForm(SessionFormType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Séance mise à jour.');
            return $this->redirectToRoute('admin_session_list');
        }

        return $this->render('admin/sessions/edit.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/responsable', name: 'admin_session_assign_responsable', methods: ['POST'])]
    public function assignResponsable(Session $session, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('session_responsable_' . $session->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_session_list');
        }

        $userId = $request->getPayload()->get('user_id');
        $user = $this->em->getRepository(User::class)->find($userId);

        if ($user !== null) {
            $this->sessionService->assignResponsableKeys($session, $user);
            $this->addFlash('success', 'Responsable clés assigné.');
        }

        return $this->redirectToRoute('admin_session_list');
    }
}
