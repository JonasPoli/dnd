<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Form\AttributeType;
use App\Repository\AttributeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/attribute')]
#[IsGranted('ROLE_USER')]
class AttributeController extends AbstractController
{
    #[Route('/', name: 'admin_attribute_index', methods: ['GET'])]
    public function index(AttributeRepository $attributeRepository): Response
    {
        return $this->render('admin/attribute/index.html.twig', [
            'attributes' => $attributeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_attribute_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $attribute = new Attribute();
        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($attribute);
            $entityManager->flush();
            $this->addFlash('success', 'Atributo criado com sucesso!');
            return $this->redirectToRoute('admin_attribute_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/attribute/new.html.twig', [
            'attribute' => $attribute,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_attribute_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Attribute $attribute, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Atributo atualizado com sucesso!');
            return $this->redirectToRoute('admin_attribute_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/attribute/edit.html.twig', [
            'attribute' => $attribute,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_attribute_delete', methods: ['POST'])]
    public function delete(Request $request, Attribute $attribute, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $attribute->getId(), $request->request->get('_token'))) {
            $entityManager->remove($attribute);
            $entityManager->flush();
            $this->addFlash('success', 'Atributo excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_attribute_index', [], Response::HTTP_SEE_OTHER);
    }
}
