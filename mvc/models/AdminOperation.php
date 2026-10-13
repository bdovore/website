<?php

/**
 * Journal des operations sensibles de l'admin du catalogue (specification section 7).
 * Une ligne decrit une fusion, un split ou une suppression multi-etapes :
 * statut PREPARED / APPLIED / VERIFIED / FAILED, etape courante, snapshot,
 * progression et erreur interne. Les ecritures passent par des requetes
 * explicites pour maitriser chaque transition d'etat.
 */
class AdminOperation extends Bdo_Db_Line
{
    const STATUS_PREPARED = 'PREPARED';
    const STATUS_APPLIED = 'APPLIED';
    const STATUS_VERIFIED = 'VERIFIED';
    const STATUS_FAILED = 'FAILED';

    public $table_name = 'admin_operation';

    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array('ID_ADMIN_OPERATION' => $id);
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        return "SELECT `ID_ADMIN_OPERATION`, `OP_TYPE`, `SOURCE_ENTITY`, `SOURCE_ID`,
                       `TARGET_ENTITY`, `TARGET_ID`, `IDEMPOTENCY_KEY`, `STATUS`,
                       `CURRENT_STEP`, `SNAPSHOT`, `PROGRESSION`, `ERROR_CODE`,
                       `ERROR_MESSAGE`, `ID_USER`, `CREATED_DTE`, `STARTED_DTE`,
                       `UPDATED_DTE`, `FINISHED_DTE`
                FROM `$this->table_name` ";
    }

    public function getById ($id)
    {
        $resultat = Db_query("SELECT * FROM `" . $this->table_name . "`
                              WHERE `ID_ADMIN_OPERATION` = " . intval($id));
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        return $row;
    }

    public function getByIdempotencyKey ($key)
    {
        $resultat = Db_query("SELECT * FROM `" . $this->table_name . "`
                              WHERE `IDEMPOTENCY_KEY` = '" . Db_Escape_String($key) . "'");
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        return $row;
    }

    /**
     * Operations actives (PREPARED ou APPLIED) ciblant l'une des entites donnees,
     * pour refuser deux traitements concurrents incompatibles.
     */
    public function findActiveByEntity ($entity, $a_ids)
    {
        $a_ids = array_values(array_unique(array_map('intval', $a_ids)));
        if (empty($a_ids)) return array();
        $in = implode(',', $a_ids);
        $entity = Db_Escape_String($entity);
        $resultat = Db_query("SELECT * FROM `" . $this->table_name . "`
                              WHERE `STATUS` IN ('" . self::STATUS_PREPARED . "','" . self::STATUS_APPLIED . "')
                                AND ((`SOURCE_ENTITY` = '" . $entity . "' AND `SOURCE_ID` IN (" . $in . "))
                                  OR (`TARGET_ENTITY` = '" . $entity . "' AND `TARGET_ID` IN (" . $in . ")))");
        $a_rows = tabOfFetchObj($resultat);
        return $a_rows;
    }

    /**
     * Cree l'operation PREPARED et son snapshot. Retourne l'identifiant.
     */
    public function createPrepared ($opType, $sourceEntity, $sourceId, $targetEntity, $targetId,
                                   $idempotencyKey, $snapshotJson, $userId)
    {
        Db_query("INSERT INTO `" . $this->table_name . "`
                    (`OP_TYPE`, `SOURCE_ENTITY`, `SOURCE_ID`, `TARGET_ENTITY`, `TARGET_ID`,
                     `IDEMPOTENCY_KEY`, `STATUS`, `SNAPSHOT`, `PROGRESSION`, `ID_USER`,
                     `CREATED_DTE`, `STARTED_DTE`, `UPDATED_DTE`)
                  VALUES ('" . Db_Escape_String($opType) . "',
                          '" . Db_Escape_String($sourceEntity) . "', " . intval($sourceId) . ",
                          '" . Db_Escape_String($targetEntity) . "', " . intval($targetId) . ",
                          '" . Db_Escape_String($idempotencyKey) . "',
                          '" . self::STATUS_PREPARED . "',
                          '" . Db_Escape_String($snapshotJson) . "',
                          '" . Db_Escape_String(json_encode(array('steps' => array()))) . "',
                          " . intval($userId) . ",
                          '" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "', '" . date('Y-m-d H:i:s') . "')");
        return Db_insert_id();
    }

    /**
     * Met a jour l'etape courante et la progression (compteurs des etapes).
     */
    public function recordStep ($id, $stepCode, $progressionJson)
    {
        Db_query("UPDATE `" . $this->table_name . "`
                  SET `CURRENT_STEP` = '" . Db_Escape_String($stepCode) . "',
                      `PROGRESSION` = '" . Db_Escape_String($progressionJson) . "',
                      `UPDATED_DTE` = '" . date('Y-m-d H:i:s') . "'
                  WHERE `ID_ADMIN_OPERATION` = " . intval($id));
    }

    /**
     * Transition de statut de l'operation.
     */
    public function setStatus ($id, $status, $finished = false)
    {
        $now = date('Y-m-d H:i:s');
        $finishedDte = $finished ? ", `FINISHED_DTE` = '" . $now . "'" : '';
        Db_query("UPDATE `" . $this->table_name . "`
                  SET `STATUS` = '" . Db_Escape_String($status) . "'" . $finishedDte . ",
                      `UPDATED_DTE` = '" . $now . "'
                  WHERE `ID_ADMIN_OPERATION` = " . intval($id));
    }

    /**
     * Journalise l'echec : code et message techniques, jamais affiches tels quels.
     */
    public function markFailed ($id, $errorCode, $errorMessage)
    {
        Db_query("UPDATE `" . $this->table_name . "`
                  SET `STATUS` = '" . self::STATUS_FAILED . "',
                      `ERROR_CODE` = '" . Db_Escape_String($errorCode) . "',
                      `ERROR_MESSAGE` = '" . Db_Escape_String($errorMessage) . "',
                      `FINISHED_DTE` = '" . date('Y-m-d H:i:s') . "',
                      `UPDATED_DTE` = '" . date('Y-m-d H:i:s') . "'
                  WHERE `ID_ADMIN_OPERATION` = " . intval($id));
    }

    /**
     * Reprise explicite d'une operation FAILED : retour a PREPARED apres un nouveau
     * preflight, erreur effacee, reprise journalisee dans la progression.
     */
    public function beginResume ($id, $progressionJson)
    {
        Db_query("UPDATE `" . $this->table_name . "`
                  SET `STATUS` = '" . self::STATUS_PREPARED . "',
                      `ERROR_CODE` = NULL,
                      `ERROR_MESSAGE` = NULL,
                      `FINISHED_DTE` = NULL,
                      `PROGRESSION` = '" . Db_Escape_String($progressionJson) . "',
                      `STARTED_DTE` = '" . date('Y-m-d H:i:s') . "',
                      `UPDATED_DTE` = '" . date('Y-m-d H:i:s') . "'
                  WHERE `ID_ADMIN_OPERATION` = " . intval($id));
    }
}
