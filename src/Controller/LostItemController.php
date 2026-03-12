<?php

namespace App\Controller;

use App\Entity\LostItem;
use App\Form\LostItemFormType;
use App\Repository\LostItemRepository;
use App\Service\LostItemServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur pour la gestion des objets trouvés.
 */
#[IsGranted('ROLE_MEMBER')]
#[Route('/objets-trouves')]
class LostItemController extends AbstractController
{
    public function __construct(
        private readonly LostItemServiceInterface $lostItemService,
    ) {}

    /** Liste des objets trouvés */
    #[Route('', name: 'lost_item_list', methods: ['GET'])]
    public function index(LostItemRepository $lostItemRepo): Response
    {
        return $this->render('lost_item/index.html.twig', [
            'lostItems' => $lostItemRepo->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    /** Formulaire de déclaration d'un objet trouvé */
    #[Route('/nouveau', name: 'lost_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $lostItem = new LostItem();
        $form = $this->createForm(LostItemFormType::class, $lostItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->lostItemService->declare($lostItem, $this->getUser());
            $this->addFlash('success', 'L\'objet trouvé a été déclaré.');
            return $this->redirectToRoute('lost_item_list');
        }

        return $this->render('lost_item/new.html.twig', [
            'form' => $form,
        ]);
    }
}
