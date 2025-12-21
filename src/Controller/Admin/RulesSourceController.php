<?php

namespace App\Controller\Admin;

use App\Entity\RulesSource;
use App\Form\RulesSourceType;
use App\Repository\RulesSourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/rules-source')]
#[IsGranted('ROLE_USER')]
class RulesSourceController extends AbstractController
{
    #[Route('/', name: 'admin_rules_source_index', methods: ['GET'])]
    public function index(RulesSourceRepository $rulesSourceRepository): Response
    {
        return $this->render('admin/rules_source/index.html.twig', [
            'rules_sources' => $rulesSourceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_rules_source_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rulesSource = new RulesSource();
        $form = $this->createForm(RulesSourceType::class, $rulesSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rulesSource);
            $entityManager->flush();

            $this->addFlash('success', 'Fonte de dados criada com sucesso!');

            return $this->redirectToRoute('admin_rules_source_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rules_source/new.html.twig', [
            'rules_source' => $rulesSource,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_rules_source_show', methods: ['GET'])]
    public function show(RulesSource $rulesSource): Response
    {
        return $this->render('admin/rules_source/show.html.twig', [
            'rules_source' => $rulesSource,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_rules_source_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RulesSource $rulesSource, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RulesSourceType::class, $rulesSource);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Fonte de dados atualizada com sucesso!');

            return $this->redirectToRoute('admin_rules_source_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rules_source/edit.html.twig', [
            'rules_source' => $rulesSource,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_rules_source_delete', methods: ['POST'])]
    public function delete(Request $request, RulesSource $rulesSource, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $rulesSource->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($rulesSource);
            $entityManager->flush();
            $this->addFlash('success', 'Fonte de dados excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_rules_source_index', [], Response::HTTP_SEE_OTHER);
    }
}
