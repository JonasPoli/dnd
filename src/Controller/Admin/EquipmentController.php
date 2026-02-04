<?php

namespace App\Controller\Admin;

use App\Entity\Equipment;
use App\Form\EquipmentType;
use App\Repository\EquipmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/equipment')]
#[IsGranted('ROLE_USER')]
class EquipmentController extends AbstractController
{
    #[Route('/', name: 'admin_equipment_index', methods: ['GET'])]
    public function index(Request $request, EquipmentRepository $equipmentRepository): Response
    {
        $statusFilter = $request->query->get('status', 'active');
        $qb = $equipmentRepository->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC');

        if ($statusFilter === 'active') {
            $qb->andWhere('e.isActive = :active')
                ->setParameter('active', true);
        } elseif ($statusFilter === 'inactive') {
            $qb->andWhere('e.isActive = :active')
                ->setParameter('active', false);
        }

        return $this->render('admin/equipment/index.html.twig', [
            'equipment_list' => $qb->getQuery()->getResult(),
            'status_filter' => $statusFilter,
        ]);
    }

    #[Route('/new', name: 'admin_equipment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipment = new Equipment();
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipment);
            $entityManager->flush();
            $this->addFlash('success', 'Equipamento criado com sucesso!');
            return $this->redirectToRoute('admin_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/equipment/new.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_equipment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipmentType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Equipamento atualizado com sucesso!');
            return $this->redirectToRoute('admin_equipment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/equipment/edit.html.twig', [
            'equipment' => $equipment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_equipment_delete', methods: ['POST'])]
    public function delete(Request $request, Equipment $equipment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $equipment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipment);
            $entityManager->flush();
            $this->addFlash('success', 'Equipamento excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_equipment_index', [], Response::HTTP_SEE_OTHER);
    }
}
