<?php

namespace App\Controller\Admin;

use App\Entity\Feat;
use App\Form\FeatType;
use App\Repository\FeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/feat')]
#[IsGranted('ROLE_USER')]
class FeatController extends AbstractController
{
    #[Route('/', name: 'admin_feat_index', methods: ['GET'])]
    public function index(Request $request, FeatRepository $repository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;
        $search = $request->query->get('search', '');

        $qb = $repository->createQueryBuilder('f')
            ->orderBy('f.name', 'ASC');

        if ($search) {
            $qb->where('f.name LIKE :search OR f.prerequisite LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        $totalItems = count($qb->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        $feats = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $this->render('admin/feat/index.html.twig', [
            'feats' => $feats,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'admin_feat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feat = new Feat();
        $form = $this->createForm(FeatType::class, $feat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feat);
            $entityManager->flush();
            $this->addFlash('success', 'Talento criado com sucesso!');
            return $this->redirectToRoute('admin_feat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/feat/new.html.twig', [
            'feat' => $feat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_feat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Feat $feat, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FeatType::class, $feat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Talento atualizado com sucesso!');
            return $this->redirectToRoute('admin_feat_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/feat/edit.html.twig', [
            'feat' => $feat,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_feat_delete', methods: ['POST'])]
    public function delete(Request $request, Feat $feat, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $feat->getId(), $request->request->get('_token'))) {
            $entityManager->remove($feat);
            $entityManager->flush();
            $this->addFlash('success', 'Talento excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_feat_index', [], Response::HTTP_SEE_OTHER);
    }
}
