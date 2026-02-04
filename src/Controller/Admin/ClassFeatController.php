<?php

namespace App\Controller\Admin;

use App\Entity\ClassDef;
use App\Entity\Feat;
use App\Repository\FeatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/class-feat')]
class ClassFeatController extends AbstractController
{
    #[Route('/{id}', name: 'admin_class_feat_index', methods: ['GET', 'POST'])]
    public function index(ClassDef $classDef, FeatRepository $featRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $featId = $request->request->get('feat');
            $feat = $featRepository->find($featId);

            if ($feat) {
                $classDef->addAvailableFeat($feat);
                $entityManager->flush();
                $this->addFlash('success', 'Talento adicionado com sucesso!');
            }

            return $this->redirectToRoute('admin_class_feat_index', ['id' => $classDef->getId()]);
        }

        return $this->render('admin/class_feat/index.html.twig', [
            'class_def' => $classDef,
            'all_feats' => $featRepository->findBy(['isActive' => true], ['name' => 'ASC']),
        ]);
    }

    #[Route('/{id}/remove/{feat_id}', name: 'admin_class_feat_remove', methods: ['POST'])]
    public function remove(ClassDef $classDef, int $feat_id, FeatRepository $featRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $feat = $featRepository->find($feat_id);

        if ($this->isCsrfTokenValid('delete' . $feat->getId(), $request->request->get('_token'))) {
            $classDef->removeAvailableFeat($feat);
            $entityManager->flush();
            $this->addFlash('success', 'Talento removido com sucesso!');
        }

        return $this->redirectToRoute('admin_class_feat_index', ['id' => $classDef->getId()]);
    }
}
