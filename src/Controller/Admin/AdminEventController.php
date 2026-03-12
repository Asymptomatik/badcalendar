<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Entity\EventRegistration;
use App\Enum\EventRegistrationStatus;
use App\Form\EventFormType;
use App\Repository\EventRepository;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des événements par l'administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/evenements')]
class AdminEventController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventRepository $eventRepository,
    ) {}

    #[Route('', name: 'admin_event_list', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/events/index.html.twig', [
            'events' => $this->eventRepository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $event = new Event();
        $form = $this->createForm(EventFormType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($event);
            $this->em->flush();
            $this->addFlash('success', 'Événement créé.');
            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('admin/events/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/inscriptions', name: 'admin_event_registrations', methods: ['GET'])]
    public function registrations(Event $event): Response
    {
        return $this->render('admin/events/registrations.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/inscription/{id}/valider', name: 'admin_event_registration_validate', methods: ['POST'])]
    public function validateRegistration(EventRegistration $registration, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('registration_validate_' . $registration->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $registration->setStatus(EventRegistrationStatus::INSCRIT);
        $this->em->flush();
        $this->addFlash('success', 'Inscription validée.');

        return $this->redirectToRoute('admin_event_registrations', ['id' => $registration->getEvent()->getId()]);
    }
}
