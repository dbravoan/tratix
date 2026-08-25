<?php

namespace App\Services;

class ClauseFormatter
{
    /**
     * Formats a clause body string into clean, structured HTML for Web or PDF views.
     */
    public static function formatHtml(?string $body, bool $isPdf = false): string
    {
        if (blank($body)) {
            return '';
        }

        // 1. Normalize inline bullets and section dividers into separate lines
        $normalized = self::normalizeClauseText($body);

        // 2. Parse lines into structured blocks (headers, subheaders, lists, paragraphs)
        $lines = preg_split('/\r\n|\r|\n/', $normalized);
        $output = [];
        $currentList = [];

        $flushList = function () use (&$output, &$currentList, $isPdf) {
            if (empty($currentList)) {
                return;
            }
            if ($isPdf) {
                $itemsHtml = implode('', array_map(function ($item) {
                    return '<li style="margin-bottom: 2.5px; line-height: 1.35; text-align: left; font-size: 10px; color: #2d3748;">'.htmlspecialchars($item, ENT_QUOTES, 'UTF-8').'</li>';
                }, $currentList));
                $output[] = '<ul style="margin: 2px 0 6px 16px; padding: 0; list-style-type: disc;">'.$itemsHtml.'</ul>';
            } else {
                $itemsHtml = implode('', array_map(function ($item) {
                    return '<li class="flex items-start gap-2 leading-relaxed"><span class="text-emerald-400 font-bold select-none text-[10px] mt-0.5">•</span><span class="text-slate-300">'.htmlspecialchars($item, ENT_QUOTES, 'UTF-8').'</span></li>';
                }, $currentList));
                $output[] = '<ul class="space-y-1 my-1.5 pl-2 text-xs list-none">'.$itemsHtml.'</ul>';
            }
            $currentList = [];
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                $flushList();
                continue;
            }

            // Detect Bullet List Item
            if (preg_match('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}\x{25CF}\*\-]\s*(.*)$/u', $trimmed, $m)) {
                $itemText = trim($m[1]);
                if ($itemText !== '') {
                    $currentList[] = $itemText;
                }
                continue;
            }

            // Not a bullet -> flush any open list
            $flushList();

            // Detect Main Section Header (e.g., "1. PARTE VENDEDORA (Juan Perez):", "VENDEDOR — DERECHOS:", "3. MARCO Y REFERENCIAS LEGALES APLICABLES:")
            if (preg_match('/^(?:\d+\.\s+[A-Za-zÁÉÍÓÚáéíóúÑñ\s\(\)\:\-\—]+|[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+[—\-–]\s*(?:DERECHOS|OBLIGACIONES)?:?)$/u', $trimmed)) {
                if ($isPdf) {
                    $output[] = '<div class="clause-section-header" style="font-weight: bold; color: #0f766e; font-size: 10.5px; margin-top: 7px; margin-bottom: 3px; border-bottom: 1px solid #e2e8f0; padding-bottom: 2px;">'.htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8').'</div>';
                } else {
                    $output[] = '<div class="font-bold text-emerald-400 text-xs uppercase tracking-wide mt-3 mb-1.5 border-b border-slate-700/50 pb-1 flex items-center gap-1.5"><span>§</span> '.htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8').'</div>';
                }
                continue;
            }

            // Detect Sub-Section Header (e.g., "a) DERECHOS:", "b) OBLIGACIONES:", "DERECHOS:", "OBLIGACIONES:")
            if (preg_match('/^(?:[a-d]\)\s*[A-ZÁÉÍÓÚÑ\s]+:?|(?:DERECHOS|OBLIGACIONES|MARCO LEGAL|REFERENCIAS LEGALES):?)$/ui', $trimmed)) {
                if ($isPdf) {
                    $output[] = '<div class="clause-sub-header" style="font-weight: bold; color: #1a202c; font-size: 10px; margin-top: 4px; margin-bottom: 2px; padding-left: 2px;">'.htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8').'</div>';
                } else {
                    $output[] = '<div class="font-semibold text-emerald-300 text-xs mt-2 mb-1 pl-1 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span> '.htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8').'</div>';
                }
                continue;
            }

            // Regular paragraph
            if ($isPdf) {
                $output[] = '<p style="margin: 4px 0; text-align: justify; line-height: 1.45; font-size: 10.5px;">'.htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8').'</p>';
            } else {
                $output[] = '<p class="my-1.5 text-xs text-justify text-slate-300 leading-relaxed">'.htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8').'</p>';
            }
        }

        $flushList();

        return implode("\n", $output);
    }

    /**
     * Normalizes text by placing inline bullets and section headers on their own lines.
     */
    public static function normalizeClauseText(string $text): string
    {
        $text = trim($text);

        // 1. Break before bullet characters (•, *, -, etc.)
        $text = preg_replace('/(?<=\S)\s+(?=[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}\x{25CF}])/u', "\n", $text);

        // 2. Break before main role sections like "VENDEDOR —", "COMPRADOR —", "PARTE VENDEDORA:", "1. PARTE...", "2. PARTE...", "3. MARCO..."
        $text = preg_replace('/(?<=\S)\s+(?=(?:\d+\.\s+)?(?:PARTE\s+)?(?:VENDEDORA?|COMPRADORA?|ARRENDADOR|ARRENDATARIO|PRESTAMISTA|PRESTATARIO)\s*[—\-–:])/u', "\n", $text);

        // 3. Break before subheaders like "a) DERECHOS:", "b) OBLIGACIONES:" or standalone "OBLIGACIONES:", "DERECHOS:" when preceded by another sentence
        $text = preg_replace('/(?<=\S)\s+(?=[a-d]\)\s*(?:DERECHOS|OBLIGACIONES):?)/ui', "\n", $text);
        $text = preg_replace('/(?<=[a-z0-9\.\»])\s+(?=(?:DERECHOS|OBLIGACIONES|MARCO LEGAL|REFERENCIAS LEGALES):)/ui', "\n", $text);

        return $text;
    }
}
