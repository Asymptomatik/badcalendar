<?php

namespace App\Controller\Admin;

use App\Repository\EventRegistrationRepository;
use App\Repository\LostItemRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableau de bord administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        SessionRepository $sessionRepo,
        EventRegistrationRepository $eventRegistrationRepo,
        UserRepository $userRepo,
        LostItemRepository $lostItemRepo,
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'sessionsWithoutResponsable' => $sessionRepo->findWithoutResponsableKeys(),
            'pendingValidations' => $eventRegistrationRepo->findPendingValidation(),
            'membersWithoutType' => $userRepo->findMembersWithoutType(),
            'unreturnedLostItems' => $lostItemRepo->findNotReturned(),
        ]);
    }
}
