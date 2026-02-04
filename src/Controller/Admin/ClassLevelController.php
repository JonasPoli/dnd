<?php

namespace App\Controller\Admin;

use App\Entity\ClassDef;
use App\Entity\ClassLevel;
use App\Form\ClassLevelType;
use App\Repository\ClassLevelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/class/{id}/levels')]
class ClassLevelController extends AbstractController
{
    #[Route('/', name: 'admin_class_level_index', methods: ['GET'])]
    public function index(ClassDef $classDef): Response
    {
        return $this->render('admin/class_level/index.html.twig', [
            'class_def' => $classDef,
            'levels' => $classDef->getClassLevels()->toArray(),
        ]);
    }

    #[Route('/new', name: 'admin_class_level_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ClassDef $classDef, EntityManagerInterface $entityManager): Response
    {
        $classLevel = new ClassLevel();
        $classLevel->setClassDef($classDef);
        
        // Auto-increment level suggestion
        $currentMaxLevel = 0;
        foreach ($classDef->getClassLevels() as $level) {
            if ($level->getLevel() > $currentMaxLevel) {
                $currentMaxLevel = $level->getLevel();
            }
        }
        $classLevel->setLevel($currentMaxLevel + 1);

        $form = $this->createForm(ClassLevelType::class, $classLevel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($classLevel);
            $entityManager->flush();

            return $this->redirectToRoute('admin_class_level_index', ['id' => $classDef->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/class_level/new.html.twig', [
            'class_def' => $classDef,
            'class_level' => $classLevel,
            'form' => $form,
        ]);
    }

    #[Route('/{level_id}/edit', name: 'admin_class_level_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ClassDef $classDef, int $level_id, ClassLevelRepository $classLevelRepository, EntityManagerInterface $entityManager): Response
    {
        $classLevel = $classLevelRepository->find($level_id);

        if (!$classLevel) {
            throw $this->createNotFoundException('Nível da classe não encontrado');
        }

        $form = $this->createForm(ClassLevelType::class, $classLevel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_class_level_index', ['id' => $classDef->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/class_level/edit.html.twig', [
            'class_def' => $classDef,
            'class_level' => $classLevel,
            'form' => $form,
        ]);
    }

    #[Route('/{level_id}', name: 'admin_class_level_delete', methods: ['POST'])]
    public function delete(Request $request, ClassDef $classDef, int $level_id, ClassLevelRepository $classLevelRepository, EntityManagerInterface $entityManager): Response
    {
        $classLevel = $classLevelRepository->find($level_id);

        if ($classLevel && $this->isCsrfTokenValid('delete' . $classLevel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($classLevel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_class_level_index', ['id' => $classDef->getId()], Response::HTTP_SEE_OTHER);
    }
}
