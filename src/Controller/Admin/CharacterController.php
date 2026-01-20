<?php

namespace App\Controller\Admin;

use App\Entity\Character;
use App\Form\CharacterType;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/character')]
#[IsGranted('ROLE_USER')]
class CharacterController extends AbstractController
{
    #[Route('/', name: 'admin_character_index', methods: ['GET'])]
    public function index(CharacterRepository $characterRepository): Response
    {
        return $this->render('admin/character/index.html.twig', [
            'characters' => $characterRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_character_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $character = new Character();
        $form = $this->createForm(CharacterType::class, $character);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($character);
            $entityManager->flush();
            $this->addFlash('success', 'Personagem criado com sucesso!');
            return $this->redirectToRoute('admin_character_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/character/new.html.twig', [
            'character' => $character,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_character_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Character $character, \App\Repository\FeatureRepository $featureRepository): Response
    {
        $features = $featureRepository->findFeaturesForCharacter($character);

        return $this->render('admin/character/show.html.twig', [
            'character' => $character,
            'features' => $features,
        ]);
    }

    #[Route('/{id}/print', name: 'admin_character_print', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function print(Character $character, \App\Repository\FeatureRepository $featureRepository): Response
    {
        $features = $featureRepository->findFeaturesForCharacter($character);
        $classDef = $character->getClassDef();

        // 1. HP Details Logic
        $hpText = '';
        if ($classDef) {
            $hpText = "**DV:** 1d" . $classDef->getHitDie() . " por nível de " . $classDef->getName() . "\n";
            $hpText .= "**Pontos de Vida no 1º Nível:** " . $classDef->getHpAt1stLevel() . "\n";
            if ($character->getLevel() > 1) {
                $hpText .= "**Pontos de Vida nos Níveis Seguintes:** " . $classDef->getHpAtHigherLevels();
            }
        }

        // 2. Class Table Parsing
        $classTableRows = [];
        $classTableHeader = [];
        if ($classDef && $classDef->getClassTableMd()) {
            $lines = explode("\n", $classDef->getClassTableMd());
            $levelIndex = -1;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Detect Header (starts with | Nível | or similar, but let's assume first non-separator line is header)
                if (str_starts_with($line, '|') && empty($classTableHeader)) {
                     // Check if it's not a separator line convention like |---|
                    if (!str_contains($line, '---')) {
                         $classTableHeader = array_map('trim', array_filter(explode('|', $line), fn($val) => $val !== ''));
                         continue;
                    }
                }
                
                // Skip separator lines
                if (str_contains($line, '---')) continue;

                // Process Rows
                if (str_starts_with($line, '|')) {
                    $cols = array_map('trim', array_filter(explode('|', $line), fn($val) => $val !== ''));
                    // Check first column for level
                    $firstCol = reset($cols);
                    if (is_numeric($firstCol)) {
                        $rowLevel = (int)$firstCol;
                        if ($rowLevel <= $character->getLevel()) {
                            $classTableRows[] = $cols;
                        }
                    }
                }
            }
        }

        // 3. Portuguese Proficiencies
        $proficiencies = [
            'armor' => $classDef?->getArmorTraining()?->label() ?? 'Nenhuma',
            'weapons' => $classDef?->getWeaponProficiencies()?->label() ?? 'Nenhuma',
            'tools' => [],
        ];

        if ($classDef?->getToolProficiency1()) {
            $proficiencies['tools'][] = $classDef->getToolProficiency1()->getNamePt() ?? $classDef->getToolProficiency1()->getName();
        }
        if ($classDef?->getToolProficiency2()) {
            $proficiencies['tools'][] = $classDef->getToolProficiency2()->getNamePt() ?? $classDef->getToolProficiency2()->getName();
        }
        $proficiencies['toolsString'] = empty($proficiencies['tools']) ? 'Nenhuma' : implode(', ', $proficiencies['tools']);


        return $this->render('admin/character/print.html.twig', [
            'character' => $character,
            'features' => $features,
            'hpText' => $hpText,
            'classTableHeaders' => $classTableHeader,
            'classTableRows' => $classTableRows,
            'proficiencies' => $proficiencies,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_character_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Character $character, EntityManagerInterface $entityManager): Response
    {
        // User requested to use the "Step-by-Step" (Wizard) system for editing instead of the monolithic form.
        return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
    }

    #[Route('/{id}', name: 'admin_character_delete', methods: ['POST'])]
    public function delete(Request $request, Character $character, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $character->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($character);
            $entityManager->flush();
            $this->addFlash('success', 'Personagem excluído com sucesso!');
        }

        return $this->redirectToRoute('admin_character_index', [], Response::HTTP_SEE_OTHER);
    }
}
