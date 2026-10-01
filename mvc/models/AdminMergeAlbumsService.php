<?php

require_once __DIR__ . '/AdminOperationException.php';
if (!class_exists('Bdo_Db_Line')) require_once dirname(__DIR__, 2) . '/library/Bdo/Db/Line.php';
require_once __DIR__ . '/AdminOperation.php';

/**
 * Fusion d'albums pilote (Lot B de la specification de fiabilisation de l'admin).
 *
 * Preflight sans mutation, verrou applicatif base de donnees, journal
 * admin_operation et execution par etapes rejouables : chaque etape possede
 * un code stable, relit l'etat reel avant de s'executer et peut etre ignoree
 * lors d'une reprise si sa postcondition est deja satisfaite.
 *
 * Regle de deduplication des collections (specification 9.4) : lorsqu'un membre
 * possede deja une ligne sur l'album conserve (commentaire, selection, exclusion),
 * la ligne de l'album conserve est conservee et la ligne source est supprimee,
 * comptee comme dedupliquee. Aucune donnee personnelle de l'album conserve
 * n'est ecrasee.
 *
 * Les references des collections (users_album) pointent des editions dont
 * l'identifiant ne change pas lors d'une fusion d'albums : la regle deterministe
 * est de conserver le rattachement a l'edition, qui rejoint l'album conserve.
 */
class AdminMergeAlbumsService
{
    const OP_TYPE = 'MERGE_ALBUM';
    const ENTITY = 'album';
    const LOCK_NAME = 'bdovore_admin_catalogue';
    const LOCK_TIMEOUT = 5;

    /**
     * Hooks de test : code d'etape => liste de callables, appeles au debut de
     * l'etape. Sert a simuler une panne apres une etape ou un etat incoherent
     * sans modifier le service.
     */
    public static $hooksBeforeStep = array();

    private $operationModel;

    public function __construct ()
    {
        $this->operationModel = new AdminOperation();
    }

    // --------------------------------------------- verrou applicatif

    /**
     * Verrou base de donnees : une seule operation destructive de catalogue
     * a la fois. L'attente est courte ; faux si le verrou est indisponible.
     */
    public function acquireLock ()
    {
        $resultat = Db_query("SELECT GET_LOCK('" . self::LOCK_NAME . "', " . intval(self::LOCK_TIMEOUT) . ") AS locked");
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        return $row && 1 === intval($row->locked);
    }

    public function releaseLock ()
    {
        $resultat = Db_query("SELECT RELEASE_LOCK('" . self::LOCK_NAME . "') AS released");
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        return $row && 1 === intval($row->released);
    }

    // --------------------------------------------- preflight

    /**
     * Controle sans mutation : existence et distinction des albums, edition
     * par defaut utilisable, impacts comptes, absence d'operation active
     * incompatible sur ces albums. Retourne un tableau descriptif.
     */
    public function preflight ($sourceId, $destId)
    {
        $sourceId = intval($sourceId);
        $destId = intval($destId);
        $this->validateIds($sourceId, $destId);

        $source = $this->getAlbum($sourceId);
        if (!$source) {
            throw new AdminOperationException("L'album &agrave; supprimer est introuvable.", 404, 'SOURCE_NOT_FOUND');
        }
        $dest = $this->getAlbum($destId);
        if (!$dest) {
            throw new AdminOperationException("L'album &agrave; conserver est introuvable.", 404, 'TARGET_NOT_FOUND');
        }

        $impacts = array(
            'editions' => $this->countQuery("SELECT COUNT(*) AS nb FROM bd_edition WHERE ID_TOME = " . $sourceId),
            'comments' => $this->countQuery("SELECT COUNT(*) AS nb FROM users_comment WHERE ID_TOME = " . $sourceId),
            'selections' => $this->countQuery("SELECT COUNT(*) AS nb FROM users_list_carre WHERE id_tome = " . $sourceId),
            'exclusions' => $this->countQuery("SELECT COUNT(*) AS nb FROM users_exclusions WHERE id_tome = " . $sourceId),
            'collection_refs' => $this->countQuery("SELECT COUNT(*) AS nb FROM users_album ua
                                                     INNER JOIN bd_edition e ON e.ID_EDITION = ua.id_edition
                                                     WHERE e.ID_TOME = " . $sourceId)
        );

        $conflicts = array(
            'comments' => $this->countQuery("SELECT COUNT(*) AS nb FROM users_comment sc
                                             INNER JOIN users_comment dc ON dc.USER_ID = sc.USER_ID AND dc.ID_TOME = " . $destId . "
                                             WHERE sc.ID_TOME = " . $sourceId),
            'selections' => $this->countQuery("SELECT COUNT(*) AS nb FROM users_list_carre sc
                                               INNER JOIN users_list_carre dc ON dc.user_id = sc.user_id AND dc.id_tome = " . $destId . "
                                               WHERE sc.id_tome = " . $sourceId),
            'exclusions' => $this->countQuery("SELECT COUNT(*) AS nb FROM users_exclusions sc
                                               INNER JOIN users_exclusions dc ON dc.user_id = sc.user_id AND dc.id_tome = " . $destId . "
                                               WHERE sc.id_tome = " . $sourceId)
        );

        $defaultEdition = $this->getDefaultEdition($destId);
        if (!$defaultEdition) {
            // specification 9.2 : la cible doit posseder une edition par defaut utilisable.
            // L'etape ENSURE_DEFAULT_EDITION reste une securite en cours d'execution,
            // pas un mode de reparation silencieux au preflight.
            throw new AdminOperationException("L'album &agrave; conserver n'a pas d'&eacute;dition par d&eacute;faut utilisable.", 422, 'TARGET_DEFAULT_EDITION');
        }

        $idempotencyKey = $this->idempotencyKey($sourceId, $destId);
        $a_active = $this->operationModel->findActiveByEntity(self::ENTITY, array($sourceId, $destId));
        foreach ($a_active as $active) {
            if ($active->IDEMPOTENCY_KEY !== $idempotencyKey) {
                throw new AdminOperationException("Une autre op&eacute;ration administrative est en cours sur ces albums.", 409, 'ACTIVE_OPERATION');
            }
        }

        return array(
            'source' => $source,
            'target' => $dest,
            'default_edition' => $defaultEdition,
            'impacts' => $impacts,
            'conflicts' => $conflicts,
            'idempotency_key' => $idempotencyKey
        );
    }

    /**
     * Preflight reduit d'une reprise : la source a pu deja etre supprimee par
     * l'operation elle-meme, son existence n'est donc plus exigee. La cible
     * doit exister et aucune autre operation active ne doit viser ces albums.
     */
    private function preflightForResume ($sourceId, $destId, $existing)
    {
        $dest = $this->getAlbum(intval($destId));
        if (!$dest) {
            throw new AdminOperationException("L'album &agrave; conserver est introuvable.", 404, 'TARGET_NOT_FOUND');
        }
        $a_active = $this->operationModel->findActiveByEntity(self::ENTITY, array(intval($sourceId), intval($destId)));
        foreach ($a_active as $active) {
            if ($active->IDEMPOTENCY_KEY !== $existing->IDEMPOTENCY_KEY) {
                throw new AdminOperationException("Une autre op&eacute;ration administrative est en cours sur ces albums.", 409, 'ACTIVE_OPERATION');
            }
        }
    }

    // --------------------------------------------- execution

    /**
     * Execute la fusion sous verrou (acquis par l'appelant) :
     * preflight rejoue, operation journalisee PREPARED et son snapshot,
     * puis etapes rejouables jusqu'a VERIFIED.
     *
     * Une double soumission retourne l'operation existante au lieu d'en
     * creer une seconde. Une operation FAILED ou interrompue est reprise
     * explicitement : preflight reduit puis rejeu des etapes non satisfaites.
     */
    public function execute ($sourceId, $destId, $userId)
    {
        $sourceId = intval($sourceId);
        $destId = intval($destId);
        $this->validateIds($sourceId, $destId);

        $existing = $this->operationModel->getByIdempotencyKey($this->idempotencyKey($sourceId, $destId));
        if ($existing && $existing->STATUS === AdminOperation::STATUS_VERIFIED) {
            return $existing;
        }

        if ($existing) {
            // Reprise : la source a pu deja disparaitre (etape destructive passee),
            // le preflight complet n'est donc pas rejouable tel quel. Seuls la cible
            // et l'absence d'operation concurrente sont verifies ; chaque etape
            // relit ensuite l'etat reel avant de s'executer.
            $this->preflightForResume($sourceId, $destId, $existing);
            $progression = $this->decodeProgression($existing->PROGRESSION);
            $progression['resumes'][] = array(
                'dte' => date('Y-m-d H:i:s'),
                'id_user' => intval($userId),
                'previous_status' => $existing->STATUS
            );
            $this->operationModel->beginResume($existing->ID_ADMIN_OPERATION, $this->encodeProgression($progression));
            $op = $this->operationModel->getById($existing->ID_ADMIN_OPERATION);
        }
        else {
            $preflight = $this->preflight($sourceId, $destId);
            $snapshot = array(
                'source' => $this->albumSummary($preflight['source']),
                'target' => $this->albumSummary($preflight['target']),
                'source_editions' => $this->sourceEditions($sourceId),
                'impacts' => $preflight['impacts'],
                'preflight_dte' => date('Y-m-d H:i:s')
            );
            $id = $this->operationModel->createPrepared(
                self::OP_TYPE, self::ENTITY, $sourceId, self::ENTITY, $destId,
                $this->idempotencyKey($sourceId, $destId), json_encode($snapshot), $userId);
            $op = $this->operationModel->getById($id);
            if (!$op) {
                throw new AdminOperationException("L'op&eacute;ration n'a pas pu &ecirc;tre journalis&eacute;e.", 500, 'SNAPSHOT_UNAVAILABLE');
            }
        }

        try {
            $this->runSteps($op);
        }
        catch (AdminOperationException $e) {
            $this->operationModel->markFailed($op->ID_ADMIN_OPERATION, $e->errorCode(), $e->getMessage());
            throw new AdminOperationException($e->getMessage(), $e->httpStatus(), $e->errorCode(), $op->ID_ADMIN_OPERATION, $e);
        }
        catch (Exception $e) {
            error_log('AdminMergeAlbumsService operation #' . $op->ID_ADMIN_OPERATION . ' : ' . $e->getMessage());
            $this->operationModel->markFailed($op->ID_ADMIN_OPERATION, 'TECHNICAL_ERROR', $e->getMessage());
            throw new AdminOperationException(
                "La fusion n'a pas pu &ecirc;tre men&eacute;e &agrave; terme. Consultez l'op&eacute;ration n&deg;" . $op->ID_ADMIN_OPERATION . ".",
                500, 'TECHNICAL_ERROR', $op->ID_ADMIN_OPERATION, $e);
        }

        return $this->operationModel->getById($op->ID_ADMIN_OPERATION);
    }

    /**
     * Reprise explicite d'une operation journalisee a partir de son identifiant.
     */
    public function resume ($operationId, $userId)
    {
        $op = $this->operationModel->getById(intval($operationId));
        if (!$op || $op->OP_TYPE !== self::OP_TYPE) {
            throw new AdminOperationException("Op&eacute;ration introuvable.", 404, 'OPERATION_NOT_FOUND');
        }
        if ($op->STATUS !== AdminOperation::STATUS_FAILED) {
            throw new AdminOperationException("Seule une op&eacute;ration en &eacute;chec peut &ecirc;tre reprise.", 409, 'RESUME_NOT_ALLOWED');
        }
        return $this->execute(intval($op->SOURCE_ID), intval($op->TARGET_ID), $userId);
    }

    public function getOperation ($operationId)
    {
        return $this->operationModel->getById(intval($operationId));
    }

    // --------------------------------------------- etapes

    private function runSteps ($op)
    {
        $a_stepMethods = array(
            'MOVE_COMMENTS' => 'stepMoveComments',
            'MOVE_SELECTIONS' => 'stepMoveSelections',
            'MOVE_EXCLUSIONS' => 'stepMoveExclusions',
            'MOVE_EDITIONS' => 'stepMoveEditions',
            'ENSURE_DEFAULT_EDITION' => 'stepEnsureDefaultEdition',
            'CHECK_POSTCONDITIONS' => 'stepCheckPostconditions',
            'DELETE_SOURCE' => 'stepDeleteSource',
            'VERIFY_FINAL' => 'stepVerifyFinal'
        );
        $progression = $this->decodeProgression($op->PROGRESSION);
        foreach ($a_stepMethods as $step => $method) {
            $this->runHooks($step, $op);
            $progression = $this->$method($op, $progression);
            $this->operationModel->recordStep($op->ID_ADMIN_OPERATION, $step, $this->encodeProgression($progression));
            $op->CURRENT_STEP = $step;
        }
        return $progression;
    }

    /**
     * Deplace les commentaires vers l'album conserve. UPDATE IGNORE laisse en
     * place les lignes d'un membre ayant deja un commentaire sur l'album conserve :
     * la ligne source est supprimee et comptee dedupliquee, la ligne de l'album
     * conserve n'est jamais ecrasee.
     */
    private function stepMoveComments ($op, $progression)
    {
        $sourceId = intval($op->SOURCE_ID);
        $destId = intval($op->TARGET_ID);
        $remaining = $this->countQuery("SELECT COUNT(*) AS nb FROM users_comment WHERE ID_TOME = " . $sourceId);
        if (0 === $remaining) {
            if (!isset($progression['steps']['MOVE_COMMENTS'])) {
                $progression['steps']['MOVE_COMMENTS'] = array('moved' => 0, 'deduplicated' => 0, 'skipped' => true, 'dte' => date('Y-m-d H:i:s'));
            }
            return $progression;
        }
        Db_query("UPDATE IGNORE users_comment SET ID_TOME = " . $destId . " WHERE ID_TOME = " . $sourceId);
        $moved = Db_affected_rows();
        $deduplicated = $this->countQuery("SELECT COUNT(*) AS nb FROM users_comment WHERE ID_TOME = " . $sourceId);
        if (0 < $deduplicated) {
            Db_query("DELETE FROM users_comment WHERE ID_TOME = " . $sourceId);
        }
        $progression['steps']['MOVE_COMMENTS'] = array('moved' => $moved, 'deduplicated' => $deduplicated, 'dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    /** Deplace les selections (carres) avec la meme regle de deduplication. */
    private function stepMoveSelections ($op, $progression)
    {
        $sourceId = intval($op->SOURCE_ID);
        $destId = intval($op->TARGET_ID);
        $remaining = $this->countQuery("SELECT COUNT(*) AS nb FROM users_list_carre WHERE id_tome = " . $sourceId);
        if (0 === $remaining) {
            if (!isset($progression['steps']['MOVE_SELECTIONS'])) {
                $progression['steps']['MOVE_SELECTIONS'] = array('moved' => 0, 'deduplicated' => 0, 'skipped' => true, 'dte' => date('Y-m-d H:i:s'));
            }
            return $progression;
        }
        Db_query("UPDATE IGNORE users_list_carre SET id_tome = " . $destId . " WHERE id_tome = " . $sourceId);
        $moved = Db_affected_rows();
        $deduplicated = $this->countQuery("SELECT COUNT(*) AS nb FROM users_list_carre WHERE id_tome = " . $sourceId);
        if (0 < $deduplicated) {
            Db_query("DELETE FROM users_list_carre WHERE id_tome = " . $sourceId);
        }
        $progression['steps']['MOVE_SELECTIONS'] = array('moved' => $moved, 'deduplicated' => $deduplicated, 'dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    /** Deplace les exclusions avec la meme regle de deduplication. */
    private function stepMoveExclusions ($op, $progression)
    {
        $sourceId = intval($op->SOURCE_ID);
        $destId = intval($op->TARGET_ID);
        $remaining = $this->countQuery("SELECT COUNT(*) AS nb FROM users_exclusions WHERE id_tome = " . $sourceId);
        if (0 === $remaining) {
            if (!isset($progression['steps']['MOVE_EXCLUSIONS'])) {
                $progression['steps']['MOVE_EXCLUSIONS'] = array('moved' => 0, 'deduplicated' => 0, 'skipped' => true, 'dte' => date('Y-m-d H:i:s'));
            }
            return $progression;
        }
        Db_query("UPDATE IGNORE users_exclusions SET id_tome = " . $destId . " WHERE id_tome = " . $sourceId);
        $moved = Db_affected_rows();
        $deduplicated = $this->countQuery("SELECT COUNT(*) AS nb FROM users_exclusions WHERE id_tome = " . $sourceId);
        if (0 < $deduplicated) {
            Db_query("DELETE FROM users_exclusions WHERE id_tome = " . $sourceId);
        }
        $progression['steps']['MOVE_EXCLUSIONS'] = array('moved' => $moved, 'deduplicated' => $deduplicated, 'dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    /**
     * Rattache les editions conservees a l'album cible. La table bd_edition n'a
     * pas de contrainte unique sur ID_TOME : un UPDATE simple suffit, et tout
     * rattachement incomplet est une erreur et non un succes partiel silencieux.
     */
    private function stepMoveEditions ($op, $progression)
    {
        $sourceId = intval($op->SOURCE_ID);
        $destId = intval($op->TARGET_ID);
        $remaining = $this->countQuery("SELECT COUNT(*) AS nb FROM bd_edition WHERE ID_TOME = " . $sourceId);
        if (0 === $remaining) {
            if (!isset($progression['steps']['MOVE_EDITIONS'])) {
                $progression['steps']['MOVE_EDITIONS'] = array('moved' => 0, 'skipped' => true, 'dte' => date('Y-m-d H:i:s'));
            }
            return $progression;
        }
        Db_query("UPDATE bd_edition SET ID_TOME = " . $destId . " WHERE ID_TOME = " . $sourceId);
        $moved = Db_affected_rows();
        $remaining = $this->countQuery("SELECT COUNT(*) AS nb FROM bd_edition WHERE ID_TOME = " . $sourceId);
        if (0 < $remaining) {
            throw new AdminOperationException(
                "Des &eacute;ditions n'ont pas pu &ecirc;tre rattach&eacute;es &agrave; l'album conserv&eacute;.",
                500, 'EDITION_MOVE_INCOMPLETE', intval($op->ID_ADMIN_OPERATION));
        }
        $progression['steps']['MOVE_EDITIONS'] = array('moved' => $moved, 'dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    /**
     * Garantit une edition par defaut valide a l'album conserve. Regle
     * deterministe : si l'edition par defaut est absente ou invalide, la plus
     * petite edition de l'album conserve est designee.
     */
    private function stepEnsureDefaultEdition ($op, $progression)
    {
        $destId = intval($op->TARGET_ID);
        if ($this->getDefaultEdition($destId)) {
            if (!isset($progression['steps']['ENSURE_DEFAULT_EDITION'])) {
                $progression['steps']['ENSURE_DEFAULT_EDITION'] = array('assigned' => false, 'dte' => date('Y-m-d H:i:s'));
            }
            return $progression;
        }
        $resultat = Db_query("SELECT MIN(ID_EDITION) AS min_id FROM bd_edition WHERE ID_TOME = " . $destId);
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        if (!$row || null === $row->min_id) {
            throw new AdminOperationException(
                "L'album &agrave; conserver n'a pas d'&eacute;dition par d&eacute;faut utilisable.",
                422, 'TARGET_DEFAULT_EDITION', intval($op->ID_ADMIN_OPERATION));
        }
        Db_query("UPDATE bd_tome SET ID_EDITION = " . intval($row->min_id) . " WHERE ID_TOME = " . $destId);
        $progression['steps']['ENSURE_DEFAULT_EDITION'] = array('assigned_id_edition' => intval($row->min_id), 'dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    /** Verifie les postconditions puis passe l'operation a APPLIED. */
    private function stepCheckPostconditions ($op, $progression)
    {
        $a_failures = $this->verifyPostconditions($op);
        if (!empty($a_failures)) {
            throw new AdminOperationException(
                "La fusion ne peut pas &ecirc;tre finalis&eacute;e : " . implode(', ', $a_failures) . ".",
                422, 'POSTCONDITION_INVALID', intval($op->ID_ADMIN_OPERATION));
        }
        $this->operationModel->setStatus(intval($op->ID_ADMIN_OPERATION), AdminOperation::STATUS_APPLIED);
        $op->STATUS = AdminOperation::STATUS_APPLIED;
        $progression['steps']['CHECK_POSTCONDITIONS'] = array('dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    /**
     * Supprime l'album source, seule etape destructive sur les donnees,
     * strictement apres validation des postconditions. Idempotent : une
     * reprise trouve la source deja supprimee et poursuit.
     */
    private function stepDeleteSource ($op, $progression)
    {
        $sourceId = intval($op->SOURCE_ID);
        $a_failures = $this->verifyPostconditions($op);
        if (!empty($a_failures)) {
            throw new AdminOperationException(
                "Suppression de l'album source refus&eacute;e : " . implode(', ', $a_failures) . ".",
                422, 'POSTCONDITION_BEFORE_DELETE', intval($op->ID_ADMIN_OPERATION));
        }
        $remaining = $this->countQuery("SELECT COUNT(*) AS nb FROM bd_tome WHERE ID_TOME = " . $sourceId);
        if (0 === $remaining) {
            if (!isset($progression['steps']['DELETE_SOURCE'])) {
                $progression['steps']['DELETE_SOURCE'] = array('skipped' => true, 'dte' => date('Y-m-d H:i:s'));
            }
            return $progression;
        }
        Db_query("DELETE FROM bd_tome WHERE ID_TOME = " . $sourceId);
        if (1 !== Db_affected_rows()) {
            throw new AdminOperationException(
                "L'album source n'a pas pu &ecirc;tre supprim&eacute;.",
                500, 'SOURCE_DELETE_INCOMPLETE', intval($op->ID_ADMIN_OPERATION));
        }
        $progression['steps']['DELETE_SOURCE'] = array('dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    /** Verification finale : postconditions et source absente, puis VERIFIED. */
    private function stepVerifyFinal ($op, $progression)
    {
        $sourceId = intval($op->SOURCE_ID);
        $a_failures = $this->verifyPostconditions($op);
        if (!empty($a_failures)) {
            throw new AdminOperationException(
                "La fusion ne peut pas &ecirc;tre finalis&eacute;e : " . implode(', ', $a_failures) . ".",
                422, 'POSTCONDITION_INVALID', intval($op->ID_ADMIN_OPERATION));
        }
        if (0 < $this->countQuery("SELECT COUNT(*) AS nb FROM bd_tome WHERE ID_TOME = " . $sourceId)) {
            throw new AdminOperationException(
                "L'album source est encore pr&eacute;sent.",
                500, 'SOURCE_NOT_DELETED', intval($op->ID_ADMIN_OPERATION));
        }
        $this->operationModel->setStatus(intval($op->ID_ADMIN_OPERATION), AdminOperation::STATUS_VERIFIED, true);
        $op->STATUS = AdminOperation::STATUS_VERIFIED;
        $progression['steps']['VERIFY_FINAL'] = array('dte' => date('Y-m-d H:i:s'));
        return $progression;
    }

    // --------------------------------------------- postconditions

    /**
     * Postconditions de la fusion (specification 9.5), evaluees sur l'etat
     * reel de la base, jamais sur les compteurs enregistres.
     */
    private function verifyPostconditions ($op)
    {
        $sourceId = intval($op->SOURCE_ID);
        $destId = intval($op->TARGET_ID);
        $a_failures = array();

        if (0 < $this->countQuery("SELECT COUNT(*) AS nb FROM bd_edition WHERE ID_TOME = " . $sourceId)) {
            $a_failures[] = 'une &eacute;dition rattache &agrave; la source';
        }
        if (0 < $this->countQuery("SELECT COUNT(*) AS nb FROM users_comment WHERE ID_TOME = " . $sourceId)) {
            $a_failures[] = 'un commentaire rattache &agrave; la source';
        }
        if (0 < $this->countQuery("SELECT COUNT(*) AS nb FROM users_list_carre WHERE id_tome = " . $sourceId)) {
            $a_failures[] = 'une s&eacute;lection rattache &agrave; la source';
        }
        if (0 < $this->countQuery("SELECT COUNT(*) AS nb FROM users_exclusions WHERE id_tome = " . $sourceId)) {
            $a_failures[] = 'une exclusion rattache &agrave; la source';
        }

        $dest = $this->getAlbum($destId);
        if (!$dest) {
            $a_failures[] = "l'album &agrave; conserver est introuvable";
        }
        elseif (!$this->getDefaultEdition($destId)) {
            $a_failures[] = "l'&eacute;dition par d&eacute;faut de l'album conserv&eacute; est invalide";
        }

        $snapshot = json_decode($op->SNAPSHOT, true);
        if (is_array($snapshot) && isset($snapshot['impacts']['collection_refs'])) {
            $now = $this->countQuery("SELECT COUNT(*) AS nb FROM users_album ua
                                      INNER JOIN bd_edition e ON e.ID_EDITION = ua.id_edition
                                      WHERE e.ID_TOME = " . $destId
                                     . " AND e.ID_EDITION IN (" . $this->snapshotEditionIds($snapshot) . ")");
            if ($now != intval($snapshot['impacts']['collection_refs'])) {
                $a_failures[] = 'des r&eacute;f&eacute;rences de collections sont perdues';
            }
        }

        return $a_failures;
    }

    // --------------------------------------------- helpers

    private function validateIds ($sourceId, $destId)
    {
        if ($sourceId <= 0 or $destId <= 0) {
            throw new AdminOperationException("Album &agrave; supprimer ou album &agrave; garder non d&eacute;fini.", 422, 'UNDEFINED_ALBUM');
        }
        if ($sourceId == $destId) {
            throw new AdminOperationException("Album &agrave; garder et album &agrave; supprimer identiques.", 422, 'SAME_ALBUM');
        }
    }

    private function idempotencyKey ($sourceId, $destId)
    {
        return self::OP_TYPE . ':' . intval($sourceId) . ':' . intval($destId);
    }

    private function getAlbum ($id)
    {
        $resultat = Db_query("SELECT * FROM bd_tome WHERE ID_TOME = " . intval($id) . " LIMIT 1");
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        return $row;
    }

    /**
     * Edition par defaut valide de l'album : ID_EDITION renseigne et
     * correspondant a une edition de cet album.
     */
    private function getDefaultEdition ($destId)
    {
        $destId = intval($destId);
        $resultat = Db_query("SELECT e.ID_EDITION
                              FROM bd_tome t
                              INNER JOIN bd_edition e ON e.ID_EDITION = t.ID_EDITION AND e.ID_TOME = t.ID_TOME
                              WHERE t.ID_TOME = " . $destId . " LIMIT 1");
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        return $row ? intval($row->ID_EDITION) : null;
    }

    private function albumSummary ($album)
    {
        return array(
            'id_tome' => intval($album->ID_TOME),
            'titre' => isset($album->TITRE) ? $album->TITRE : null,
            'num_tome' => isset($album->NUM_TOME) ? $album->NUM_TOME : null,
            'id_serie' => isset($album->ID_SERIE) ? intval($album->ID_SERIE) : null,
            'id_edition' => isset($album->ID_EDITION) ? intval($album->ID_EDITION) : null
        );
    }

    private function sourceEditions ($sourceId)
    {
        $resultat = Db_query("SELECT ID_EDITION, IMG_COUV FROM bd_edition WHERE ID_TOME = " . intval($sourceId) . " ORDER BY ID_EDITION");
        $a_editions = array();
        while ($row = Db_fetch_object($resultat)) {
            $a_editions[] = array('id_edition' => intval($row->ID_EDITION), 'img_couv' => $row->IMG_COUV);
        }
        Db_free_result($resultat);
        return $a_editions;
    }

    private function snapshotEditionIds ($snapshot)
    {
        $a_ids = array(0);
        if (isset($snapshot['source_editions']) && is_array($snapshot['source_editions'])) {
            foreach ($snapshot['source_editions'] as $edition) {
                if (!empty($edition['id_edition'])) $a_ids[] = intval($edition['id_edition']);
            }
        }
        return implode(',', array_unique($a_ids));
    }

    private function countQuery ($query)
    {
        $resultat = Db_query($query);
        $row = Db_fetch_object($resultat);
        Db_free_result($resultat);
        return $row ? intval($row->nb) : 0;
    }

    private function decodeProgression ($json)
    {
        $progression = json_decode((string) $json, true);
        if (!is_array($progression)) $progression = array();
        if (!isset($progression['steps']) || !is_array($progression['steps'])) $progression['steps'] = array();
        return $progression;
    }

    private function encodeProgression ($progression)
    {
        return json_encode($progression);
    }

    private function runHooks ($step, $op)
    {
        if (empty(self::$hooksBeforeStep[$step])) return;
        foreach (self::$hooksBeforeStep[$step] as $callback) {
            call_user_func($callback, $op);
        }
    }
}
