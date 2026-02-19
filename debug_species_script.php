<?php

use App\Kernel;
use App\Entity\Species;
use App\Entity\Character;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__ . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new class ($kernel) {
        public function __construct(private Kernel $kernel)
        {
        }

        public function __invoke()
        {
            $this->kernel->boot();
            $container = $this->kernel->getContainer();

            if ($container->has(EntityManagerInterface::class)) {
                $em = $container->get(EntityManagerInterface::class);
            } elseif ($container->has('doctrine.orm.entity_manager')) {
                $em = $container->get('doctrine.orm.entity_manager');
            } else {
                echo "Cannot get EntityManager.\n";
                return;
            }

            // check character 39 just in case
            $character = $em->getRepository(Character::class)->find(39);
            if ($character) {
                echo "Character 39 found. Name: " . $character->getName() . "\n";
                if ($character->getSpecies()) {
                    echo "Selected Species: " . $character->getSpecies()->getName() . "\n";
                } else {
                    echo "No species selected yet.\n";
                }
            } else {
                echo "Character 39 not found.\n";
            }

            echo "--------------------------------------------------\n";

            // Find Draconato
            $species = $em->getRepository(Species::class)->findOneBy(['name' => 'Draconato']);

            if ($species) {
                echo "Species Found: " . $species->getName() . " (ID: " . $species->getId() . ")\n";
                echo "Description MD: START\n";
                echo $species->getDescriptionMd() . "\n";
                echo "Description MD: END\n";
            } else {
                echo "Species 'Draconato' not found.\n";
                // List all to see if name is slightly different
                $all = $em->getRepository(Species::class)->findAll();
                echo "Available Species:\n";
                foreach ($all as $s) {
                    echo "- " . $s->getName() . "\n";
                }
            }
        }
    };
};
