<?php

require_once __DIR__ . '/ParabdException.php';

/**
 * Pure Para-BD domain rules.
 *
 * This class deliberately has no database, session or filesystem dependency,
 * so normalisation and decision rules remain usable from the web application,
 * CLI imports and focused tests.
 */
class ParabdRules
{
    const GLOBAL_SCHEMES = 'EAN13,UPCA,ISBN10,ISBN13';

    public static function normalizeText($value)
    {
        $value = trim(mb_strtolower((string) $value, 'UTF-8'));
        if (class_exists('Normalizer')) $value = Normalizer::normalize($value, Normalizer::FORM_D);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii !== false) $value = $ascii;
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value);
        return trim(preg_replace('/\s+/', ' ', $value));
    }

    public static function normalizeIdentifier($scheme, $value)
    {
        $value = strtoupper(trim((string) $value));
        if (in_array($scheme, explode(',', self::GLOBAL_SCHEMES), true)) return preg_replace('/[^0-9X]/', '', $value);
        return preg_replace('/[^A-Z0-9]+/', '', $value);
    }

    public static function isValidIdentifier($scheme, $value)
    {
        $value = self::normalizeIdentifier($scheme, $value);
        if ($scheme === 'EAN13' || $scheme === 'ISBN13') {
            if (!preg_match('/^\d{13}$/', $value)) return false;
            $sum = 0;
            for ($i = 0; $i < 12; $i++) $sum += intval($value[$i]) * (($i % 2) ? 3 : 1);
            return ((10 - ($sum % 10)) % 10) === intval($value[12]);
        }
        if ($scheme === 'UPCA') {
            if (!preg_match('/^\d{12}$/', $value)) return false;
            $sum = 0;
            for ($i = 0; $i < 11; $i++) $sum += intval($value[$i]) * (($i % 2) ? 1 : 3);
            return ((10 - ($sum % 10)) % 10) === intval($value[11]);
        }
        if ($scheme === 'ISBN10') {
            if (!preg_match('/^\d{9}[\dX]$/', $value)) return false;
            $sum = 0;
            for ($i = 0; $i < 10; $i++) $sum += (10 - $i) * (($value[$i] === 'X') ? 10 : intval($value[$i]));
            return ($sum % 11) === 0;
        }
        return $value !== '';
    }

    public static function parsePartialDate($value)
    {
        $value = trim((string) $value);
        if ($value === '') return array('date' => null, 'precision' => 'UNKNOWN');
        if (preg_match('/^(\d{4})$/', $value, $match) && checkdate(1, 1, intval($match[1]))) return array('date' => $match[1] . '-01-01', 'precision' => 'YEAR');
        if (preg_match('/^(\d{4})-(\d{2})$/', $value, $match) && checkdate(intval($match[2]), 1, intval($match[1]))) return array('date' => $value . '-01', 'precision' => 'MONTH');
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $match) && checkdate(intval($match[2]), intval($match[3]), intval($match[1]))) return array('date' => $value, 'precision' => 'DAY');
        throw new ParabdException('VALIDATION_ERROR', 'La date est invalide.', array('release_date' => 'Format attendu : AAAA, AAAA-MM ou AAAA-MM-JJ.'));
    }

    public static function displayPartialDate($date, $precision)
    {
        if (!$date || $precision === 'UNKNOWN') return 'Date inconnue';
        if ($precision === 'YEAR') return substr($date, 0, 4);
        if ($precision === 'MONTH') return substr($date, 5, 2) . '/' . substr($date, 0, 4);
        return date('d/m/Y', strtotime($date));
    }

    public static function titleSimilarity($left, $right)
    {
        $left = self::normalizeText($left);
        $right = self::normalizeText($right);
        if ($left === '' || $right === '') return 0.0;
        similar_text($left, $right, $percent);
        return round($percent, 2);
    }

    private static function titleWordComparison($left, $right)
    {
        $ignored = array_flip(array(
            'a', 'au', 'aux', 'avec', 'ce', 'ces', 'dans', 'de', 'des', 'du', 'en', 'et', 'ex', 'la', 'le', 'les',
            'ou', 'par', 'pour', 'sans', 'sur', 'un', 'une',
            'buste', 'collector', 'collection', 'diorama', 'edition', 'editions', 'exclusif', 'exclusive', 'exclusivite',
            'figurine', 'impression', 'libris', 'objet', 'statue', 'statuette', 'tirage'
        ));
        $parse = function ($title) use ($ignored) {
            $tokens = array(); $numbers = array();
            foreach (explode(' ', self::normalizeText($title)) as $word) {
                if ($word === '') continue;
                if (ctype_digit($word)) {
                    $number = ltrim($word, '0');
                    $numbers[$number === '' ? '0' : $number] = true;
                } elseif (strlen($word) >= 3 && !isset($ignored[$word])) {
                    $tokens[$word] = true;
                }
            }
            $tokens = array_keys($tokens); $numbers = array_keys($numbers);
            sort($tokens); sort($numbers);
            return array($tokens, $numbers);
        };
        list($leftTokens, $leftNumbers) = $parse($left);
        list($rightTokens, $rightNumbers) = $parse($right);
        if (!$leftTokens || !$rightTokens) return array('close' => false, 'score' => 0.0, 'numbers_differ' => $leftNumbers !== $rightNumbers);

        $intersection = array_values(array_intersect($leftTokens, $rightTokens));
        $union = array_values(array_unique(array_merge($leftTokens, $rightTokens)));
        $sharedCount = count($intersection);
        $coverage = $sharedCount / min(count($leftTokens), count($rightTokens)) * 100;
        $jaccard = $sharedCount / count($union) * 100;
        $longestShared = 0;
        foreach ($intersection as $word) $longestShared = max($longestShared, strlen($word));
        $distinctive = $sharedCount >= 2 || $longestShared >= 7;
        $close = $distinctive && ($coverage >= 100 || ($coverage >= 75 && $jaccard >= 50));
        $score = round(($coverage + $jaccard) / 2, 2);
        if ($leftNumbers !== $rightNumbers) $score = min($score, 90.0);
        return array(
            'close' => $close,
            'score' => $score,
            'numbers_differ' => $leftNumbers !== $rightNumbers
        );
    }

    public static function duplicateLevel($candidate, $input)
    {
        if (!empty($input['exact_identifier'])) return array('level' => 'CERTAIN', 'score' => 100, 'reasons' => array('Identifiant exact'));
        $similarity = self::titleSimilarity($candidate['TITLE'], $input['TITLE']);
        $wordComparison = self::titleWordComparison($candidate['TITLE'], $input['TITLE']);
        $sameRelation = !empty($input['common_relation']);
        $sameType = intval($candidate['TYPE_ID']) === intval($input['TYPE_ID']);
        $candidatePublisher = self::normalizeText(isset($candidate['PUBLISHER']) ? $candidate['PUBLISHER'] : '');
        $samePublisher = $candidatePublisher !== ''
            && $candidatePublisher === self::normalizeText(isset($input['PUBLISHER']) ? $input['PUBLISHER'] : '');
        if ($sameType && $similarity >= 90 && $sameRelation) {
            return array('level' => 'STRONG', 'score' => $similarity, 'reasons' => array('Titre très proche', 'Rattachement commun'));
        }
        if ($sameType && $similarity > 80 && ($samePublisher || $sameRelation)) {
            return array('level' => 'POSSIBLE', 'score' => $similarity, 'reasons' => array('Titre proche', $samePublisher ? 'Même éditeur' : 'Rattachement commun'));
        }
        if ($sameType && $wordComparison['close']) {
            $reasons = array('Noyau du titre commun');
            if ($wordComparison['numbers_differ']) $reasons[] = 'Numérotation différente';
            return array('level' => 'POSSIBLE', 'score' => max($similarity, $wordComparison['score']), 'reasons' => $reasons);
        }
        if ($sameType && $similarity >= 85) {
            return array('level' => 'POSSIBLE', 'score' => $similarity, 'reasons' => array('Titre presque identique'));
        }
        return null;
    }

    public static function defaultAuthorRole($author)
    {
        $rolesByFlag = array(
            'FLG_DESSIN' => 'ILLUSTRATOR',
            'FLG_DESIGN' => 'DESIGNER',
            'FLG_SCULPT' => 'SCULPTOR',
            'FLG_PEINT' => 'PAINTER'
        );
        foreach ($rolesByFlag as $flag => $role) {
            $value = is_array($author)
                ? (isset($author[$flag]) ? $author[$flag] : null)
                : (isset($author->$flag) ? $author->$flag : null);
            if (intval($value) === 1) return $role;
        }
        return '';
    }

    public static function calculateTrust($createdAt, $validatedContributions, $override = 'NONE', $now = null)
    {
        if ($override === 'REVOKE') return false;
        if ($override === 'GRANT') return true;
        $now = $now === null ? time() : intval($now);
        return strtotime($createdAt) <= strtotime('-1 year', $now) && intval($validatedContributions) >= 5;
    }

    public static function decimal($value)
    {
        if ($value === null || trim((string) $value) === '') return null;
        $value = str_replace(',', '.', $value);
        if (!is_numeric($value) || floatval($value) < 0) throw new ParabdException('VALIDATION_ERROR', 'Une valeur numérique est invalide.');
        return number_format(floatval($value), 2, '.', '');
    }

    public static function positiveInt($value)
    {
        if ($value === null || trim((string) $value) === '') return null;
        if (!ctype_digit((string) $value) || intval($value) < 0) throw new ParabdException('VALIDATION_ERROR', 'Une valeur entière est invalide.');
        return intval($value);
    }

    public static function tri($value)
    {
        if ($value === null || $value === '' || $value === 'unknown') return null;
        if ($value === true || in_array((string) $value, array('1', 'yes', 'Y'), true)) return 1;
        if ($value === false || in_array((string) $value, array('0', 'no', 'N'), true)) return 0;
        throw new ParabdException('VALIDATION_ERROR', 'Une valeur oui/non/inconnu est invalide.');
    }
}
