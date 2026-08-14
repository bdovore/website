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

    public static function duplicateLevel($candidate, $input)
    {
        if (!empty($input['exact_identifier'])) return array('level' => 'CERTAIN', 'score' => 100, 'reasons' => array('Identifiant exact'));
        $similarity = self::titleSimilarity($candidate['TITLE'], $input['TITLE']);
        $sameRelation = !empty($input['common_relation']);
        $sameType = intval($candidate['TYPE_ID']) === intval($input['TYPE_ID']);
        $sameManufacturer = self::normalizeText($candidate['MANUFACTURER']) !== ''
            && self::normalizeText($candidate['MANUFACTURER']) === self::normalizeText(isset($input['MANUFACTURER']) ? $input['MANUFACTURER'] : '');
        $year = !empty($candidate['RELEASE_DATE']) && !empty($input['RELEASE_DATE'])
            && substr($candidate['RELEASE_DATE'], 0, 4) === substr($input['RELEASE_DATE'], 0, 4);
        $dimensions = self::dimensionsMatch($candidate, $input);
        if ($sameType && $similarity >= 85 && $sameManufacturer && ($year || $dimensions || $sameRelation)) {
            return array('level' => 'STRONG', 'score' => $similarity, 'reasons' => array('Titre très proche', 'Même fabricant', $year ? 'Même année' : ($dimensions ? 'Dimensions proches' : 'Rattachement commun')));
        }
        if ($similarity >= 70) return array('level' => 'POSSIBLE', 'score' => $similarity, 'reasons' => array('Titre proche'));
        return null;
    }

    private static function dimensionsMatch($left, $right)
    {
        $checked = 0;
        foreach (array('WIDTH_MM', 'HEIGHT_MM', 'DEPTH_MM') as $field) {
            if (!empty($left[$field]) && !empty($right[$field])) {
                $checked++;
                if (abs(floatval($left[$field]) - floatval($right[$field])) / max(floatval($left[$field]), 1) > 0.05) return false;
            }
        }
        return $checked > 0;
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
