<?php

namespace App\Controller\Admin;

use App\Entity\Subrace;
use App\Form\SubraceType;
use App\Repository\SubraceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/subrace')]
#[IsGranted('ROLE_USER')]
class SubraceController extends AbstractController
{
    #[Route('/', name: 'admin_subrace_index', methods: ['GET'])]
    public function index(SubraceRepository $subraceRepository): Response
    {
        return $this->render('admin/subrace/index.html.twig', [
            'subraces' => $subraceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_subrace_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subrace = new Subrace();
        $form = $this->createForm(SubraceType::class, $subrace);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subrace);
            $entityManager->flush();
            $this->addFlash('success', 'Sub-raça criada com sucesso!');
            return $this->redirectToRoute('admin_subrace_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/subrace/new.html.twig', [
            'subrace' => $subrace,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_subrace_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subrace $subrace, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubraceType::class, $subrace);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Sub-raça atualizada com sucesso!');
            return $this->redirectToRoute('admin_subrace_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/subrace/edit.html.twig', [
            'subrace' => $subrace,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_subrace_delete', methods: ['POST'])]
    public function delete(Request $request, Subrace $subrace, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $subrace->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subrace);
            $entityManager->flush();
            $this->addFlash('success', 'Sub-raça excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_subrace_index', [], Response::HTTP_SEE_OTHER);
    }
}
