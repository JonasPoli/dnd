<?php

namespace App\Command;

use App\Entity\Trinket;
use App\Entity\RulesSource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:trinkets',
    description: 'Seeds the database with D&D trinkets (bugigangas)',
)]
class SeedTrinketsCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get or create default rules source
        $rulesSource = $this->entityManager->getRepository(RulesSource::class)
            ->findOneBy(['slug' => 'phb']) ?? $this->entityManager->getRepository(RulesSource::class)->findOneBy([]);

        if (!$rulesSource) {
            $io->error('No RulesSource found. Please create one first.');
            return Command::FAILURE;
        }

        $trinkets = $this->getTrinketData();

        $io->section('Seeding Trinkets');
        $io->progressStart(count($trinkets));

        foreach ($trinkets as $data) {
            $trinket = new Trinket();
            $trinket->setRulesSource($rulesSource);
            $trinket->setRollKey($data['roll']);
            $trinket->setTextMd($data['text']);

            $this->entityManager->persist($trinket);
            $io->progressAdvance();
        }

        $this->entityManager->flush();
        $io->progressFinish();

        $io->success(sprintf('%d trinkets seeded successfully!', count($trinkets)));

        return Command::SUCCESS;
    }

    private function getTrinketData(): array
    {
        return [
            ['roll' => 1, 'text' => 'Uma mão de goblin mumificada'],
            ['roll' => 2, 'text' => 'Um cristal que brilha levemente ao luar'],
            ['roll' => 3, 'text' => 'Uma moeda de ouro cunhada em uma terra desconhecida'],
            ['roll' => 4, 'text' => 'Um diário escrito em um idioma que você não conhece'],
            ['roll' => 5, 'text' => 'Um anel de latão que nunca mancha'],
            ['roll' => 6, 'text' => 'Uma antiga peça de xadrez feita de vidro'],
            ['roll' => 7, 'text' => 'Um par de dados feitos de ossos de articulação, cada um com o símbolo de um crânio na face que normalmente mostraria seis pontos'],
            ['roll' => 8, 'text' => 'Um pequeno ídolo que representa uma criatura aterradora e lhe causa sonhos inquietantes quando você dorme perto dele'],
            ['roll' => 9, 'text' => 'Uma mecha de cabelo de alguém'],
            ['roll' => 10, 'text' => 'A escritura de um terreno em um reino desconhecido por você'],
            ['roll' => 11, 'text' => 'Um bloco pesando 30 gramas produzido de material desconhecido'],
            ['roll' => 12, 'text' => 'Uma pequena boneca de tecido espetada com agulhas'],
            ['roll' => 13, 'text' => 'Um dente de uma fera desconhecida'],
            ['roll' => 14, 'text' => 'Uma escama enorme, talvez de um dragão'],
            ['roll' => 15, 'text' => 'Uma pena verde brilhante'],
            ['roll' => 16, 'text' => 'Uma antiga carta de adivinhação com imagens semelhantes a você'],
            ['roll' => 17, 'text' => 'Um orbe de vidro cheio de fumaça se movimentando'],
            ['roll' => 18, 'text' => 'Um ovo com uma casca vermelha e brilhante, pesando 500 gramas'],
            ['roll' => 19, 'text' => 'Um tubo que sopra bolhas'],
            ['roll' => 20, 'text' => 'Um frasco de vidro contendo um pouco de carne flutuando no fluido de decapagem'],
            ['roll' => 21, 'text' => 'Uma caixa de música feita por gnomos que toca uma música que você lembra vagamente da sua infância'],
            ['roll' => 22, 'text' => 'Uma estatueta de madeira de um pequenino orgulhoso'],
            ['roll' => 23, 'text' => 'Um orbe de bronze com runas estranhas gravadas'],
            ['roll' => 24, 'text' => 'Um disco de pedra multicolorido'],
            ['roll' => 25, 'text' => 'Um ícone de um corvo prateado'],
            ['roll' => 26, 'text' => 'Uma bolsa contendo quarenta e sete dentes, um dos quais está podre'],
            ['roll' => 27, 'text' => 'Uma lasca de obsidiana que, quando tocada, provoca sensação de calor'],
            ['roll' => 28, 'text' => 'Uma garra de dragão amarrada em um cordão de couro'],
            ['roll' => 29, 'text' => 'Um par de meias velhas'],
            ['roll' => 30, 'text' => 'Um livro em branco cujas páginas se recusam ter tinta, giz, grafite ou qualquer outra marcação gravada'],
            ['roll' => 31, 'text' => 'Um emblema de prata de uma estrela de cinco pontas'],
            ['roll' => 32, 'text' => 'Uma faca que pertencia a um parente'],
            ['roll' => 33, 'text' => 'Um frasco de vidro cheio de pedaços de unhas'],
            ['roll' => 34, 'text' => 'Um dispositivo de metal retangular com dois minúsculos copos de metal em uma extremidade que lança faíscas quando molhado'],
            ['roll' => 35, 'text' => 'Uma luva branca, com adornos, projetada para um ser humano'],
            ['roll' => 36, 'text' => 'Um colete com cem bolsos minúsculos'],
            ['roll' => 37, 'text' => 'Uma pedra sem peso'],
            ['roll' => 38, 'text' => 'Um rascunho desenhado de um goblin'],
            ['roll' => 39, 'text' => 'Um frasco de vidro vazio que cheira a perfume'],
            ['roll' => 40, 'text' => 'Uma pedra preciosa que se parece com um pedaço de carvão quando examinada por alguém que não seja você'],
            ['roll' => 41, 'text' => 'Um pedaço de pano de uma bandeira velha'],
            ['roll' => 42, 'text' => 'Uma insígnia de patente de um legionário perdido'],
            ['roll' => 43, 'text' => 'Um sino de prata sem badalo'],
            ['roll' => 44, 'text' => 'Um canário mecânico dentro de uma lâmpada'],
            ['roll' => 45, 'text' => 'Um baú em miniatura esculpido para parecer que tem vários pés na parte inferior'],
            ['roll' => 46, 'text' => 'Um sprite morto dentro de uma garrafa de vidro transparente'],
            ['roll' => 47, 'text' => 'Uma lata de metal que não tem abertura, mas soa como se estivesse cheia de líquido, areia, aranhas ou vidro quebrado (à sua escolha)'],
            ['roll' => 48, 'text' => 'Um orbe de vidro cheio de água, no qual um peixinho dourado mecânico fica nadando'],
            ['roll' => 49, 'text' => 'Uma colher de prata com um M gravado na pega'],
            ['roll' => 50, 'text' => 'Um apito feito de madeira dourada'],
            ['roll' => 51, 'text' => 'Um escaravelho morto do tamanho da sua mão'],
            ['roll' => 52, 'text' => 'Dois soldados de brinquedo, um sem cabeça'],
            ['roll' => 53, 'text' => 'Uma pequena caixa cheia de botões de diferentes tamanhos'],
            ['roll' => 54, 'text' => 'Uma vela que não pode ser acesa'],
            ['roll' => 55, 'text' => 'Uma gaiola em miniatura sem porta'],
            ['roll' => 56, 'text' => 'Uma chave antiga'],
            ['roll' => 57, 'text' => 'Um mapa de tesouro indecifrável'],
            ['roll' => 58, 'text' => 'Uma empunhadura de uma espada quebrada'],
            ['roll' => 59, 'text' => 'Uma pata de coelho'],
            ['roll' => 60, 'text' => 'Um olho de vidro'],
            ['roll' => 61, 'text' => 'Uma breve aparição de uma pessoa de aparência desagradável'],
            ['roll' => 62, 'text' => 'Um crânio de prata do tamanho de uma moeda'],
            ['roll' => 63, 'text' => 'Uma máscara de alabastro'],
            ['roll' => 64, 'text' => 'Um cone de incenso-preto, pegajoso, que fede'],
            ['roll' => 65, 'text' => 'Um gorro de dormir que te dá sonhos agradáveis quando você o usa'],
            ['roll' => 66, 'text' => 'Um único estrepe produzido de osso'],
            ['roll' => 67, 'text' => 'Uma armação dourada de um monóculo sem a lente'],
            ['roll' => 68, 'text' => 'Um cubo de 2,5 centímetros, cada lado com uma cor diferente'],
            ['roll' => 69, 'text' => 'Uma maçaneta de cristal'],
            ['roll' => 70, 'text' => 'Um pacote cheio de pó rosa'],
            ['roll' => 71, 'text' => 'Um fragmento de uma bela canção, escrita como notas musicais em dois pedaços de pergaminho'],
            ['roll' => 72, 'text' => 'Um brinco prateado em formato de lágrima produzido de uma lágrima verdadeira'],
            ['roll' => 73, 'text' => 'Uma casca de ovo pintada com cenas de desgraça em detalhes perturbadores'],
            ['roll' => 74, 'text' => 'Um leque que, quando desdobrado, mostra um gato dormindo'],
            ['roll' => 75, 'text' => 'Um conjunto de tubos produzidos com osso'],
            ['roll' => 76, 'text' => 'Um trevo-de-quatro-folhas pressionado dentro de um livro sobre boas maneiras e etiqueta'],
            ['roll' => 77, 'text' => 'Uma folha de pergaminho sobre a qual é desenhada uma engenhoca mecânica'],
            ['roll' => 78, 'text' => 'Uma bainha ornamentada que não cabe em nenhuma lâmina que você tenha encontrado'],
            ['roll' => 79, 'text' => 'Um convite para uma festa onde aconteceu um assassinato'],
            ['roll' => 80, 'text' => 'Um pentagrama de bronze com uma gravação da cabeça de um rato em seu centro'],
            ['roll' => 81, 'text' => 'Um lenço roxo bordado com o nome de um arquimago'],
            ['roll' => 82, 'text' => 'Metade de uma planta baixa de um templo, um castelo ou outra estrutura'],
            ['roll' => 83, 'text' => 'Um pedaço de pano dobrado que, quando desdobrado, se transforma em um chapéu elegante'],
            ['roll' => 84, 'text' => 'Um recibo de depósito em um banco em uma cidade distante'],
            ['roll' => 85, 'text' => 'Um diário com sete páginas faltando'],
            ['roll' => 86, 'text' => 'Uma caixa prateada de tabaco vazia com a inscrição "sonhos" na tampa'],
            ['roll' => 87, 'text' => 'Um símbolo sagrado de ferro dedicado a um deus desconhecido'],
            ['roll' => 88, 'text' => 'Um livro sobre a ascensão e queda de um herói lendário, com o último capítulo faltando'],
            ['roll' => 89, 'text' => 'Um frasco com sangue de dragão'],
            ['roll' => 90, 'text' => 'Uma flecha antiga de estilo élfico'],
            ['roll' => 91, 'text' => 'Uma agulha que nunca entorta'],
            ['roll' => 92, 'text' => 'Um broche ornamentado de estilo anão'],
            ['roll' => 93, 'text' => 'Uma garrafa de vinho vazia com um rótulo bonito que diz: "Vinícola O Mago dos Vinhos e Vinhais, Enlaço do Dragão Vermelho, 331422-W"'],
            ['roll' => 94, 'text' => 'Um mosaico com uma superfície em vidro multicolorido'],
            ['roll' => 95, 'text' => 'Um rato petrificado'],
            ['roll' => 96, 'text' => 'Uma bandeira pirata negra, adornada com uma caveira de dragão e ossos cruzados'],
            ['roll' => 97, 'text' => 'Um minúsculo caranguejo ou aranha mecânicos que se move quando não está sendo observado'],
            ['roll' => 98, 'text' => 'Um frasco de vidro contendo banha com uma etiqueta que diz: "Graxa de Grifo"'],
            ['roll' => 99, 'text' => 'Uma caixa de madeira com um fundo de cerâmica que contém uma minhoca viva com uma cabeça em cada extremidade do corpo'],
            ['roll' => 100, 'text' => 'Uma urna de metal contendo as cinzas de um herói'],
        ];
    }
}
