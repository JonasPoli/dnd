<?php

namespace App\Controller\Admin;

use App\Entity\Glossary;
use App\Form\GlossaryType;
use App\Repository\GlossaryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/glossary')]
final class GlossaryController extends AbstractController
{
    #[Route(name: 'app_admin_glossary_index', methods: ['GET'])]
    public function index(GlossaryRepository $glossaryRepository): Response
    {
        return $this->render('admin/glossary/index.html.twig', [
            'glossaries' => $glossaryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_glossary_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $glossary = new Glossary();
        $form = $this->createForm(GlossaryType::class, $glossary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($glossary);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_glossary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/glossary/new.html.twig', [
            'glossary' => $glossary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_glossary_show', methods: ['GET'])]
    public function show(Glossary $glossary): Response
    {
        return $this->render('admin/glossary/show.html.twig', [
            'glossary' => $glossary,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_glossary_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Glossary $glossary, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(GlossaryType::class, $glossary);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_glossary_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/glossary/edit.html.twig', [
            'glossary' => $glossary,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_glossary_delete', methods: ['POST'])]
    public function delete(Request $request, Glossary $glossary, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$glossary->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($glossary);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_glossary_index', [], Response::HTTP_SEE_OTHER);
    }
}
