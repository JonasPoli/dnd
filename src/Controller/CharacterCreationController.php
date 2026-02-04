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
        private \App\Repository\TrinketRepository $trinketRepository,
    ) {}

    #[Route('/', name: 'app_character_creation_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_character_creation_step1');
    }

    #[Route('/step/1/{id?}', name: 'app_character_creation_step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, ?Character $character = null): Response
    {
        if ($character && $character->isComplete()) {
             // Optional: redirect validation
        }

        if ($request->isMethod('POST')) {
            $classId = $request->request->get('class_def');
            $trinketId = $request->request->get('trinket_id');

            if ($classId) {
                $classDef = $this->classDefRepository->find($classId);
                
                if (!$character) {
                    $character = new Character();
                    $character->setName('Novo Personagem'); // Temporary name
                }
                
                $oldClass = $character->getClassDef();
                $character->setClassDef($classDef);
            
                // Handle Trinket
                if ($trinketId) {
                     $trinket = $this->trinketRepository->find($trinketId);
                     if ($trinket) {
                         $character->setTrinket($trinket);
                     }
                }

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
        $trinkets = $this->trinketRepository->findAll();

        return $this->render('character_creation/step1_class.html.twig', [
            'character' => $character,
            'classes' => $classes,
            'trinkets' => $trinkets,
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
        $availableTools = $this->equipmentRepository->findBy(['isActive' => true], ['name' => 'ASC']);

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

        // If no cantrips allowed by class, we still allow access (e.g. for Feats)
        // Set a "soft limit" or just a high number if it's 0, or keep it 0 but visually handle it?
        // User wants to *choose*. If limit is 0, the JS blocks it.
        // So we set a "Feat Allowance" default, e.g. 5.
        $isClassCaster = ($maxCantrips > 0);
        if ($maxCantrips <= 0) {
            $maxCantrips = 5; // Allow selecting up to 5 for non-casters (arbitrary safety limit)
        }

        // Fetch Cantrips (Level 0 Spells)
        // If class iscaster, filter by class. If not (Fighter), maybe show ALL or specific list?
        // Usually Magic Initiate lets you pick from a specific list (Bard, Cleric, Druid, Sorcerer, Warlock, Wizard).
        // For simplicity now, if not a caster class, we might show ALL or just Wizard/Cleric/Druid?
        // Let's show ALL level 0 spells if the class doesn't restrict.
        
        $allCantrips = $this->spellRepository->findBy(['level' => 0, 'isActive' => true], ['name' => 'ASC']);
        $availableCantrips = [];
        
        if ($isClassCaster) {
             foreach ($allCantrips as $spell) {
                if ($spell->getClasses()->contains($character->getClassDef())) {
                    $availableCantrips[] = $spell;
                }
            }
        } else {
            // Non-caster: Show all (or maybe filter by Magic Initiate common classes later)
            $availableCantrips = $allCantrips;
        }

        if ($request->isMethod('POST')) {
            $selectedCantripIds = $request->request->all('cantrips'); 
            
            // Validate? If non-caster, we might not strictly validate "max" via backend as rigidly
            // or we use the fallback limit.
            if (count($selectedCantripIds) > $maxCantrips) {
                 $this->addFlash('error', "Você pode escolher no máximo $maxCantrips truques.");
            } else {
                // Remove all current cantrips
                foreach ($character->getCharacterSpells() as $charSpell) {
                    if ($charSpell->getSpell()->getLevel() === 0) {
                        $character->removeCharacterSpell($charSpell);
                        $this->entityManager->remove($charSpell);
                    }
                }

                foreach ($allCantrips as $spell) { // Iterate all to find selected
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
            'is_class_caster' => $isClassCaster, // Pass flag to view
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

        // If no spells allowed at level 1, normally skip. 
        // But user requests ability to select for Feats.
        $isClassCaster = ($maxSpells > 0);
        if ($maxSpells <= 0) {
             $maxSpells = 5; // Fallback "Feat Allowance"
        }

        // Fetch Level 1 Spells
        // If isClassCaster, filter by class. Else show all?
        $allSpells = $this->spellRepository->findBy(['level' => 1, 'isActive' => true], ['name' => 'ASC']);
        $availableSpells = [];

        if ($isClassCaster) {
            foreach ($allSpells as $spell) {
                 if ($spell->getClasses()->contains($character->getClassDef())) {
                    $availableSpells[] = $spell;
                }
            }
        } else {
            // Show all for non-casters picking feats
            $availableSpells = $allSpells;
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

                foreach ($allSpells as $spell) {
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

        // Determine Prev Route
        // Step 5 (Cantrips) is now ALWAYS available technically, but maybe we link there always?
        // Or we check if user VISITED it?
        // Actually, since we unblocked Step 5, it's always the previous step unless we re-introduce logic.
        // But wait, if we unblock everything, the linear flow is Step 4 -> Step 5 -> Step 6.
        // So Step 6's prev is ALWAYS Step 5.
        $prevRoute = 'app_character_creation_step5';

        return $this->render('character_creation/step6_spells.html.twig', [
            'character' => $character,
            'available_spells' => $availableSpells,
            'max_spells' => $maxSpells,
            'current_spell_ids' => $currentSpellIds,
            'prev_route' => $prevRoute
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
                    return $this->redirectToRoute('app_character_creation_step7b', ['id' => $character->getId()]);
                }
            } else {
                 $this->addFlash('error', "Por favor, selecione uma espécie.");
            }
        }

        $species = $speciesRepository->findBy([], ['name' => 'ASC']);

        // Determine Previous Route (Back Button Logic)
        // Now that Step 5 (Cantrips) and Step 6 (Spells) are always accessible (for Feat support),
        // the previous step from 7 is always 6.
        $prevRoute = 'app_character_creation_step6'; 
        
        /* 
        Legacy Check (Removed):
        $classLevel = $character->getClassDef() ? $this->classLevelRepository->findOneBy(['classDef' => $character->getClassDef(), 'level' => 1]) : null;
        if ($classLevel) {
            if (($classLevel->getSpellsPrepared() ?? 0) > 0) {
                $prevRoute = 'app_character_creation_step6';
            } elseif (($classLevel->getCantripsKnown() ?? 0) > 0) {
                $prevRoute = 'app_character_creation_step5';
            }
        }
        */

        return $this->render('character_creation/step7_species.html.twig', [
            'character' => $character,
            'species_list' => $species,
            'current_species' => $character->getSpecies(),
            'current_subrace' => $character->getSubrace(),
            'prev_route' => $prevRoute
        ]);
    }

    #[Route('/step/7b/{id}', name: 'app_character_creation_step7b', methods: ['GET', 'POST'])]
    public function step7b(Request $request, Character $character, \App\Repository\FeatRepository $featRepository, \App\Repository\SkillRepository $skillRepository): Response
    
    {
        if (!$character->getSpecies()) {
            return $this->redirectToRoute('app_character_creation_step7', ['id' => $character->getId()]);
        }

        // 1. Detect Choices from Traits
        $choices = [];
        $speciesTraitsRaw = $character->getSpecies()->getTraits();
        $speciesTraits = is_array($speciesTraitsRaw) ? $speciesTraitsRaw : [];
        $subraceTraits = $character->getSubrace() ? ($character->getSubrace()->getTraits() ?? []) : [];
        
        $allTraits = array_merge($speciesTraits, $subraceTraits);

        foreach ($allTraits as $trait) {
            if (isset($trait['type']) && $trait['type'] === 'choice') {
                $uniqueId = 'trait_' . ($trait['code'] ?? uniqid());
                
                $choices[$uniqueId] = [
                    'label' => $trait['name'],
                    'description' => $trait['description'],
                    'type' => $trait['choice_type'] ?? 'unknown', // 'feat', 'skill', 'spell'
                    'pool' => $trait['pool'] ?? null, // e.g. 'origin'
                    'count' => $trait['count'] ?? 1,
                    'options' => [] 
                ];

                // Load Options based on type
                if ($choices[$uniqueId]['type'] === 'feat') {
                    $allFeats = $featRepository->findBy(['isActive' => true], ['name' => 'ASC']);
                    
                    if (($choices[$uniqueId]['pool'] ?? '') === 'origin') {
                         $choices[$uniqueId]['options'] = array_filter($allFeats, fn($f) => stripos($f->getType(), 'Origem') !== false || stripos($f->getType(), 'Origin') !== false);
                    } else {
                         $choices[$uniqueId]['options'] = $allFeats;
                    }

                    // Determine currently selected IDs for this choice type
                    // We check if the character HAS any of these available options
                    $charFeatIds = $character->getFeats()->map(fn($f) => $f->getId())->toArray();
                    $choices[$uniqueId]['selected_ids'] = array_intersect(
                        array_map(fn($f) => $f->getId(), $choices[$uniqueId]['options']),
                        $charFeatIds
                    );

                }
                elseif ($choices[$uniqueId]['type'] === 'skill') {
                     // Get current skills to mark selected, BUT exclude "base" skills from options if we want strictly new ones
                     // However, for "Back" functionality, we MUST include the ones we already picked so they show up as checked.
                     // The filtering logic previously was: exclude present skills. This is problematic if we already picked them!
                     // Logic fix: Exclude skills from Class Base, Background (future), etc.
                     // But strictly speaking, if we saved it to `character->skills`, it's there.
                     
                     // Implementation limitation: We can't easily distinguish "Skill from Class" vs "Skill from Species Choice" 
                     // unless we track source. for now, we just check if it's in character->skills.
                     
                     $currentSkillIds = $character->getSkills()->map(fn($s) => $s->getId())->toArray();
                     $allSkills = $skillRepository->findAll();
                     
                     // If we filter out "already possessed", we filter out the one we just saved!
                     // So we should probably show ALL valid skills, and maybe visually disable ones that are "hard" locked (like class skills)?
                     // OR, we accept that 'options' contains what we picked.
                     
                     // Let's just show ALL skills, but maybe mark them?
                     // Simply: Options = All Skills. Checked = In Character.
                     // But we shouldn't allow picking a skill we have from Class.
                     // Complexity: How to know which ones are from Class? $classDef->getBaseSkills().
                     
                     $classSkillIds = [];
                     if ($character->getClassDef()) {
                         // ClassDef has "profSkills" as text usually, or baseSkills if strictly mapped.
                         // Our system seems to use `profSkills` string or `class_def_skill` for fixed ones?
                         // Let's check `character->getSkills()` vs what we *can* pick.
                         // For now, let's just allow picking any not-yet-possessed-EXCEPT-if-it-was-picked-here.
                         // Ideally, we just show all.
                     }
                     
                     $choices[$uniqueId]['options'] = $allSkills;
                     $choices[$uniqueId]['selected_ids'] = $currentSkillIds;
                }
            }
        }

        // If no choices found, skip to Step 8
        if (empty($choices)) {
            return $this->redirectToRoute('app_character_creation_step8', ['id' => $character->getId()]);
        }

        if ($request->isMethod('POST')) {
            foreach ($choices as $uid => $config) {
                 $selectedIds = $request->request->all($uid); // array of IDs
                 // Input name in HTML should be name="uniqueId[]" for multiple or just name="uniqueId"
                 // Symfony request->all() is tricky if it's not array.
                 if (!is_array($selectedIds)) {
                     $selectedIds = [$selectedIds];
                 }
                 
                 // Basic validation count
                 if (count($selectedIds) > $config['count']) {
                     $this->addFlash('error', "Escolha no máximo {$config['count']} para {$config['label']}.");
                     // Return to view... logic simplified here
                 }
                 
                 if ($config['type'] === 'feat') {
                     foreach($selectedIds as $fid) {
                         $feat = $featRepository->find($fid);
                         if ($feat) $character->addFeat($feat);
                     }
                 }
                 elseif ($config['type'] === 'skill') {
                     foreach($selectedIds as $sid) {
                         $skill = $skillRepository->find($sid);
                         if ($skill) $character->addSkill($skill);
                     }
                 }
            }

            $this->entityManager->flush();
            return $this->redirectToRoute('app_character_creation_step8', ['id' => $character->getId()]);
        }
        
        return $this->render('character_creation/step7b_species_choices.html.twig', [
            'character' => $character,
            'choices' => $choices
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
                
                // Save Attribute Bonuses
                $bonuses = [];
                $totalPoints = 0;
                $allowedAttributes = ['Força', 'Destreza', 'Constituição', 'Inteligência', 'Sabedoria', 'Carisma'];
                
                foreach ($allowedAttributes as $attr) {
                    $val = (int) $request->request->get('bonus_' . $attr, 0);
                    if ($val > 0) {
                        $bonuses[$attr] = $val;
                        $totalPoints += $val;
                    }
                }
                
                // Validate total points (Server-side check)
                if ($totalPoints !== 3) {
                     $this->addFlash('error', "Por favor, distribua exatamente 3 pontos de bônus.");
                     // Re-render handled by falling through? No, need to halt flow.
                     // But we already set background... 
                     // Ideally we should re-render.
                     $backgrounds = $backgroundRepository->findBy([], ['name' => 'ASC']);
                     return $this->render('character_creation/step8_background.html.twig', [
                        'character' => $character,
                        'backgrounds' => $backgrounds,
                        'current_background' => $character->getBackground(),
                    ]);
                }

                $character->setAttributeBonuses($bonuses);
                
                // Clear existing character attributes to force recalculation in Step 9
                // This ensures if user changes bonuses, they are reapplied.
                foreach ($character->getCharacterAttributes() as $attr) {
                    $this->entityManager->remove($attr);
                }
                $character->getCharacterAttributes()->clear();
                
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
            'Psiônico' => ['Força' => 8, 'Destreza' => 12, 'Constituição' => 13, 'Inteligência' => 15, 'Sabedoria' => 14, 'Carisma' => 10],
        ];

        // Ensure Attributes exist in DB (Seed logic really, but handling here for safety)
        // Check if character already has attributes
        if ($character->getCharacterAttributes()->isEmpty()) {
            $className = $character->getClassDef() ? $character->getClassDef()->getName() : null;
            $baseStats = $classStats[$className] ?? ['Força'=>10,'Destreza'=>10,'Constituição'=>10,'Inteligência'=>10,'Sabedoria'=>10,'Carisma'=>10];

            // Base Bonuses from User Choice (Step 8)
            $userBonuses = $character->getAttributeBonuses() ?? [];

            // Background Entity Bonuses (Legacy/Specific rules - check if used or if replaced by the generic +3 rule)
            // The prompt implies the generic +3 rule is THE rule now. 
            // So we disable the old entity-based logic to avoid double dipping or confusion unless the entity defines something FIXED.
            // Assuming the new +3 rule replaces the specific background ability increases.
            $bonuses = $userBonuses;
            
            // Legacy Logic commented out for safety based on "Bônus de Atributo de 2 ou 3 atributos" prompt.
            /*
            $bg = $character->getBackground();
            if ($bg) {
                if ($bg->getAttribute1()) $bonuses[$bg->getAttribute1()->getName()] = ($bonuses[$bg->getAttribute1()->getName()] ?? 0) + 1;
                if ($bg->getAttribute2()) $bonuses[$bg->getAttribute2()->getName()] = ($bonuses[$bg->getAttribute2()->getName()] ?? 0) + 1;
                if ($bg->getAttribute3()) $bonuses[$bg->getAttribute3()->getName()] = ($bonuses[$bg->getAttribute3()->getName()] ?? 0) + 1;
            }
            */

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
        $userBonuses = $character->getAttributeBonuses() ?? [];

        foreach ($character->getCharacterAttributes() as $ca) {
            $val = $ca->getValue();
            $mod = floor(($val - 10) / 2);
            $attrName = $ca->getAttribute()->getName();
            $bonus = $userBonuses[$attrName] ?? 0;

            $attributes[] = [
                'name' => $attrName,
                'value' => $val,
                'modifier' => ($mod >= 0 ? '+' : '') . $mod,
                'entity' => $ca,
                'bonus' => $bonus, // Pass the bonus value
                'base' => $val - $bonus // Pass base value for clarity if needed
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
        // Update: User requested 2 extra slots for flexibility.
        $slots = 3;

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
        $currentChoices = [];
        foreach ($character->getLanguages() as $l) {
            if (!in_array($l->getId(), $fixedIds)) {
                $currentChoices[] = $l->getId();
            }
        }
        
        // Ensure we have enough entries for the slots (fill with null)
        $currentChoices = array_pad($currentChoices, $slots, null);

        return $this->render('character_creation/step10_languages.html.twig', [
            'character' => $character,
            'fixed_languages' => $fixedLanguages,
            'standard_languages' => $standardLanguages,
            'exotic_languages' => $exoticLanguages,
            'slots' => $slots,
            'current_choices' => $currentChoices, // Pass indexed array
        ]);
    }

    #[Route('/step/11/{id}', name: 'app_character_creation_step11', methods: ['GET', 'POST'])]
    public function step11(Request $request, Character $character, \App\Repository\EquipmentRepository $equipmentRepository): Response
    {
        // Calculate Totals
        $currentWeight = 0;
        $currentCost = 0;
        foreach ($character->getInventory() as $item) {
            $currentWeight += (float) $item->getWeightKg();
             // Parse Cost (Simplified: assuming costGp is standard)
            $currentCost += (float) $item->getCostGp();
        }

        // Handle Actions
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            
            // Navigate Next
            if ($action === 'next') {
                // Update Coins
                $character->setCoinCp((int)$request->request->get('coinC'));
                $character->setCoinSp((int)$request->request->get('coinS'));
                $character->setCoinEp((int)$request->request->get('coinE'));
                $character->setCoinGp((int)$request->request->get('coinG'));
                $character->setCoinPp((int)$request->request->get('coinP'));

                $this->entityManager->flush();
                $character->setCoinPp((int)$request->request->get('coinP'));

                $this->entityManager->flush();

                // Check for Class Feats (Step 11b)
                $featsKnown = 0;
                if ($character->getClassDef()) {
                    $classLevel = $this->classLevelRepository->findOneBy([
                        'classDef' => $character->getClassDef(),
                        'level' => 1
                    ]);
                    if ($classLevel) {
                        $featsKnown = $classLevel->getFeatsKnown() ?? 0;
                    }
                }

                if ($featsKnown > 0) {
                     return $this->redirectToRoute('app_character_creation_step11b', ['id' => $character->getId()]);
                }

                return $this->redirectToRoute('app_character_creation_step12', ['id' => $character->getId()]);
            }

            // Add Item
            if ($action === 'add') {
                $itemId = $request->request->get('item_id');
                $item = $equipmentRepository->find($itemId);
                if ($item) {
                    $character->addInventoryItem($item);
                    $this->entityManager->flush();
                    $this->addFlash('success', "Item adicionado: " . ($item->getNamePt() ?? $item->getName()));
                }
            }
            
            // Remove Item
            if ($action === 'remove') {
                $itemId = $request->request->get('item_id');
                $item = $equipmentRepository->find($itemId); // Get entity strictly
                if ($item) {
                    $character->removeInventoryItem($item);
                    $this->entityManager->flush();
                    $this->addFlash('success', "Item removido.");
                }
            }

             // Redirect back to same step to refresh list
             return $this->redirectToRoute('app_character_creation_step11', ['id' => $character->getId()]);
        }

        // Fetch All Equipment for List
        // Optimize: Group by type
        $allEquipment = $equipmentRepository->findBy(['isActive' => true], ['namePt' => 'ASC', 'name' => 'ASC']);
        
        return $this->render('character_creation/step11_equipment.html.twig', [
            'character' => $character,
            'equipment_list' => $allEquipment,
            'current_weight' => $currentWeight,
            'current_cost' => $currentCost,
        ]);
    }

    #[Route('/step/11b/{id}', name: 'app_character_creation_step11b', methods: ['GET', 'POST'])]
    public function step11b(Request $request, Character $character, \App\Repository\FeatRepository $featRepository): Response
    {
        if (!$character->getClassDef()) {
             return $this->redirectToRoute('app_character_creation_step1', ['id' => $character->getId()]);
        }

        // Determine Feats Known
        $featsKnown = 0;
        $classLevel = $this->classLevelRepository->findOneBy([
            'classDef' => $character->getClassDef(),
            'level' => 1
        ]);
        if ($classLevel) {
            $featsKnown = $classLevel->getFeatsKnown() ?? 0;
        }

        // Safety: If 0, skip
        if ($featsKnown <= 0) {
            return $this->redirectToRoute('app_character_creation_step12', ['id' => $character->getId()]);
        }
        
        // Get Available Feats from ClassDef
        $availableFeats = $character->getClassDef()->getAvailableFeats();

        if ($request->isMethod('POST')) {
            $selectedFeatIds = $request->request->all('feats');
            
            if (count($selectedFeatIds) > $featsKnown) {
                 $this->addFlash('error', "Você pode escolher no máximo $featsKnown talentos.");
            } else {
                // We need to distinguish Class Feats vs Origin Feats vs Specie Feats?
                // Currently Character->feats covers all.
                // WE SHOULD NOT wipe all feats, because Specie feats (Step 7b) are there.
                // But we should replace PREVIOUS Class Feats... 
                // Problem: How do we know which are Class Feats?
                // Assumption: The ones available in this list are Class Feats.
                // Strategy: Remove any feat from character that IS present in $availableFeats list, then add selected.
                
                $availableFeatIds = [];
                foreach ($availableFeats as $af) {
                    $availableFeatIds[] = $af->getId();
                    // Remove if present
                    if ($character->getFeats()->contains($af)) {
                        $character->removeFeat($af);
                    }
                }
                
                // Add Selected
                foreach ($selectedFeatIds as $fid) {
                    $feat = $featRepository->find($fid);
                    // Verify it is allowed
                    if ($feat && in_array($feat->getId(), $availableFeatIds)) {
                         $character->addFeat($feat);
                    }
                }
                
                $this->entityManager->flush();
                return $this->redirectToRoute('app_character_creation_step12', ['id' => $character->getId()]);
            }
        }
        
        // Determine current selection (intersection of char feats and available feats)
        $currentFeatIds = [];
        foreach ($character->getFeats() as $f) {
            if ($availableFeats->contains($f)) {
                $currentFeatIds[] = $f->getId();
            }
        }

        return $this->render('character_creation/step11b_feats.html.twig', [
            'character' => $character,
            'available_feats' => $availableFeats,
            'feats_known' => $featsKnown,
            'current_feat_ids' => $currentFeatIds,
        ]);
    }

    #[Route('/step/12/{id}', name: 'app_character_creation_step12', methods: ['GET', 'POST'])]
    public function step12(Request $request, Character $character, \App\Repository\CharacterAttributeRepository $charAttrRepo): Response
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
            
            if ($name) {
                $character->setName($name);
                $character->setAlignment($alignment);
                $character->setAppearance($appearance);
                $character->setBonds($bonds);
                $character->setOrigin($origin);
                
                $trinketId = $request->request->get('trinket_id');
                if ($trinketId) {
                    $trinket = $this->trinketRepository->find($trinketId);
                    if ($trinket) $character->setTrinket($trinket);
                }
                
                // Image Upload Handling
                $imageFile = $request->files->get('image');
                if ($imageFile) {
                    $character->setImageFile($imageFile);
                }

                $character->setIsComplete(true);
                
                $this->entityManager->flush();
                
                // Redirect to Character Sheet
                return $this->redirectToRoute('admin_character_show', ['id' => $character->getId()]);
            } else {
                $this->addFlash('error', "O nome é obrigatório.");
            }
        }

        return $this->render('character_creation/step12_final.html.twig', [
            'character' => $character,
            'stats' => [
                'hp' => $maxHp,
                'ac' => $ac,
                'initiative' => ($initiative >= 0 ? '+' : '') . $initiative,
                'passive_perception' => $passivePerception,
                'hit_dice' => $hitDice
            ],
            'attributes' => $attributes,
            'modifiers' => $modifiers,
            'trinkets' => $this->trinketRepository->findAll(),
        ]);
    }
}
