<?php

namespace App\Controller\Admin;

use App\Entity\ClassDef;
use App\Entity\Skill;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/class-skill')]
class ClassSkillController extends AbstractController
{
    #[Route('/{id}', name: 'admin_class_skill_index', methods: ['GET', 'POST'])]
    public function index(ClassDef $classDef, SkillRepository $skillRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $skillId = $request->request->get('skill');
            $skill = $skillRepository->find($skillId);

            if ($skill) {
                $classDef->addBaseSkill($skill);
                $entityManager->flush();
                $this->addFlash('success', 'Perícia adicionada com sucesso!');
            }

            return $this->redirectToRoute('admin_class_skill_index', ['id' => $classDef->getId()]);
        }

        return $this->render('admin/class_skill/index.html.twig', [
            'class_def' => $classDef,
            'all_skills' => $skillRepository->findAll(),
        ]);
    }

    #[Route('/{id}/remove/{skill_id}', name: 'admin_class_skill_remove', methods: ['POST'])]
    public function remove(ClassDef $classDef, int $skill_id, SkillRepository $skillRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $skill = $skillRepository->find($skill_id);

        if ($this->isCsrfTokenValid('delete' . $skill->getId(), $request->request->get('_token'))) {
            $classDef->removeBaseSkill($skill);
            $entityManager->flush();
            $this->addFlash('success', 'Perícia removida com sucesso!');
        }

        return $this->redirectToRoute('admin_class_skill_index', ['id' => $classDef->getId()]);
    }
}
