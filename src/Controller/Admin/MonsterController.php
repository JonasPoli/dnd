<?php

namespace App\Controller\Admin;

use App\Entity\Monster;
use App\Form\MonsterType;
use App\Repository\MonsterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/monster')]
#[IsGranted('ROLE_USER')]
class MonsterController extends AbstractController
{
    #[Route('/', name: 'admin_monster_index', methods: ['GET'])]
    public function index(Request $request, MonsterRepository $repository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;
        $sort = $request->query->get('sort', 'name');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');
        $typeFilter = $request->query->get('type', '');
        $sizeFilter = $request->query->get('size', '');
        $crMin = $request->query->get('cr_min', '');
        $crMax = $request->query->get('cr_max', '');

        $qb = $repository->createQueryBuilder('m');

        // Apply filters
        if ($search) {
            $qb->andWhere('m.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($typeFilter) {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $typeFilter);
        }

        if ($sizeFilter) {
            $qb->andWhere('m.size = :size')
                ->setParameter('size', $sizeFilter);
        }

        if ($crMin !== '') {
            $qb->andWhere('CAST(m.challengeRating AS DECIMAL(10,2)) >= :crMin')
                ->setParameter('crMin', (float) $crMin);
        }

        if ($crMax !== '') {
            $qb->andWhere('CAST(m.challengeRating AS DECIMAL(10,2)) <= :crMax')
                ->setParameter('crMax', (float) $crMax);
        }

        // Apply sorting
        $validSorts = ['name', 'challengeRating', 'type', 'size'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'name';
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $qb->orderBy('m.' . $sort, $order);

        $totalItems = count($qb->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        $monsters = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Get unique types and sizes for filters
        $types = $repository->createQueryBuilder('m')
            ->select('DISTINCT m.type')
            ->where('m.type IS NOT NULL')
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $sizes = $repository->createQueryBuilder('m')
            ->select('DISTINCT m.size')
            ->where('m.size IS NOT NULL')
            ->orderBy('m.size', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->render('admin/monster/index.html.twig', [
            'monsters' => $monsters,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'sort' => $sort,
            'order' => $order,
            'search' => $search,
            'type_filter' => $typeFilter,
            'size_filter' => $sizeFilter,
            'cr_min' => $crMin,
            'cr_max' => $crMax,
            'available_types' => $types,
            'available_sizes' => $sizes,
        ]);
    }

    #[Route('/new', name: 'admin_monster_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $monster = new Monster();
        $form = $this->createForm(MonsterType::class, $monster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($monster);
            $entityManager->flush();
            $this->addFlash('success', 'Monstro criado com sucesso!');
            return $this->redirectToRoute('admin_monster_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/monster/new.html.twig', [
            'monster' => $monster,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_monster_show', methods: ['GET'])]
    public function show(Monster $monster, \App\Service\UnsplashImageService $imageService): Response
    {
        $imageUrl = $imageService->searchImage('monster', $monster->getName());

        if (!$imageUrl) {
            $imageUrl = $imageService->getPlaceholderImage('monster');
        }

        return $this->render('admin/monster/show.html.twig', [
            'monster' => $monster,
            'image_url' => $imageUrl,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_monster_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Monster $monster, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MonsterType::class, $monster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Monstro atualizado com sucesso!');
            return $this->redirectToRoute('admin_monster_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/monster/edit.html.twig', [
            'monster' => $monster,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_monster_delete', methods: ['POST'])]
    public function delete(Request $request, Monster $monster, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $monster->getId(), $request->request->get('_token'))) {
            $entityManager->remove($monster);
            $entityManager->flush();
            $this->addFlash('success', 'Monstro excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_monster_index', [], Response::HTTP_SEE_OTHER);
    }
}
