<?php

namespace App\Controller\Admin;

use App\Entity\MagicItem;
use App\Form\MagicItemType;
use App\Repository\MagicItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/magic-item')]
#[IsGranted('ROLE_USER')]
class MagicItemController extends AbstractController
{
    #[Route('/', name: 'admin_magic_item_index', methods: ['GET'])]
    public function index(Request $request, MagicItemRepository $repository): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;
        $search = $request->query->get('search', '');
        $typeFilter = $request->query->get('type', '');
        $rarityFilter = $request->query->get('rarity', '');
        $sourceFilter = $request->query->get('source', '');

        $qb = $repository->createQueryBuilder('m')
            ->leftJoin('m.rulesSource', 's')
            ->orderBy('m.name', 'ASC');

        if ($search) {
            $qb->andWhere('m.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($typeFilter) {
            $qb->andWhere('m.type = :type')
                ->setParameter('type', $typeFilter);
        }

        if ($rarityFilter) {
            $qb->andWhere('m.rarity = :rarity')
                ->setParameter('rarity', $rarityFilter);
        }

        if ($sourceFilter) {
            $qb->andWhere('m.rulesSource = :source')
                ->setParameter('source', $sourceFilter);
        }

        $totalItems = count($qb->getQuery()->getResult());
        $totalPages = ceil($totalItems / $limit);

        $items = $qb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Get unique values for filters
        $types = $repository->createQueryBuilder('m')
            ->select('DISTINCT m.type')
            ->where('m.type IS NOT NULL')
            ->orderBy('m.type', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        $rarities = $repository->createQueryBuilder('m')
            ->select('DISTINCT m.rarity')
            ->where('m.rarity IS NOT NULL')
            ->orderBy('m.rarity', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();

        return $this->render('admin/magic_item/index.html.twig', [
            'magic_items' => $items,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'search' => $search,
            'type_filter' => $typeFilter,
            'rarity_filter' => $rarityFilter,
            'source_filter' => $sourceFilter,
            'available_types' => $types,
            'available_rarities' => $rarities,
        ]);
    }

    #[Route('/new', name: 'admin_magic_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $item = new MagicItem();
        $form = $this->createForm(MagicItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();
            $this->addFlash('success', 'Item Mágico criado com sucesso!');
            return $this->redirectToRoute('admin_magic_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/magic_item/new.html.twig', [
            'magic_item' => $item,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_magic_item_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(MagicItem $magicItem): Response
    {
        return $this->render('admin/magic_item/show.html.twig', [
            'magic_item' => $magicItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_magic_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MagicItem $item, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MagicItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Item Mágico atualizado com sucesso!');
            return $this->redirectToRoute('admin_magic_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/magic_item/edit.html.twig', [
            'magic_item' => $item,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_magic_item_delete', methods: ['POST'])]
    public function delete(Request $request, MagicItem $item, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $item->getId(), $request->request->get('_token'))) {
            $entityManager->remove($item);
            $entityManager->flush();
            $this->addFlash('success', 'Item Mágico excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_magic_item_index', [], Response::HTTP_SEE_OTHER);
    }
}
