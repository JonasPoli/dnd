<?php

use App\Entity\Feature;
use App\Kernel;

require_once __DIR__ . '/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);

    return new class ($kernel) extends \Symfony\Bundle\FrameworkBundle\Console\Application {
        public function doRun(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
        {
            $kernel = $this->getKernel();
            $kernel->boot();
            $em = $kernel->getContainer()->get('doctrine')->getManager();
            $count = $em->getRepository(Feature::class)->count([]);
            $output->writeln("Total Features: " . $count);

            // Breakdown
            $conn = $em->getConnection();
            $rows = $conn->fetchAllAssociative("SELECT owner_type, COUNT(*) as c FROM feature GROUP BY owner_type");
            foreach ($rows as $row) {
                $output->writeln("  - " . $row['owner_type'] . ": " . $row['c']);
            }
            return 0;
        }
    };
};
