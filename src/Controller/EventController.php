<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\EventRegistrationRepository;
use App\Service\EventServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la gestion des événements/tournois (vue membre).
 */
#[IsGranted('ROLE_MEMBER')]
#[Route('/evenements')]
class EventController extends AbstractController
{
    public function __construct(
        private readonly EventServiceInterface $eventService,
        private readonly EventRepository $eventRepository,
    ) {}

    /** Liste des événements */
    #[Route('', name: 'event_list', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('event/index.html.twig', [
            'upcomingEvents' => $this->eventRepository->findUpcoming(),
        ]);
    }

    /** Détail d'un événement */
    #[Route('/{id}', name: 'event_show', methods: ['GET'])]
    public function show(Event $event, EventRegistrationRepository $registrationRepo): Response
    {
        $user = $this->getUser();
        $registration = $registrationRepo->findOneByEventAndUser($event, $user);

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'registration' => $registration,
        ]);
    }

    /** Inscription à un événement */
    #[Route('/{id}/inscription', name: 'event_register', methods: ['POST'])]
    public function register(Event $event, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('event_register_' . $event->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        $this->eventService->register($event, $this->getUser());
        $this->addFlash('success', 'Votre inscription a été enregistrée.');

        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }

    /** Désistement d'un événement */
    #[Route('/{id}/desistement', name: 'event_desist', methods: ['POST'])]
    public function desist(Event $event, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('event_desist_' . $event->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        $this->eventService->desist($event, $this->getUser());
        $this->addFlash('success', 'Votre désistement a été enregistré.');

        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }

    /** Confirmation de participation */
    #[Route('/{id}/confirmation', name: 'event_confirm', methods: ['POST'])]
    public function confirm(Event $event, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('event_confirm_' . $event->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
        }

        $this->eventService->confirm($event, $this->getUser());
        $this->addFlash('success', 'Votre participation a été confirmée.');

        return $this->redirectToRoute('event_show', ['id' => $event->getId()]);
    }

    /** Mes inscriptions aux événements */
    #[Route('/mes-evenements', name: 'event_my_events', methods: ['GET'])]
    public function myEvents(EventRegistrationRepository $registrationRepo): Response
    {
        return $this->render('event/my_events.html.twig', [
            'registrations' => $registrationRepo->findBy(['user' => $this->getUser()], ['createdAt' => 'DESC']),
        ]);
    }
}
