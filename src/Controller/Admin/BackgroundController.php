<?php

namespace App\Controller\Admin;

use App\Entity\Background;
use App\Form\BackgroundType;
use App\Repository\BackgroundRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/background')]
#[IsGranted('ROLE_USER')]
class BackgroundController extends AbstractController
{
    #[Route('/', name: 'admin_background_index', methods: ['GET'])]
    public function index(BackgroundRepository $backgroundRepository): Response
    {
        return $this->render('admin/background/index.html.twig', [
            'backgrounds' => $backgroundRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_background_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $background = new Background();
        $form = $this->createForm(BackgroundType::class, $background);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($background);
            $entityManager->flush();
            $this->addFlash('success', 'Antecedente criado com sucesso!');
            return $this->redirectToRoute('admin_background_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/background/new.html.twig', [
            'background' => $background,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_background_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Background $background, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BackgroundType::class, $background);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Antecedente atualizado com sucesso!');
            return $this->redirectToRoute('admin_background_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/background/edit.html.twig', [
            'background' => $background,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_background_delete', methods: ['POST'])]
    public function delete(Request $request, Background $background, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $background->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($background);
            $entityManager->flush();
            $this->addFlash('success', 'Antecedente excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_background_index', [], Response::HTTP_SEE_OTHER);
    }
}
