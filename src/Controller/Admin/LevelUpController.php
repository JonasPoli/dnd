<?php

namespace App\Controller\Admin;

use App\Entity\LevelUp;
use App\Form\LevelUpType;
use App\Repository\LevelUpRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/level-up')]
#[IsGranted('ROLE_USER')]
class LevelUpController extends AbstractController
{
    #[Route('/', name: 'admin_level_up_index', methods: ['GET'])]
    public function index(LevelUpRepository $levelUpRepository): Response
    {
        return $this->render('admin/level_up/index.html.twig', [
            'level_ups' => $levelUpRepository->findBy([], ['level' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'admin_level_up_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $levelUp = new LevelUp();
        $form = $this->createForm(LevelUpType::class, $levelUp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($levelUp);
            $entityManager->flush();
            $this->addFlash('success', 'Nível criado com sucesso!');
            return $this->redirectToRoute('admin_level_up_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/level_up/new.html.twig', [
            'level_up' => $levelUp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_level_up_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LevelUp $levelUp, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LevelUpType::class, $levelUp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Nível atualizado com sucesso!');
            return $this->redirectToRoute('admin_level_up_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/level_up/edit.html.twig', [
            'level_up' => $levelUp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_level_up_delete', methods: ['POST'])]
    public function delete(Request $request, LevelUp $levelUp, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $levelUp->getId(), $request->request->get('_token'))) {
            $entityManager->remove($levelUp);
            $entityManager->flush();
            $this->addFlash('success', 'Nível excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_level_up_index', [], Response::HTTP_SEE_OTHER);
    }
}
