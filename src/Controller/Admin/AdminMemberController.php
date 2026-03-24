<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Enum\MemberType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des membres par l'administrateur.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/membres')]
class AdminMemberController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('', name: 'admin_member_list', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findBy([], ['lastName' => 'ASC']);

        return $this->render('admin/members/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}', name: 'admin_member_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/members/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/type', name: 'admin_member_set_type', methods: ['POST'])]
    public function setType(User $user, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('member_type_' . $user->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_member_show', ['id' => $user->getId()]);
        }

        $typeValue = $request->getPayload()->get('member_type');
        $memberType = MemberType::tryFrom($typeValue);

        if ($memberType !== null) {
            $user->setMemberType($memberType);
            $this->em->flush();
            $this->addFlash('success', 'Type de membre mis à jour.');
        }

        return $this->redirectToRoute('admin_member_show', ['id' => $user->getId()]);
    }

    #[Route('/{id}/valider', name: 'admin_member_verify', methods: ['POST'])]
    public function verify(User $user, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('member_verify_' . $user->getId(), $request->getPayload()->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_member_show', ['id' => $user->getId()]);
        }

        $user->setIsVerified(true);
        $this->em->flush();
        $this->addFlash('success', 'Compte membre validé.');

        return $this->redirectToRoute('admin_member_show', ['id' => $user->getId()]);
    }
}
