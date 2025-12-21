<?php

namespace App\Controller\Admin;

use App\Entity\SubclassDef;
use App\Form\SubclassDefType;
use App\Repository\SubclassDefRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/subclass')]
#[IsGranted('ROLE_USER')]
class SubclassDefController extends AbstractController
{
    #[Route('/', name: 'admin_subclass_index', methods: ['GET'])]
    public function index(SubclassDefRepository $subclassDefRepository): Response
    {
        return $this->render('admin/subclass_def/index.html.twig', [
            'subclasses' => $subclassDefRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_subclass_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subclass = new SubclassDef();
        $form = $this->createForm(SubclassDefType::class, $subclass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subclass);
            $entityManager->flush();
            $this->addFlash('success', 'Subclasse criada com sucesso!');
            return $this->redirectToRoute('admin_subclass_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/subclass_def/new.html.twig', [
            'subclass' => $subclass,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_subclass_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SubclassDef $subclass, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubclassDefType::class, $subclass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Subclasse atualizada com sucesso!');
            return $this->redirectToRoute('admin_subclass_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/subclass_def/edit.html.twig', [
            'subclass' => $subclass,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_subclass_delete', methods: ['POST'])]
    public function delete(Request $request, SubclassDef $subclass, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $subclass->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subclass);
            $entityManager->flush();
            $this->addFlash('success', 'Subclasse excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_subclass_index', [], Response::HTTP_SEE_OTHER);
    }
}
