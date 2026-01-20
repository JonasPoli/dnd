<?php

use App\Kernel;
use App\Entity\Monster;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();

$id = 1024;
echo "===================================================\n";
$monster = $em->getRepository(Monster::class)->find($id);

if (!$monster) {
    echo "Monster $id not found.\n";
    exit;
}

echo "Monster ID: " . $monster->getId() . " (" . $monster->getName() . ")\n";

echo "\n--- srcJson['description'] ---\n";
$srcJson = $monster->getSrcJson();
var_dump($srcJson['description'] ?? 'KEY MISSING');

echo "\n--- srcJsonPt['description'] ---\n";
$srcJsonPt = $monster->getSrcJsonPt();
if ($srcJsonPt) {
    var_dump($srcJsonPt['description'] ?? 'KEY MISSING');
} else {
    echo "srcJsonPt is NULL\n";
}

echo "\n--- descriptionMd column ---\n";
var_dump($monster->getDescriptionMd());

echo "\n--- descriptionMdPt column ---\n";
var_dump($monster->getDescriptionMdPt());
echo "===================================================\n";
