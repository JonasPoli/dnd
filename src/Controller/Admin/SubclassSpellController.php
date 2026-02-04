<?php

namespace App\Controller\Admin;

use App\Entity\SubclassDef;
use App\Entity\SubclassSpell;
use App\Form\SubclassSpellType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/subclass-spell')]
#[IsGranted('ROLE_USER')]
class SubclassSpellController extends AbstractController
{
    #[Route('/{id}/new', name: 'admin_subclass_spell_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SubclassDef $subclassDef, EntityManagerInterface $entityManager): Response
    {
        $subclassSpell = new SubclassSpell();
        $subclassSpell->setSubclassDef($subclassDef);
        
        $form = $this->createForm(SubclassSpellType::class, $subclassSpell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subclassSpell);
            $entityManager->flush();

            $this->addFlash('success', 'Magia adicionada à subclasse com sucesso!');

            // Redirect back to new form to allow adding more spells, or to the subclass edit page?
            // User requirement: "list atual das magias já adicionadas" -> suggests staying here or cleaner flow.
            // Let's redirect to this same page to allow quick addition of another spell.
            return $this->redirectToRoute('admin_subclass_spell_new', ['id' => $subclassDef->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/subclass_spell/new.html.twig', [
            'subclass' => $subclassDef,
            'subclass_spell' => $subclassSpell,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_subclass_spell_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SubclassSpell $subclassSpell, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubclassSpellType::class, $subclassSpell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Magia da subclasse atualizada com sucesso!');

            return $this->redirectToRoute('admin_subclass_edit', ['id' => $subclassSpell->getSubclassDef()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/subclass_spell/edit.html.twig', [
            'subclass' => $subclassSpell->getSubclassDef(),
            'subclass_spell' => $subclassSpell,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_subclass_spell_delete', methods: ['POST'])]
    public function delete(Request $request, SubclassSpell $subclassSpell, EntityManagerInterface $entityManager): Response
    {
        $subclassId = $subclassSpell->getSubclassDef()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$subclassSpell->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subclassSpell);
            $entityManager->flush();
            $this->addFlash('success', 'Magia removida da subclasse.');
        }

        return $this->redirectToRoute('admin_subclass_edit', ['id' => $subclassId], Response::HTTP_SEE_OTHER);
    }
}
