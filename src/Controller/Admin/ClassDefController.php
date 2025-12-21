<?php

namespace App\Controller\Admin;

use App\Entity\ClassDef;
use App\Form\ClassDefType;
use App\Repository\ClassDefRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/class')]
#[IsGranted('ROLE_USER')]
class ClassDefController extends AbstractController
{
    #[Route('/', name: 'admin_class_index', methods: ['GET'])]
    public function index(ClassDefRepository $classDefRepository): Response
    {
        return $this->render('admin/class_def/index.html.twig', [
            'class_defs' => $classDefRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_class_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $classDef = new ClassDef();
        $form = $this->createForm(ClassDefType::class, $classDef);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($classDef);
            $entityManager->flush();
            $this->addFlash('success', 'Classe criada com sucesso!');
            return $this->redirectToRoute('admin_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/class_def/new.html.twig', [
            'class_def' => $classDef,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_class_show', methods: ['GET'])]
    public function show(ClassDef $classDef): Response
    {
        return $this->render('admin/class_def/show.html.twig', [
            'class_def' => $classDef,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_class_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClassDef $classDef, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ClassDefType::class, $classDef);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Classe atualizada com sucesso!');
            return $this->redirectToRoute('admin_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/class_def/edit.html.twig', [
            'class_def' => $classDef,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_class_delete', methods: ['POST'])]
    public function delete(Request $request, ClassDef $classDef, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $classDef->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($classDef);
            $entityManager->flush();
            $this->addFlash('success', 'Classe excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_class_index', [], Response::HTTP_SEE_OTHER);
    }
}
