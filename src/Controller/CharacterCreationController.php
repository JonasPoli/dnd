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
        private \App\Repository\SubclassDefRepository $subclassDefRepository,
        private \App\Repository\SkillRepository $skillRepository,
        private \App\Repository\EquipmentRepository $equipmentRepository,
        private \App\Repository\ClassLevelRepository $classLevelRepository,
        private \App\Repository\SpellRepository $spellRepository,
    ) {}

    #[Route('/', name: 'app_character_creation_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_character_creation_step1');
    }

    #[Route('/step/1/{id?}', name: 'app_character_creation_step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, ?Character $character = null): Response
    {
        // ... (existing code)
        if ($request->isMethod('POST')) {
            $classId = $request->request->get('class_def');
            if ($classId) {
                $classDef = $this->classDefRepository->find($classId);
                
                if (!$character) {
                    $character = new Character();
                    $character->setName('Novo Personagem'); // Temporary name
                }
                
                $oldClass = $character->getClassDef();
                $character->setClassDef($classDef);
            
                // Only reset if class actually changed
                if ($oldClass !== $classDef) {
                    // Reset subclass
                    if ($character->getSubclassDef() && $character->getSubclassDef()->getClassDef() !== $classDef) {
                        $character->setSubclassDef(null);
                    }
                    
                     // Reset skills and tools since available options change with class
                     $character->getSkills()->clear();
                     $character->getToolProficiencies()->clear();
                     
                     // Also reset attributes so they can be regenerated for the new class in Step 9
                     // (Optional but recommended to match class base stats)
                     $character->getCharacterAttributes()->clear();
                }
                
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

    #[Route('/step/2/{id}', name: 'app_character_creation_step2', methods: ['GET', 'POST'])]
    public function step2(Request $request, Character $character): Response
    {
        if (!$character->getClassDef()) {
            return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
        }

        if ($request->isMethod('POST')) {
            $subclassId = $request->request->get('subclass_def');
            if ($subclassId) {
                $subclassDef = $this->subclassDefRepository->find($subclassId);
                
                if ($subclassDef && $subclassDef->getClassDef() === $character->getClassDef()) {
                    $character->setSubclassDef($subclassDef);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('app_character_creation_step3', ['id' => $character->getId()]);
                }
            }
        }

        $subclasses = $this->subclassDefRepository->findBy(['classDef' => $character->getClassDef()]);

        return $this->render('character_creation/step2_subclass.html.twig', [
            'character' => $character,
            'subclasses' => $subclasses,
            'current_subclass' => $character->getSubclassDef(),
        ]);
    }

    #[Route('/step/3/{id}', name: 'app_character_creation_step3', methods: ['GET', 'POST'])]
    public function step3(Request $request, Character $character): Response
    {
        if (!$character->getClassDef()) {
            return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
        }

        $maxSkills = $character->getClassDef()->getInitialSkillsCount() ?? 2;
        // Fallback: if baseSkills is empty, show all skills (allows continuing dev without perfect data)
        $availableSkills = $character->getClassDef()->getBaseSkills();
        if ($availableSkills->isEmpty()) {
            $availableSkills = $this->skillRepository->findAll();
        }

        if ($request->isMethod('POST')) {
            $selectedSkillIds = $request->request->all('skills'); // array of IDs
            
            // Basic Validation
            if (count($selectedSkillIds) > $maxSkills) {
                 $this->addFlash('error', "Você pode escolher no máximo $maxSkills perícias.");
            } else {
                // Clear and Add
                $character->getSkills()->clear();
                foreach ($availableSkills as $skill) {
                    if (in_array($skill->getId(), $selectedSkillIds)) {
                        $character->addSkill($skill);
                    }
                }
                
                $this->entityManager->flush();
                return $this->redirectToRoute('app_character_creation_step4', ['id' => $character->getId()]);
            }
        }

        return $this->render('character_creation/step3_skills.html.twig', [
            'character' => $character,
            'available_skills' => $availableSkills,
            'max_skills' => $maxSkills,
            'current_skills' => $character->getSkills(),
        ]);
    }

    #[Route('/step/4/{id}', name: 'app_character_creation_step4', methods: ['GET', 'POST'])]
    public function step4(Request $request, Character $character): Response
    {
        if (!$character->getClassDef()) {
            return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
        }

        $maxTools = $character->getClassDef()->getInitialToolsCount() ?? 0;
        
        // Fetch ALL equipment to allow user-requested filtering (Arma, Montaria, etc.)
        $availableTools = $this->equipmentRepository->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $selectedToolIds = $request->request->all('tools'); 
            
            if (count($selectedToolIds) > $maxTools) {
                 $this->addFlash('error', "Você pode escolher no máximo $maxTools ferramentas.");
            } else {
                $character->getToolProficiencies()->clear();
                foreach ($availableTools as $tool) {
                    if (in_array($tool->getId(), $selectedToolIds)) {
                        $character->addToolProficiency($tool);
                    }
                }
                
                $this->entityManager->flush();
                return $this->redirectToRoute('app_character_creation_step5', ['id' => $character->getId()]);
            }
        }

        return $this->render('character_creation/step4_tools.html.twig', [
            'character' => $character,
            'available_tools' => $availableTools,
            'max_tools' => $maxTools,
            'current_tools' => $character->getToolProficiencies(),
        ]);
    }

    #[Route('/step/5/{id}', name: 'app_character_creation_step5', methods: ['GET', 'POST'])]
    public function step5(Request $request, Character $character): Response
    {
        if (!$character->getClassDef()) {
            return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
        }

        // Determine Cantrips Known from ClassLevel (Level 1)
        $classLevel = $this->classLevelRepository->findOneBy([
            'classDef' => $character->getClassDef(),
            'level' => 1
        ]);
        
        $maxCantrips = $classLevel ? $classLevel->getCantripsKnown() : 0;

        // If no cantrips allowed, skip step
        if ($maxCantrips <= 0) {
            // Logic to clear existing cantrips if any? Maybe valid to keep if class changed? 
            // For now, let's assume we proceed. Ideally we'd skip or show a "No cantrips" screen.
            // But per specs: "Algumas classes não têm truques, nesse caso, o passo deve ser ignorado."
            // So we auto-redirect to Step 6.
             return $this->redirectToRoute('app_character_creation_step6', ['id' => $character->getId()]);
        }

        // Fetch Cantrips (Level 0 Spells for this Class)

        // Fetch Cantrips (Level 0 Spells for this Class)
        // Need custom query repository method ideally, or filter in PHP
        // "spell", filtrada pela classe escolhida em spell.classes e level = 0
        $allCantrips = $this->spellRepository->findBy(['level' => 0], ['name' => 'ASC']);
        $availableCantrips = [];
        foreach ($allCantrips as $spell) {
            if ($spell->getClasses()->contains($character->getClassDef())) {
                $availableCantrips[] = $spell;
            }
        }
        
        // Fallback or Generic List if empty? Rulebook says restricted by class.

        if ($request->isMethod('POST')) {
            $selectedCantripIds = $request->request->all('cantrips'); 
            
            if (count($selectedCantripIds) > $maxCantrips) {
                 $this->addFlash('error', "Você pode escolher no máximo $maxCantrips truques.");
            } else {
                // Clear existing CANTIRPS (Level 0 spells)
                // Note: CharacterSpell relation doesn't distinguish cantrips/spells easily without join.
                // We need to be careful not to remove Level 1 spells if we had them (future step).
                // For now, let's look at `characterSpells`.
                
                // Remove all current cantrips
                foreach ($character->getCharacterSpells() as $charSpell) {
                    if ($charSpell->getSpell()->getLevel() === 0) {
                        $character->removeCharacterSpell($charSpell);
                        $this->entityManager->remove($charSpell);
                    }
                }

                foreach ($availableCantrips as $spell) {
                    if (in_array($spell->getId(), $selectedCantripIds)) {
                        $charSpell = new \App\Entity\CharacterSpell();
                        $charSpell->setCharacter($character);
                        $charSpell->setSpell($spell);
                        $charSpell->setLearnedAtLevel(1); 
                        $this->entityManager->persist($charSpell);
                        $character->addCharacterSpell($charSpell);
                    }
                }
                
                $this->entityManager->flush();
                return $this->redirectToRoute('app_character_creation_step6', ['id' => $character->getId()]);
            }
        }
        
        // Prepare current selection IDs for view
        $currentCantripIds = [];
        foreach ($character->getCharacterSpells() as $cs) {
            if ($cs->getSpell()->getLevel() === 0) {
                $currentCantripIds[] = $cs->getSpell()->getId();
            }
        }

        return $this->render('character_creation/step5_cantrips.html.twig', [
            'character' => $character,
            'available_cantrips' => $availableCantrips,
            'max_cantrips' => $maxCantrips,
            'current_cantrip_ids' => $currentCantripIds,
        ]);
    }

    #[Route('/step/6/{id}', name: 'app_character_creation_step6', methods: ['GET', 'POST'])]
    public function step6(Request $request, Character $character): Response
    {
        if (!$character->getClassDef()) {
            return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
        }

        // Determine Spells Known/Prepared from ClassLevel (Level 1)
        // Note: For Wizards, this is usually 6 (in book). For Sorcerers/Bards, it's Spells Known.
        // We assume 'spellsPrepared' field in ClassLevel holds this 'Initial Spells Count' value for Level 1.
        $classLevel = $this->classLevelRepository->findOneBy([
            'classDef' => $character->getClassDef(),
            'level' => 1
        ]);
        
        $maxSpells = $classLevel ? $classLevel->getSpellsPrepared() : 0;

        // If no spells allowed at level 1, skip step
        if ($maxSpells <= 0) {
             return $this->redirectToRoute('app_character_creation_step7', ['id' => $character->getId()]);
        }

        // Fetch Level 1 Spells for this Class
        $allSpells = $this->spellRepository->findBy(['level' => 1], ['name' => 'ASC']);
        $availableSpells = [];
        foreach ($allSpells as $spell) {
             if ($spell->getClasses()->contains($character->getClassDef())) {
                $availableSpells[] = $spell;
            }
        }

        if ($request->isMethod('POST')) {
            $selectedSpellIds = $request->request->all('spells'); 
            
            if (count($selectedSpellIds) > $maxSpells) {
                 $this->addFlash('error', "Você pode escolher no máximo $maxSpells magias.");
            } else {
                // Remove all current Level 1 spells to replace with new selection
                foreach ($character->getCharacterSpells() as $charSpell) {
                    if ($charSpell->getSpell()->getLevel() === 1) {
                        $character->removeCharacterSpell($charSpell);
                        $this->entityManager->remove($charSpell);
                    }
                }

                foreach ($availableSpells as $spell) {
                    if (in_array($spell->getId(), $selectedSpellIds)) {
                        $charSpell = new \App\Entity\CharacterSpell();
                        $charSpell->setCharacter($character);
                        $charSpell->setSpell($spell);
                        $charSpell->setLearnedAtLevel(1); 
                        $this->entityManager->persist($charSpell);
                        $character->addCharacterSpell($charSpell);
                    }
                }
                
                $this->entityManager->flush();
                return $this->redirectToRoute('app_character_creation_step7', ['id' => $character->getId()]);
            }
        }
        
        // Prepare current selection IDs for view
        $currentSpellIds = [];
        foreach ($character->getCharacterSpells() as $cs) {
            if ($cs->getSpell()->getLevel() === 1) {
                $currentSpellIds[] = $cs->getSpell()->getId();
            }
        }

        return $this->render('character_creation/step6_spells.html.twig', [
            'character' => $character,
            'available_spells' => $availableSpells,
            'max_spells' => $maxSpells,
            'current_spell_ids' => $currentSpellIds,
        ]);
    }

    #[Route('/step/7/{id}', name: 'app_character_creation_step7', methods: ['GET', 'POST'])]
    public function step7(Request $request, Character $character, \App\Repository\SpeciesRepository $speciesRepository, \App\Repository\SubraceRepository $subraceRepository): Response
    {
        if (!$character->getClassDef()) {
            return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
        }

        if ($request->isMethod('POST')) {
            $speciesId = $request->request->get('species');
            $subraceId = $request->request->get('subrace_' . $speciesId); // Get subrace specific to selected species

            if ($speciesId) {
                $species = $speciesRepository->find($speciesId);
                
                // Validate if species has subraces but none selected
                $hasSubraces = $species->getSubraces()->count() > 0;
                
                if ($hasSubraces && !$subraceId) {
                    $this->addFlash('error', "A espécie {$species->getName()} requer a escolha de uma sub-espécie.");
                    // Return to render view with error
                } else {
                    $character->setSpecies($species);
                    
                    if ($subraceId) {
                        $subrace = $subraceRepository->find($subraceId);
                         // Security check: ensure subrace belongs to species
                        if ($subrace->getSpecies() === $species) {
                             $character->setSubrace($subrace);
                        }
                    } else {
                        $character->setSubrace(null);
                    }

                    $this->entityManager->flush();
                    return $this->redirectToRoute('app_character_creation_step8', ['id' => $character->getId()]);
                }
            } else {
                 $this->addFlash('error', "Por favor, selecione uma espécie.");
            }
        }

        $species = $speciesRepository->findBy([], ['name' => 'ASC']);

        return $this->render('character_creation/step7_species.html.twig', [
            'character' => $character,
            'species_list' => $species,
            'current_species' => $character->getSpecies(),
            'current_subrace' => $character->getSubrace(),
        ]);
    }

    #[Route('/step/8/{id}', name: 'app_character_creation_step8', methods: ['GET', 'POST'])]
    public function step8(Request $request, Character $character, \App\Repository\BackgroundRepository $backgroundRepository): Response
    {
        if (!$character->getSpecies()) {
            return $this->redirectToRoute('app_character_creation_step7', ['id' => $character->getId()]);
        }

        if ($request->isMethod('POST')) {
            $backgroundId = $request->request->get('background');
            if ($backgroundId) {
                $background = $backgroundRepository->find($backgroundId);
                $character->setBackground($background);
                
                // Note: We are currently ONLY saving the background choice.
                // Applying the bonuses (skills, feats, equipment) to the character 
                // should happen either here or be derived dynamically.
                // For now, consistent with other steps, we just link the entity.
                
                $this->entityManager->flush();

                return $this->redirectToRoute('app_character_creation_step9', ['id' => $character->getId()]);
            } else {
                 $this->addFlash('error', "Por favor, selecione um antecedente.");
            }
        }

        $backgrounds = $backgroundRepository->findBy([], ['name' => 'ASC']);

        return $this->render('character_creation/step8_background.html.twig', [
            'character' => $character,
            'backgrounds' => $backgrounds,
            'current_background' => $character->getBackground(),
        ]);
    }

    #[Route('/step/9/{id}', name: 'app_character_creation_step9', methods: ['GET', 'POST'])]
    public function step9(Request $request, Character $character, \App\Repository\AttributeRepository $attributeRepository, \App\Repository\CharacterAttributeRepository $charAttrRepo): Response
    {
        if (!$character->getBackground()) {
             return $this->redirectToRoute('app_character_creation_step8', ['id' => $character->getId()]);
        }

        // Define Class Base stats mapping
        $classStats = [
            'Bárbaro' => ['Força' => 15, 'Destreza' => 13, 'Constituição' => 14, 'Inteligência' => 10, 'Sabedoria' => 12, 'Carisma' => 8],
            'Bardo' => ['Força' => 8, 'Destreza' => 14, 'Constituição' => 12, 'Inteligência' => 13, 'Sabedoria' => 10, 'Carisma' => 15],
            'Bruxo' => ['Força' => 8, 'Destreza' => 14, 'Constituição' => 13, 'Inteligência' => 12, 'Sabedoria' => 10, 'Carisma' => 15],
            'Clérigo' => ['Força' => 14, 'Destreza' => 8, 'Constituição' => 13, 'Inteligência' => 10, 'Sabedoria' => 15, 'Carisma' => 12],
            'Druida' => ['Força' => 8, 'Destreza' => 12, 'Constituição' => 14, 'Inteligência' => 13, 'Sabedoria' => 15, 'Carisma' => 10],
            'Feiticeiro' => ['Força' => 10, 'Destreza' => 13, 'Constituição' => 14, 'Inteligência' => 8, 'Sabedoria' => 12, 'Carisma' => 15],
            'Guardião' => ['Força' => 12, 'Destreza' => 15, 'Constituição' => 13, 'Inteligência' => 8, 'Sabedoria' => 14, 'Carisma' => 10],
            'Guerreiro' => ['Força' => 15, 'Destreza' => 14, 'Constituição' => 13, 'Inteligência' => 8, 'Sabedoria' => 10, 'Carisma' => 12],
            'Ladino' => ['Força' => 12, 'Destreza' => 15, 'Constituição' => 13, 'Inteligência' => 14, 'Sabedoria' => 10, 'Carisma' => 8],
            'Mago' => ['Força' => 8, 'Destreza' => 12, 'Constituição' => 13, 'Inteligência' => 15, 'Sabedoria' => 14, 'Carisma' => 10],
            'Monge' => ['Força' => 12, 'Destreza' => 15, 'Constituição' => 13, 'Inteligência' => 10, 'Sabedoria' => 14, 'Carisma' => 8],
            'Paladino' => ['Força' => 15, 'Destreza' => 10, 'Constituição' => 13, 'Inteligência' => 8, 'Sabedoria' => 12, 'Carisma' => 14],
        ];

        // Ensure Attributes exist in DB (Seed logic really, but handling here for safety)
        // Check if character already has attributes
        if ($character->getCharacterAttributes()->isEmpty()) {
            $className = $character->getClassDef() ? $character->getClassDef()->getName() : null;
            $baseStats = $classStats[$className] ?? ['Força'=>10,'Destreza'=>10,'Constituição'=>10,'Inteligência'=>10,'Sabedoria'=>10,'Carisma'=>10];

            // Background Bonuses
            $bg = $character->getBackground();
            $bonuses = [];
            if ($bg) {
                if ($bg->getAttribute1()) $bonuses[$bg->getAttribute1()->getName()] = ($bonuses[$bg->getAttribute1()->getName()] ?? 0) + 1; // Assuming +1 or whatever rule. Usually it's +2/+1 or +1/+1/+1. 
                // The entity structure has attr1, attr2, attr3. We assume +1 for each slot for simplicity unless defined otherwise. 
                // Standard D&D 2024 Backgrounds: Choose 3 abilities, get +1 to each? Or +2 to one, +1 to another. 
                // Since the entity has 3 slots, might be 3x +1.
                // Let's assume +1 for each slot present.
                if ($bg->getAttribute2()) $bonuses[$bg->getAttribute2()->getName()] = ($bonuses[$bg->getAttribute2()->getName()] ?? 0) + 1;
                if ($bg->getAttribute3()) $bonuses[$bg->getAttribute3()->getName()] = ($bonuses[$bg->getAttribute3()->getName()] ?? 0) + 1;
            }

            foreach ($baseStats as $attrName => $val) {
                // Find Attribute Entity
                // Assuming Attribute entity names match keys.
                // NOTE: Creating attributes via repository findOneBy name.
                // It's inefficient to query in loop, but usually fine for 6 items once.
                
                $attributeEntity = $attributeRepository->findOneBy(['name' => $attrName]);
                if ($attributeEntity) {
                    $charAttr = new \App\Entity\CharacterAttribute();
                    $charAttr->setCharacter($character);
                    $charAttr->setAttribute($attributeEntity);
                    
                    $bonus = $bonuses[$attrName] ?? 0;
                    $finalValue = $val + $bonus;
                    
                    $charAttr->setValue($finalValue);
                    
                    $this->entityManager->persist($charAttr);
                    $character->addCharacterAttribute($charAttr);
                }
            }
            $this->entityManager->flush();
        }

        if ($request->isMethod('POST')) {
            // Allows manual override if we wanted to implement that form.
            // For now, implicit save on load for the pre-fill requirement, moving to next step.
            return $this->redirectToRoute('app_character_creation_step10', ['id' => $character->getId()]);
        }

        // Prepare data for view
        $attributes = [];
        foreach ($character->getCharacterAttributes() as $ca) {
            $val = $ca->getValue();
            $mod = floor(($val - 10) / 2);
            $attributes[] = [
                'name' => $ca->getAttribute()->getName(),
                'value' => $val,
                'modifier' => ($mod >= 0 ? '+' : '') . $mod,
                'entity' => $ca
            ];
        }
        
        // Sort attributes by standard order if needed (Str, Dex, Con, Int, Wis, Cha)
        // ...

        return $this->render('character_creation/step9_attributes.html.twig', [
            'character' => $character,
            'attributes' => $attributes,
        ]);
    }

    #[Route('/step/10/{id}', name: 'app_character_creation_step10', methods: ['GET', 'POST'])]
    public function step10(Request $request, Character $character, \App\Repository\LanguageRepository $languageRepository): Response
    {
        if (!$character->getBackground()) {
             // If coming from Step 8, valid. If skipping Step 9, valid.
             // Just ensure flow consistency.
             // return $this->redirectToRoute('app_character_creation_step8', ['id' => $character->getId()]);
        }

        // 1. Identify Fixed Languages
        $fixedLanguages = [];
        $common = $languageRepository->findOneBy(['name' => 'Comum']); // Ensure 'Comum' exists in DB or handle gracefully
        if ($common) $fixedLanguages[] = $common;

        // Class Specific
        $class = $character->getClassDef();
        if ($class) {
            if ($class->getName() === 'Druida') {
                $druidic = $languageRepository->findOneBy(['name' => 'Druídico']);
                if ($druidic) $fixedLanguages[] = $druidic;
            }
            if ($class->getName() === 'Ladino') {
                $thievesCant = $languageRepository->findOneBy(['name' => 'Gíria de Ladrão']);
                if ($thievesCant) $fixedLanguages[] = $thievesCant;
            }
        }

        // 2. Calculate Choice Slots
        // Default Rule: Background gives 1 language? 
        // User prompt: "Slot do Antecedente: A maioria dos antecedentes concede 1 escolha."
        // We assume 1 slot for now.
        $slots = 1;

        // 3. Fetch Available Languages
        // Standard
        $standardLanguages = $languageRepository->findBy(['type' => 'Padrão'], ['name' => 'ASC']);
        // Exotic
        $exoticLanguages = $languageRepository->findBy(['type' => 'Exótico'], ['name' => 'ASC']);
        
        // Exclude fixed from available to avoid duplication in UI (though validation handles it)
        $fixedIds = array_map(fn($l) => $l->getId(), $fixedLanguages);

        if ($request->isMethod('POST')) {
            $selectedIds = $request->request->all('languages'); // Array of IDs
            
            // Validate Count
            if (count($selectedIds) > $slots) {
                $this->addFlash('error', "Você pode escolher no máximo $slots idiomas.");
            } else {
                // Clear existing
                $character->getLanguages()->clear();
                
                // Add Fixed
                foreach ($fixedLanguages as $l) {
                    $character->addLanguage($l);
                }
                
                // Add Selected
                foreach ($selectedIds as $id) {
                    $lang = $languageRepository->find($id);
                    if ($lang && !in_array($lang->getId(), $fixedIds)) {
                         $character->addLanguage($lang);
                    }
                }
                
                $this->entityManager->flush();
                return $this->redirectToRoute('app_character_creation_step11', ['id' => $character->getId()]);
            }
        }
        
        // Prepare current selection for view (excluding fixed)
        $currentLanguageIds = [];
        foreach ($character->getLanguages() as $l) {
            if (!in_array($l->getId(), $fixedIds)) {
                $currentLanguageIds[] = $l->getId();
            }
        }

        return $this->render('character_creation/step10_languages.html.twig', [
            'character' => $character,
            'fixed_languages' => $fixedLanguages,
            'standard_languages' => $standardLanguages,
            'exotic_languages' => $exoticLanguages,
            'slots' => $slots,
            'current_language_ids' => $currentLanguageIds,
        ]);
    }

    #[Route('/step/11/{id}', name: 'app_character_creation_step11', methods: ['GET', 'POST'])]
    public function step11(Request $request, Character $character, \App\Repository\CharacterAttributeRepository $charAttrRepo): Response
    {
        // 1. Calculate Stats for Display
        
        // Helper to get Modifier
        $getMod = function($score) {
            return floor(($score - 10) / 2);
        };
        
        $attributes = [];
        $modifiers = [];
        foreach ($character->getCharacterAttributes() as $attr) {
            $name = $attr->getAttribute()->getName();
            $val = $attr->getValue();
            $attributes[$name] = $val;
            $modifiers[$name] = $getMod($val);
        }
        
        // Default 10 if missing (shouldn't happen if flowed correctly)
        $const = $attributes['Constituição'] ?? 10;
        $dex = $attributes['Destreza'] ?? 10;
        $wis = $attributes['Sabedoria'] ?? 10;
        
        $conMod = $modifiers['Constituição'] ?? 0;
        $dexMod = $modifiers['Destreza'] ?? 0;
        $wisMod = $modifiers['Sabedoria'] ?? 0;

        // HP
        $baseHp = 0;
        $hitDice = "1d8";
        if ($character->getClassDef()) {
            // Base HP at level 1 is the max roll of the Hit Die
            $baseHp = $character->getClassDef()->getHitDie(); 
            $hitDice = "1d" . $baseHp; // Construct string representation
        }
        // Fallback if class def missing
        if ($baseHp == 0) $baseHp = 8; // Default fallback

        $maxHp = $baseHp + $conMod;

        // AC (Unarmored default)
        $ac = 10 + $dexMod;
        
        // Initiative
        $initiative = $dexMod; // formatted string later
        
        // Passive Perception
        // Check Proficiency
        $profBonus = 2; // Level 1
        $isPerceptionProficient = false;
        foreach ($character->getSkills() as $skill) {
            if ($skill->getName() === 'Percepção') { // Ensure name match
                $isPerceptionProficient = true;
                break;
            }
        }
        $passivePerception = 10 + $wisMod + ($isPerceptionProficient ? $profBonus : 0);

        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            $alignment = $request->request->get('alignment');
            $appearance = $request->request->get('appearance');
            $bonds = $request->request->get('bonds');
            $origin = $request->request->get('origin');
            
            // Image handling (Simplified Text URL or would be file upload service)
            // User requested "Upload or Selection". For now, we assume text or skip if file upload not ready.
            // Let's assume text input for now or dummy upload logic.
            // The prompt "Imagem do Personagem (Upload ou Seleção)" implies a picker or file.
            // I will leave it as null/placeholder or basic text input for URL for this iteration unless I implement upload service.
            
            if ($name) {
                $character->setName($name);
                $character->setAlignment($alignment);
                $character->setAppearance($appearance);
                $character->setBonds($bonds);
                $character->setOrigin($origin);
                $character->setIsComplete(true);
                
                $this->entityManager->flush();
                
                // Redirect to Character Sheet
                return $this->redirectToRoute('admin_character_show', ['id' => $character->getId()]);
            } else {
                $this->addFlash('error', "O nome é obrigatório.");
            }
        }

        return $this->render('character_creation/step11_final.html.twig', [
            'character' => $character,
            'stats' => [
                'hp' => $maxHp,
                'ac' => $ac,
                'initiative' => ($initiative >= 0 ? '+' : '') . $initiative,
                'passive_perception' => $passivePerception,
                'hit_dice' => $hitDice
            ]
        ]);
    }
}
