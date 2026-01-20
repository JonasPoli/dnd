<?php

use App\Kernel;
use App\Entity\Monster;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
// Access public doctrine service or via public container alias if possible, 
// usually 'doctrine' is public or 'doctrine.orm.entity_manager'
$em = $container->get('doctrine')->getManager();

foreach ([126, 2324] as $id) {
    echo "===================================================\n";
    $monster = $em->getRepository(Monster::class)->find($id);
    if (!$monster) {
        echo "Monster $id not found.\n";
        continue;
    }

    echo "Monster ID: " . $monster->getId() . " (" . $monster->getName() . ")\n";
    echo "srcJson dump:\n";
    var_dump($monster->getSrcJson());
    echo "\nsrcJsonPt dump:\n";
    var_dump($monster->getSrcJsonPt());
    echo "===================================================\n";
}
