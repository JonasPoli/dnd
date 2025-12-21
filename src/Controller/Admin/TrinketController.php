<?php

namespace App\Controller\Admin;

use App\Entity\Trinket;
use App\Form\TrinketType;
use App\Repository\TrinketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/trinket')]
#[IsGranted('ROLE_USER')]
class TrinketController extends AbstractController
{
    #[Route('/', name: 'admin_trinket_index', methods: ['GET'])]
    public function index(TrinketRepository $trinketRepository): Response
    {
        return $this->render('admin/trinket/index.html.twig', [
            'trinkets' => $trinketRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_trinket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trinket = new Trinket();
        $form = $this->createForm(TrinketType::class, $trinket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trinket);
            $entityManager->flush();
            $this->addFlash('success', 'Trinket criado com sucesso!');
            return $this->redirectToRoute('admin_trinket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/trinket/new.html.twig', [
            'trinket' => $trinket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_trinket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Trinket $trinket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TrinketType::class, $trinket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Trinket atualizado com sucesso!');
            return $this->redirectToRoute('admin_trinket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/trinket/edit.html.twig', [
            'trinket' => $trinket,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_trinket_delete', methods: ['POST'])]
    public function delete(Request $request, Trinket $trinket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trinket->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($trinket);
            $entityManager->flush();
            $this->addFlash('success', 'Trinket excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_trinket_index', [], Response::HTTP_SEE_OTHER);
    }
}
