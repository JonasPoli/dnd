<?php

namespace App\Controller\Admin;

use App\Entity\Species;
use App\Form\SpeciesType;
use App\Repository\SpeciesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/species')]
#[IsGranted('ROLE_USER')]
class SpeciesController extends AbstractController
{
    #[Route('/', name: 'admin_species_index', methods: ['GET'])]
    public function index(SpeciesRepository $speciesRepository): Response
    {
        return $this->render('admin/species/index.html.twig', [
            'species_list' => $speciesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_species_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $species = new Species();
        $form = $this->createForm(SpeciesType::class, $species);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($species);
            $entityManager->flush();
            $this->addFlash('success', 'Raça criada com sucesso!');
            return $this->redirectToRoute('admin_species_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/species/new.html.twig', [
            'species' => $species,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_species_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Species $species, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SpeciesType::class, $species);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Raça atualizada com sucesso!');
            return $this->redirectToRoute('admin_species_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/species/edit.html.twig', [
            'species' => $species,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_species_delete', methods: ['POST'])]
    public function delete(Request $request, Species $species, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $species->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($species);
            $entityManager->flush();
            $this->addFlash('success', 'Raça excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_species_index', [], Response::HTTP_SEE_OTHER);
    }
}
