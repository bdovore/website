<?php

require_once __DIR__ . '/ParabdException.php';

/**
 * Common base for Para-BD table models.
 *
 * Row persistence remains provided by Bdo_Db_Line. These protected helpers
 * only cover the custom joined and atomic queries that Bdo_Db_Line cannot
 * express (FOR UPDATE, bulk merge, conditional revision updates).
 */
abstract class ParabdDbLine extends Bdo_Db_Line
{
    protected function connection()
    {
        return Bdo_Cfg::getVar('connexion');
    }

    protected function escape($value)
    {
        return Db_Escape_String((string) $value, $this->connection());
    }

    protected function sqlValue($value, $emptyAsNull = true)
    {
        if ($value === null || ($emptyAsNull && $value === '')) return 'NULL';
        return "'" . $this->escape($value) . "'";
    }

    protected function executeQuery($sql)
    {
        $result = Db_query($sql, $this->connection());
        if ($result === false) throw new RuntimeException($this->connection()->error ?: 'Erreur SQL Para-BD');
        return $result;
    }

    protected function fetchOneQuery($sql)
    {
        $result = $this->executeQuery($sql);
        $row = Db_fetch_array($result);
        Db_free_result($result);
        return $row ?: null;
    }

    protected function fetchAllQuery($sql)
    {
        $result = $this->executeQuery($sql);
        $rows = array();
        while ($row = Db_fetch_array($result)) $rows[] = $row;
        Db_free_result($result);
        return $rows;
    }

    public function persist(array $data)
    {
        $this->set_dataPaste($data);
        $this->update();
        if (!empty($this->error)) {
            $messages = is_array($this->error) ? $this->error : array($this->error);
            throw new ParabdException('VALIDATION_ERROR', implode(' ', array_filter($messages)) ?: 'Données Para-BD invalides.');
        }
        return $this->insert_id ? intval($this->insert_id) : null;
    }
}

