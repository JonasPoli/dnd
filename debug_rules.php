<?php

use App\Entity\RuleSection;
use App\Kernel;
use Twig\Extra\Markdown\MarkdownInterface;

require_once __DIR__ . '/vendor/autoload_runtime.php';

return function (array $context) {
    try {
        $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
        $kernel->boot();
        $container = $kernel->getContainer();

        $em = $container->get('doctrine')->getManager();
        $repo = $em->getRepository(RuleSection::class);

        $section = $repo->find(52);

        if (!$section) {
            echo "RuleSection 52 not found.\n";
            return;
        }

        echo "Found RuleSection 52: " . $section->getName() . "\n";
        $rawPt = $section->getContentMdPt();

        echo "\nTesting Parser via Container Service:\n";

        if ($container->has(MarkdownInterface::class)) {
            $service = $container->get(MarkdownInterface::class);
            echo "Service Class: " . get_class($service) . "\n";

            if ($rawPt) {
                $html = $service->convert($rawPt);
                echo "--- PARSED HTML (First 500 chars) ---\n";
                echo substr($html, 0, 500) . "\n";
            }
        } else {
            echo "MarkdownInterface service NOT FOUND in container.\n";
            // Check for alias
            if ($container->has('twig.markdown.default')) {
                echo "Found 'twig.markdown.default' service.\n";
            }
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }

};
