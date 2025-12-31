<?php

namespace App\Command;

use App\Entity\MagicItem;
use App\Repository\MagicItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:translate:magic-items',
    description: 'Translates MagicItem descriptions to Portuguese using OpenAI GPT.',
)]
class TranslateMagicItemsCommand extends Command
{
    public function __construct(
        private MagicItemRepository $magicItemRepository,
        private EntityManagerInterface $entityManager,
        private HttpClientInterface $httpClient,
        #[Autowire(env: 'OPENAI_API_KEY')]
        private string $openAiApiKey,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Number of items to process', 1)
            ->addOption('model', null, InputOption::VALUE_OPTIONAL, 'OpenAI model to use', 'gpt-4o-mini')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = (int) $input->getOption('limit');
        $model = $input->getOption('model');

        if (empty($this->openAiApiKey)) {
            $io->error('OPENAI_API_KEY is not set in .env');
            return Command::FAILURE;
        }

        $items = $this->magicItemRepository->createQueryBuilder('m')
            ->where('m.descriptionMdPt IS NULL OR m.descriptionMdPt = :empty')
            ->andWhere('m.descriptionMd IS NOT NULL')
            ->setParameter('empty', '')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        if (empty($items)) {
            $io->success('No magic items found needing translation.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Found %d items to translate using model %s.', count($items), $model));
        $io->progressStart(count($items));

        foreach ($items as $item) {
            $prompts = [
                ['role' => 'system', 'content' => 'You are a professional translator for RPG content, specifically Dungeons & Dragons (D&D) 2024 rules. Translate the magic item name and description to Portuguese (Brazil). Keep terminology consistent with official translations. Return JSON with keys "name" and "description".'],
                ['role' => 'user', 'content' => sprintf("Name: %s\n\nDescription:\n%s", $item->getName(), $item->getDescriptionMd())],
            ];

            try {
                $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->openAiApiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $model,
                        'messages' => $prompts,
                        'temperature' => 0.3,
                        'response_format' => ['type' => 'json_object'],
                    ],
                    'timeout' => 60,
                ]);

                $data = $response->toArray();
                $content = $data['choices'][0]['message']['content'] ?? null;
                
                if ($content) {
                    $json = json_decode($content, true);
                    $translatedName = $json['name'] ?? null;
                    $translatedDescription = $json['description'] ?? null;

                    if ($translatedName && $translatedDescription) {
                        $item->setNamePt($translatedName);
                        $item->setDescriptionMdPt($translatedDescription);
                        $this->entityManager->flush();

                        if (count($items) === 1) {
                            $io->note(sprintf(
                                "ID: %d\nName (PT): %s\nDescription (PT):\n%s",
                                $item->getId(),
                                $translatedName,
                                $translatedDescription
                            ));
                        }
                    } else {
                        $io->error('Incomplete translation JSON for item: ' . $item->getName());
                    }
                } else {
                    $io->error('Failed to get translation for item: ' . $item->getName());
                }

            } catch (\Exception $e) {
                $io->error('Error translating item ' . $item->getName() . ': ' . $e->getMessage());
            }

            $io->progressAdvance();
            usleep(200000); 
        }

        $io->progressFinish();
        $io->success('Translation complete.');

        return Command::SUCCESS;
    }
}
