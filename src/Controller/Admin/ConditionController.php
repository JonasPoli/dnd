<?php

namespace App\Controller\Admin;

use App\Entity\Condition;
use App\Form\ConditionType;
use App\Repository\ConditionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/condition')]
#[IsGranted('ROLE_USER')]
class ConditionController extends AbstractController
{
    #[Route('/', name: 'admin_condition_index', methods: ['GET'])]
    public function index(ConditionRepository $conditionRepo): Response
    {
        return $this->render('admin/condition/index.html.twig', [
            'conditions' => $conditionRepo->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_condition_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $condition = new Condition();
        $form = $this->createForm(ConditionType::class, $condition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($condition);
            $entityManager->flush();
            $this->addFlash('success', 'Condição criada com sucesso!');
            return $this->redirectToRoute('admin_condition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/condition/new.html.twig', [
            'condition' => $condition,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_condition_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Condition $condition, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ConditionType::class, $condition);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Condição atualizada com sucesso!');
            return $this->redirectToRoute('admin_condition_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/condition/edit.html.twig', [
            'condition' => $condition,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_condition_delete', methods: ['POST'])]
    public function delete(Request $request, Condition $condition, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $condition->getId(), $request->request->get('_token'))) {
            $entityManager->remove($condition);
            $entityManager->flush();
            $this->addFlash('success', 'Condição excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_condition_index', [], Response::HTTP_SEE_OTHER);
    }
}
