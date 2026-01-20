<?php
require __DIR__.'/config/bootstrap.php';

$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();
/** @var \App\Repository\MonsterRepository $monsterRepo */
$monsterRepo = $container->get(App\Repository\MonsterRepository::class);
/** @var \App\Command\TranslateMonstersCommand $translator */
$translator = $container->get(App\Command\TranslateMonstersCommand::class);

$ids = [2324, 126];
foreach ($ids as $id) {
    $monster = $monsterRepo->find($id);
    if (!$monster) {
        echo "Monster ID $id not found\n";
        continue;
    }
    $src = $monster->getSrcJson();
    echo "Original srcJson for ID $id:\n";
    var_export($src);
    echo "\n--- Sanitized---\n";
    $sanitized = $translator->sanitizeSourceData($src);
    var_export($sanitized);
    echo "\n--- Validation---\n";
    $error = null;
    $valid = $translator->validateStructure($src, $sanitized, $error);
    echo $valid ? "Valid\n" : "Invalid: $error\n";
    echo "====================\n";
}
?>
