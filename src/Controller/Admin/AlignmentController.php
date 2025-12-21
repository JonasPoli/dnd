<?php

namespace App\Controller\Admin;

use App\Entity\Alignment;
use App\Form\AlignmentType;
use App\Repository\AlignmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/alignment')]
#[IsGranted('ROLE_USER')]
class AlignmentController extends AbstractController
{
    #[Route('/', name: 'admin_alignment_index', methods: ['GET'])]
    public function index(AlignmentRepository $alignmentRepository): Response
    {
        return $this->render('admin/alignment/index.html.twig', [
            'alignments' => $alignmentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_alignment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $alignment = new Alignment();
        $form = $this->createForm(AlignmentType::class, $alignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($alignment);
            $entityManager->flush();
            $this->addFlash('success', 'Alinhamento criado com sucesso!');
            return $this->redirectToRoute('admin_alignment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/alignment/new.html.twig', [
            'alignment' => $alignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_alignment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Alignment $alignment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AlignmentType::class, $alignment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Alinhamento atualizado com sucesso!');
            return $this->redirectToRoute('admin_alignment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/alignment/edit.html.twig', [
            'alignment' => $alignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_alignment_delete', methods: ['POST'])]
    public function delete(Request $request, Alignment $alignment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $alignment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($alignment);
            $entityManager->flush();
            $this->addFlash('success', 'Alinhamento excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_alignment_index', [], Response::HTTP_SEE_OTHER);
    }
}
