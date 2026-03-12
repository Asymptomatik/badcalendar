<?php

namespace App\Controller;

use App\Repository\EventRegistrationRepository;
use App\Repository\LostItemRepository;
use App\Repository\SessionRegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur du tableau de bord membre.
 */
#[IsGranted('ROLE_MEMBER')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard_member')]
    public function index(
        SessionRegistrationRepository $sessionRegistrationRepo,
        EventRegistrationRepository $eventRegistrationRepo,
        LostItemRepository $lostItemRepo,
    ): Response {
        $user = $this->getUser();

        return $this->render('dashboard/member.html.twig', [
            'upcomingSessions' => $sessionRegistrationRepo->findUpcomingByUser($user),
            'myEventRegistrations' => $eventRegistrationRepo->findBy(['user' => $user], ['createdAt' => 'DESC'], 5),
            'recentLostItems' => $lostItemRepo->findRecent(5),
        ]);
    }
}
