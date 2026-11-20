<?php

class Texte
{
    public function apercu(string $html, int $longueur): array
    {
        // Préserve la séparation des paragraphes et cellules avant de retirer le balisage.
        $html = preg_replace('~<\s*/?\s*(?:p|div|br|li|tr|td|th|h[1-6])\b[^>]*>~i', ' ', $html);
        $texte = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texte = trim(preg_replace('/\s+/u', ' ', $texte) ?? $texte);
        if (mb_strlen($texte) <= $longueur) {
            return [$texte, false];
        }

        $debut = mb_substr($texte, 0, $longueur);
        if (mb_substr($texte, $longueur, 1) !== ' ' && ($espace = mb_strrpos($debut, ' ')) !== false) {
            $debut = mb_substr($debut, 0, $espace);
        }
        return [rtrim($debut) . '…', true];
    }
}
