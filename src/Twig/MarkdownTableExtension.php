<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownTableExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('parse_markdown_tables', [$this, 'parseTables'], ['is_safe' => ['html']]),
        ];
    }

    public function parseTables(string $content): string
    {
        // Pattern to look for table blocks
        // It's tricky to match the whole block in one go safely with regex due to catastrophic backtracking risks.
        // We will simplistic approach: iterate lines.

        $lines = explode("\n", $content);
        $inTable = false;
        $tableLines = [];
        $newContent = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Check if line looks like a table row: starts and ends with | or contains |
            // Standard markdown table row: | cell | cell |
            if (preg_match('/^\|.*\|$/', $trimmed)) {
                $inTable = true;
                $tableLines[] = $trimmed;
            } else {
                if ($inTable) {
                    // Start of non-table line, process the captured table first
                    $newContent[] = $this->convertTableBlock($tableLines);
                    $tableLines = [];
                    $inTable = false;
                }
                $newContent[] = $line;
            }
        }

        // If we ended while in a table
        if ($inTable) {
            $newContent[] = $this->convertTableBlock($tableLines);
        }

        return implode("\n", $newContent);
    }

    private function convertTableBlock(array $lines): string
    {
        if (count($lines) < 2) {
            // Not a valid table, just return original lines
            return implode("\n", $lines);
        }

        $html = '<table class="w-full text-left border-collapse">';

        // Header
        $headerLine = array_shift($lines);
        $headers = $this->parseRow($headerLine);

        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th class="p-4 border-b-2 border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/30 font-bold text-indigo-900 dark:text-indigo-100">' . trim($header) . '</th>';
        }
        $html .= '</tr></thead>';

        // Separator line (usually |---|---|) - we skip it but valid it exists
        $separatorLine = array_shift($lines); // We assume second line is separator for now

        $html .= '<tbody>';
        foreach ($lines as $line) {
            $cells = $this->parseRow($line);
            $html .= '<tr class="border-b border-indigo-100 dark:border-indigo-800 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20">';
            foreach ($cells as $cell) {
                // Determine if we should handle colspan or empty cells? For now simple.
                // Apply internal formatting (bold/italic) inside cell is handled by markdown parser later? 
                // Wait, if we parse table NOW, the markdown parser might ignore HTML.
                // We should probably strip outer markdown or just render cell content.
                // Since this filter runs BEFORE markdown_to_html typically (or separate), we might want to handle basic inline MD here OR rely on the next pass.
                // Recommendation: Clean basics.
                $cleanCell = trim($cell);
                // Basic bold support
                $cleanCell = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $cleanCell);
                $cleanCell = preg_replace('/_(.*?)_/', '<em>$1</em>', $cleanCell);

                $html .= '<td class="p-3">' . $cleanCell . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        return $html;
    }

    private function parseRow(string $line): array
    {
        // Remove leading/trailing pipes
        $line = trim($line, '|');
        return explode('|', $line);
    }
}
