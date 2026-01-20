<?php

use App\Kernel;
use App\Entity\Feat;
use Doctrine\ORM\EntityManagerInterface;

require_once __DIR__.'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new class($kernel) {
        public function __construct(private Kernel $kernel)
        {
        }

        public function __invoke()
        {
            $this->kernel->boot();
            $container = $this->kernel->getContainer();
            /** @var EntityManagerInterface $em */
            $em = $container->get('doctrine.orm.entity_manager');
            
            $repo = $em->getRepository(Feat::class);
            $count = $repo->count([]);
            $activeCount = $repo->count(['isActive' => true]);
            
            echo "Total Feats: $count\n";
            echo "Active Feats: $activeCount\n";
            
            // List first 5 to verify
            $feats = $repo->findBy([], null, 5);
            foreach ($feats as $feat) {
                echo "- " . $feat->getName() . "\n";
            }
        }
    };
};
