<?php

namespace App\Controller\Admin;

use App\Entity\RuleSection;
use App\Form\RuleSectionType;
use App\Repository\RuleSectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/rule-section')]
#[IsGranted('ROLE_USER')]
class RuleSectionController extends AbstractController
{
    #[Route('/', name: 'admin_rule_section_index', methods: ['GET'])]
    public function index(Request $request, RuleSectionRepository $repository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;
        $search = $request->query->get('search', '');

        $qb = $repository->createQueryBuilder('r')
            ->orderBy('r.name', 'ASC');

        if ($search) {
            $qb->where('r.name LIKE :search OR r.contentMd LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $totalItems = count($qb->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        $ruleSections = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('admin/rule_section/index.html.twig', [
            'rule_sections' => $ruleSections,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'admin_rule_section_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $ruleSection = new RuleSection();
        $form = $this->createForm(RuleSectionType::class, $ruleSection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ruleSection);
            $entityManager->flush();
            $this->addFlash('success', 'Seção de regra criada com sucesso!');
            return $this->redirectToRoute('admin_rule_section_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rule_section/new.html.twig', [
            'rule_section' => $ruleSection,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_rule_section_show', methods: ['GET'])]
    public function show(RuleSection $ruleSection): Response
    {
        return $this->render('admin/rule_section/show.html.twig', [
            'rule_section' => $ruleSection,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_rule_section_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RuleSection $ruleSection, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RuleSectionType::class, $ruleSection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Seção de regra atualizada com sucesso!');
            return $this->redirectToRoute('admin_rule_section_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/rule_section/edit.html.twig', [
            'rule_section' => $ruleSection,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_rule_section_delete', methods: ['POST'])]
    public function delete(Request $request, RuleSection $ruleSection, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ruleSection->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ruleSection);
            $entityManager->flush();
            $this->addFlash('success', 'Seção de regra excluída com sucesso!');
        }

        return $this->redirectToRoute('admin_rule_section_index', [], Response::HTTP_SEE_OTHER);
    }
}
