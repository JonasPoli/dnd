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
        $limit = 50; // Aumentado para 50 itens por página
        $sort = $request->query->get('sort', 'name');
        $order = $request->query->get('order', 'asc');
        $search = $request->query->get('search', '');
        $typeFilter = $request->query->get('type', '');
        $sizeFilter = $request->query->get('size', '');
        $crMin = $request->query->get('cr_min', '');
        $crMin = $request->query->get('cr_min', '');
        $crMax = $request->query->get('cr_max', '');
        $statusFilter = $request->query->get('status', 'active');

        $qb = $repository->createQueryBuilder('m');

        // Apply filters
        if ($statusFilter === 'active') {
            $qb->andWhere('m.isActive = :active')
                ->setParameter('active', true);
        } elseif ($statusFilter === 'inactive') {
            $qb->andWhere('m.isActive = :active')
                ->setParameter('active', false);
        }
        if ($search) {
            $qb->andWhere('m.name LIKE :search OR m.namePt LIKE :search OR m.ruleSlug LIKE :search')
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

        // Use a coluna decimal 'cr' para filtragem numérica correta em vez de CAST
        if ($crMin !== '') {
            $qb->andWhere('m.cr >= :crMin')
                ->setParameter('crMin', (float) $crMin);
        }

        if ($crMax !== '') {
            $qb->andWhere('m.cr <= :crMax')
                ->setParameter('crMax', (float) $crMax);
        }

        // Apply sorting
        $validSorts = ['name', 'challengeRating', 'type', 'size'];
        if (!in_array($sort, $validSorts)) {
            $sort = 'name';
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        // Mapeia ordenação de 'challengeRating' para a coluna numérica 'cr'
        $sortField = $sort;
        if ($sort === 'challengeRating') {
            $sortField = 'cr';
        }

        $qb->orderBy('m.' . $sortField, $order);

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
            'cr_min' => $crMin,
            'cr_max' => $crMax,
            'status_filter' => $statusFilter,
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
        if ($monster->getImgMain()) {
            $imageUrl = '/' . $monster->getImgMain();
        } else {
            $imageUrl = $imageService->searchImage('monster', $monster->getName());

            if (!$imageUrl) {
                $imageUrl = $imageService->getPlaceholderImage('monster');
            }
        }

        return $this->render('admin/monster/show.html.twig', [
            'monster' => $monster,
            'image_url' => $imageUrl,
        ]);
    }

    #[Route('/{id}/print', name: 'admin_monster_print', methods: ['GET'])]
    public function print(Request $request, Monster $monster, \App\Service\UnsplashImageService $imageService): Response
    {
        if ($monster->getImgMain()) {
            $imageUrl = '/' . $monster->getImgMain();
        } else {
            $imageUrl = $imageService->searchImage('monster', $monster->getName());

            if (!$imageUrl) {
                $imageUrl = $imageService->getPlaceholderImage('monster');
            }
        }

        // Layout Logic System
        // Get layout from query param, default to 'standard' (or 'layout_top_image' if we prefer)
        // User requested buttons for both
        $layout = $request->query->get('layout', 'standard'); 
        
        // For top image layout, use the composed image URL
        if ($layout === 'layout_top_image') {
            $imageUrl = $this->generateUrl('admin_monster_composed_image', ['id' => $monster->getId()]);
        }
        
        // For image-left layout, use the composed image-left URL
        if ($layout === 'layout_image_left') {
            $imageUrl = $this->generateUrl('admin_monster_composed_image_left', ['id' => $monster->getId()]);
        }
        
        $charCount = strlen($monster->getDescriptionMdPt() ?? '') + strlen(json_encode($monster->getSrcJsonPt()));
        
        return $this->render('admin/monster/print.html.twig', [
            'monster' => $monster,
            'image_url' => $imageUrl,
            'layout' => $layout,
            'charCount' => $charCount
        ]);
    }
    
    #[Route('/{id}/composed-image-left', name: 'admin_monster_composed_image_left', methods: ['GET'])]
    public function composedImageLeft(Monster $monster, \App\Service\UnsplashImageService $imageService, \App\Service\MonsterImageComposerService $composerService): Response
    {
        // Get the original monster image
        if ($monster->getImgMain()) {
            $monsterImagePath = '/' . $monster->getImgMain();
        } else {
            $monsterImagePath = $imageService->searchImage('monster', $monster->getName());

            if (!$monsterImagePath) {
                $monsterImagePath = $imageService->getPlaceholderImage('monster');
            }
        }
        
        // Generate composed image path with left layout dimensions (350x600)
        $outputDir = $this->getParameter('kernel.project_dir') . '/public/media/composed';
        $outputFilename = 'monster_' . $monster->getId() . '_layout_image_left.png';
        $outputPath = $outputDir . '/' . $outputFilename;
        
        // Only regenerate if file doesn't exist or is older than 1 hour (cache)
        if (!file_exists($outputPath) || (time() - filemtime($outputPath)) > 3600) {
            // Set dimensions for image-left layout (350x600)
            $composerService->setDimensions(350, 600);
            $composerService->composeImage($monsterImagePath, $outputPath);
        }
        
        // Serve the composed image
        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($outputPath);
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        
        return $response;
    }
    
    #[Route('/{id}/composed-image', name: 'admin_monster_composed_image', methods: ['GET'])]
    public function composedImage(Monster $monster, \App\Service\UnsplashImageService $imageService, \App\Service\MonsterImageComposerService $composerService): Response
    {
        // Get the original monster image
        if ($monster->getImgMain()) {
            $monsterImagePath = '/' . $monster->getImgMain();
        } else {
            $monsterImagePath = $imageService->searchImage('monster', $monster->getName());

            if (!$monsterImagePath) {
                $monsterImagePath = $imageService->getPlaceholderImage('monster');
            }
        }
        
        // Generate composed image path
        $outputDir = $this->getParameter('kernel.project_dir') . '/public/media/composed';
        $outputFilename = 'monster_' . $monster->getId() . '_layout_top_image.png';
        $outputPath = $outputDir . '/' . $outputFilename;
        
        // Only regenerate if file doesn't exist or is older than 1 hour (cache)
        if (!file_exists($outputPath) || (time() - filemtime($outputPath)) > 3600) {
            $composerService->composeImage($monsterImagePath, $outputPath);
        }
        
        // Serve the composed image
        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($outputPath);
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Cache-Control', 'public, max-age=3600');
        
        return $response;
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
