<?php

namespace App\Controller;

use App\Entity\Character;
use App\Entity\ClassDef;
use App\Repository\ClassDefRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/character-creation')]
class CharacterCreationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ClassDefRepository $classDefRepository,
    ) {}

    #[Route('/', name: 'app_character_creation_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_character_creation_step1');
    }

    #[Route('/step/1/{id?}', name: 'app_character_creation_step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, ?Character $character = null): Response
    {
        if ($request->isMethod('POST')) {
            $classId = $request->request->get('class_def');
            if ($classId) {
                $classDef = $this->classDefRepository->find($classId);
                
                if (!$character) {
                    $character = new Character();
                    $character->setName('Novo Personagem'); // Temporary name
                }
                
                $character->setClassDef($classDef);
                // Reset subsequent choices if class changes? For now, keep it simple.
                
                $this->entityManager->persist($character);
                $this->entityManager->flush();

                return $this->redirectToRoute('app_character_creation_step2', ['id' => $character->getId()]);
            }
        }

        $classes = $this->classDefRepository->findAll();

        return $this->render('character_creation/step1_class.html.twig', [
            'character' => $character,
            'classes' => $classes,
            'current_class' => $character?->getClassDef(),
        ]);
    }

    #[Route('/step/2/{id}', name: 'app_character_creation_step2')]
    public function step2(Character $character): Response
    {
        return $this->render('character_creation/step2_subclass.html.twig', [
            'character' => $character,
        ]);
    }
}
