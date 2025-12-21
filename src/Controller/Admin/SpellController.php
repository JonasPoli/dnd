<?php

namespace App\Controller\Admin;

use App\Entity\Spell;
use App\Form\SpellType;
use App\Repository\SpellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/spell')]
#[IsGranted('ROLE_USER')]
class SpellController extends AbstractController
{
    #[Route('/', name: 'admin_spell_index', methods: ['GET'])]
    public function index(Request $request, SpellRepository $repository, \App\Repository\ClassDefRepository $classDefRepository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;
        $search = $request->query->get('search', '');
        $levelFilter = $request->query->get('level', '');
        $schoolFilter = $request->query->get('school', '');
        $classFilter = $request->query->get('class', '');

        $qb = $repository->createQueryBuilder('s')
            ->leftJoin('s.classes', 'c')
            ->orderBy('s.level', 'ASC')
            ->addOrderBy('s.name', 'ASC');

        if ($search) {
            $qb->andWhere('s.name LIKE :search OR s.descriptionMd LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($levelFilter !== '') {
            $qb->andWhere('s.level = :level')
                ->setParameter('level', (int) $levelFilter);
        }

        if ($schoolFilter) {
            $qb->andWhere('s.school = :school')
                ->setParameter('school', $schoolFilter);
        }

        if ($classFilter) {
            $qb->andWhere('c.id = :classId')
                ->setParameter('classId', $classFilter);
        }

        $totalItems = count($qb->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        $spells = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Get unique values for filters
        $schools = $repository->createQueryBuilder('s')
            ->select('DISTINCT s.school')
            ->where('s.school IS NOT NULL')
            ->orderBy('s.school', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        // Get all classes for filter
        $classes = $classDefRepository->findAll();

        return $this->render('admin/spell/index.html.twig', [
            'spells' => $spells,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'search' => $search,
            'level_filter' => $levelFilter,
            'school_filter' => $schoolFilter,
            'class_filter' => $classFilter,
            'available_schools' => $schools,
            'available_classes' => $classes,
        ]);
    }

    #[Route('/new', name: 'admin_spell_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $spell = new Spell();
        $form = $this->createForm(SpellType::class, $spell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($spell);
            $entityManager->flush();
            $this->addFlash('success', 'Magia criada com sucesso!');
            return $this->redirectToRoute('admin_spell_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/spell/new.html.twig', [
            'spell' => $spell,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_spell_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Spell $spell, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SpellType::class, $spell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Magia atualizada com sucesso!');
            return $this->redirectToRoute('admin_spell_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/spell/edit.html.twig', [
            'spell' => $spell,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_spell_delete', methods: ['POST'])]
    public function delete(Request $request, Spell $spell, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $spell->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($spell);
            $entityManager->flush();
            $this->addFlash('success', 'Magia excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_spell_index', [], Response::HTTP_SEE_OTHER);
    }
}
