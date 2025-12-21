<?php

namespace App\Controller\Admin;

use App\Entity\Feature;
use App\Form\FeatureType;
use App\Repository\FeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/feature')]
#[IsGranted('ROLE_USER')]
class FeatureController extends AbstractController
{
    #[Route('/', name: 'admin_feature_index', methods: ['GET'])]
    public function index(FeatureRepository $featureRepository, Request $request): Response
    {
        // Simple pagination or limit could be added here if needed
        return $this->render('admin/feature/index.html.twig', [
            'features' => $featureRepository->findBy([], ['id' => 'DESC'], 50), // Limit to last 50 for performance
        ]);
    }

    #[Route('/new', name: 'admin_feature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feature = new Feature();
        $form = $this->createForm(FeatureType::class, $feature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feature);
            $entityManager->flush();
            $this->addFlash('success', 'Feature criada com sucesso!');
            return $this->redirectToRoute('admin_feature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/feature/new.html.twig', [
            'feature' => $feature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_feature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feature $feature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FeatureType::class, $feature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Feature atualizada com sucesso!');
            return $this->redirectToRoute('admin_feature_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/feature/edit.html.twig', [
            'feature' => $feature,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_feature_delete', methods: ['POST'])]
    public function delete(Request $request, Feature $feature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $feature->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($feature);
            $entityManager->flush();
            $this->addFlash('success', 'Feature excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_feature_index', [], Response::HTTP_SEE_OTHER);
    }
}
