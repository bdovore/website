<?php

require_once dirname(__DIR__, 2) . '/inc/account_security.php';
require_once dirname(__DIR__, 2) . '/inc/account_login.php';
require_once dirname(__DIR__, 2) . '/config/mobile.php';

/**
 *
 * @author laurent
 *
 */
class User extends Bdo_Db_Line
{

    /**
     */
    public $table_name = 'users';

    public $CARRE_TYPE = 0;

    public $error = '';

    public $a_roleGlobal = array();

    // initialisation
    public function __construct ($id = null)
    {
        if (is_array($id)) {
            $a_data = $id;
        }
        else {
            $a_data = array(
                    'user_id' => $id
            );
        }
        parent::__construct($this->table_name, $a_data);
    }

    public function select ()
    {
        return "SELECT
                `user_id` ,
                `last_connect` ,
                `nb_connect` ,
                `username` ,
                `password` ,
                `level` ,
                `email` ,
                `birthday` ,
                `location` ,
                `image` ,
                `OPEN_COLLEC` ,
                `MSG_COLLEC` ,
                `CARRE_TYPE` ,
                `ROW_DISPLAY` ,
                `ABT_NEWS` ,
                `VAL_ALB` ,
                `VAL_INT` ,
                `VAL_COF` ,
                `VAL_COF_TYPE` ,
                `ROWSERIE` ,
                `PREF_EXPORT` , 
                API_TOKEN,
                DATE_TOKEN,
                LAST_UPDATE,
                EXPLICIT_CONTENT,
                password_v2
        FROM `" . $this->table_name . "`
 ";
    }

    public function search ($a_data = array())
    {
        // --------------------------------------------------------------------
        // -------- Champs selectionnés par defaut --------
        if (empty($a_data)) $a_data = $_POST;
        if (! isset($a_data['validSubmitSearch'])) {
            $a_data['ch_user_id'] = "checked";
            $a_data['ch_username'] = "checked";
            $a_data['ch_email'] = "checked";
        }

        $dbSearch = new Bdo_Db_Search();

        $dbSearch->select = "
SELECT
                `user_id` ,
                `last_connect` ,
                `nb_connect` ,
                `username` ,
                `level` ,
                `email` ,
                `birthday` ,
                `location` ,
                `image` ,
                `OPEN_COLLEC` ,
                `MSG_COLLEC` ,
                `CARRE_TYPE` ,
                `ROW_DISPLAY` ,
                `ABT_NEWS` ,
                `VAL_ALB` ,
                `VAL_INT` ,
                `VAL_COF` ,
                `VAL_COF_TYPE` ,
                `ROWSERIE` ,
                `PREF_EXPORT`
";

        // dans les tables
        $dbSearch->from = "
FROM " . $this->table_name . "
";

        $dbSearch->where = "WHERE 1";

        // dans l'ordre
        if ($a_data['daff'] == "") $a_data['daff'] = "0";
        if ($a_data['sens_tri'] == "") $a_data['sens_tri'] = "ASC";
        if ($a_data['col_tri'] == "") $a_data['col_tri'] = $this->table_name . ".username";

        $dbSearch->groupby = "";

        // --------------=======================----------------
        $dbSearch->infoQuery();
        // --------------=======================----------------
        $dbSearch->integreData($a_data);
        // --------------=======================----------------
        if (isset($_GET['export'])) {
            $dbSearch->execNoLimit();
        }
        else {
            $dbSearch->exec();
        }

        return $dbSearch;
    }

    function autoLogin ()
    {
        // Explicit API credentials take precedence over any ambient website cookie.
        $bearer = AccountSecurity::bearer($_SERVER);
        if ($bearer !== null) {
            if (!BDO_MOBILE_API_ENABLED) $this->rejectApiSession(503, 'api_unavailable', 'Le service est temporairement indisponible.');
            if (!$bearer) $this->rejectApiSession();
            require_once __DIR__ . '/MobileAccount.php';
            try {
                $context = (new MobileAccount())->session($bearer);
            } catch (MobileAccountException $e) {
                $this->rejectApiSession();
            } catch (Throwable $e) {
                error_log('[mobile-account] authentication_unavailable');
                $this->rejectApiSession(503, 'api_unavailable', 'Le service est temporairement indisponible.');
            }
            // Keep the identity request-local: revoking a Bearer cannot leave a web session behind.
            if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
            $_SESSION['userConnect'] = $context['user'];
            $this->set_data($context['user']);
            return $this;
        }
        if (isset($_GET['API_TOKEN'])) {
            if (!is_string($_GET['API_TOKEN']) || !$this->verifyToken($_GET['API_TOKEN'])) $this->rejectApiSession();
            $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];
            $this->set_data($_SESSION['userConnect']);
            return $this;
        }
        // Refresh website permissions/profile and invalidate pre-password-change sessions.
        if (!empty($_SESSION['userConnect']->user_id)) {
            $previous = $_SESSION['userConnect'];
            $this->load('c', 'WHERE user_id = ' . intval($previous->user_id));
            if ($this->dbSelect->nbLineResult !== 1 || intval($this->level) >= 98
                || !hash_equals(AccountSecurity::stamp($previous), AccountSecurity::stamp($this))) {
                $this->guest();
            } else {
                $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];
            }
        }
        // deconnexion demandee
        if ((isset($_POST['submitLoginConnect']) || isset($_POST['submitLoginDisconnect'])) && !adminValidateCsrf('account-auth', $_POST['csrf_token'] ?? '')) {
            $this->error = 'Formulaire expiré. Rechargez la page de connexion.';
            if (empty($_SESSION['userConnect']->user_id)) $this->guest();
            return $this;
        }
        if (isset($_POST['submitLoginDisconnect'])) {
            $this->logout();
        }

        // Retire the password-as-cookie mechanism, including the shared 'migrated' sentinel.
        if (isset($_COOKIE['pass'])) {
            setcookie('pass', '', array('expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Lax'));
            unset($_COOKIE['pass']);
        }
        // connexion demandee
        if (isset($_POST['submitLoginConnect'])) {
            if (BDO_MOBILE_API_ENABLED) {
                require_once __DIR__ . '/MobileAccount.php';
                try {
                    $security = new MobileAccount();
                    $security->throttle('login-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 60, 900);
                    $security->throttle('login-user', mb_strtolower((string) ($_POST['user_login'] ?? ''), 'UTF-8'), 15, 900);
                } catch (MobileAccountException $e) { http_response_code($e->status); exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')); }
            }

            // -----------
            // absence de cookie indique la non acceptation des cookies par le
            // navigateur
            if (isset($_COOKIE['PHPSESSID'])) {
                // -----------
                try {
                    $id = AccountLogin::resolve(Bdo_Cfg::getVar('connexion'), $_POST['user_login'] ?? '', $_POST['user_pseudo'] ?? '');
                } catch (AccountLoginAmbiguous $e) {
                    $this->loginNeedsPseudo = true;
                    $this->error = $e->getMessage();
                    AccountSecurity::dummyPasswordCheck($_POST['user_password'] ?? '');
                    $this->guest();
                    return $this;
                }
                $this->load('c', 'WHERE user_id = ' . intval($id));

                if (1 == $this->dbSelect->nbLineResult) {

                    $authenticated = false;

                    $authenticated = $this->verifyAndMigratePassword($_POST['user_password'] ?? null);

                    if ($authenticated) {
                        if ($this->level < 98) {

                            session_regenerate_id(true);
                            $_SESSION['userConnect'] = $this->dbSelect->a_dataQuery[0];
                            $_SESSION['account_authenticated_at'] = time();

                            $this->addNbConnect();

                            // défini les paramètres de cookie
                            setcookie("username", $this->username, time() + 31104000, "/");
                            $referer = $_SERVER['HTTP_REFERER'] ?? BDO_URL;
                            $base = parse_url(BDO_URL);
                            $target = parse_url($referer);
                            // Restrict post-login redirects to the website's origin.
                            if (!$target || ($target['host'] ?? '') !== ($base['host'] ?? '') || ($target['scheme'] ?? '') !== ($base['scheme'] ?? '') || strpos($referer, 'inscription?act=post') !== false) $referer = BDO_URL;
                            if (($_POST['account_login_form'] ?? '') !== 'floating'
                                || ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
                                header('Location: ' . $referer);
                            }
                        }
                        else {
                            $this->error = 'Identifiant ou mot de passe invalide';
                        }
                    }
                    else {
                        $this->error = 'Identifiant ou mot de passe invalide';
                    }
                }
                else {
                    AccountSecurity::dummyPasswordCheck($_POST['user_password'] ?? '');
                    $this->error = 'Identifiant ou mot de passe invalide';
                }
            }
            else {
                $this->error = 'Vous devez accepter les cookies pour vous connecter.';
            }
        }
        if ($this->error && isset($_POST['submitLoginConnect']) && empty($_POST['user_pseudo'])
            && AccountLogin::emailAttempt($_POST['user_login'] ?? '', ($this->dbSelect->nbLineResult ?? 0) === 1 ? $this : null)) {
            $this->loginNeedsPseudo = true;
            $this->error = AccountLogin::PSEUDO_REQUIRED;
        }
        if ($this->error || empty($_SESSION['userConnect']->user_id)) {
            $this->guest();
        }
        if (array_key_exists("userConnect",$_SESSION)) {
             foreach ((array) $_SESSION['userConnect'] as $var => $value) {
                $this->$var = $value;
            }
        }
       
        return $this;
    }

    public function addNbConnect ()
    {
        $a_data = array(
                "user_id" => $this->user_id,
                "nb_connect" => ($this->nb_connect + 1),
                "last_connect" => date("Y-m-d H:i:s")
        );

        Db_query('UPDATE users SET nb_connect = nb_connect + 1, last_connect = NOW(), LAST_UPDATE = LAST_UPDATE WHERE user_id = ' . intval($this->user_id));
    }

    public function guest ()
    {
        // niveau d'acces publique
        unset($_SESSION['user']);
        unset($_SESSION['userConnect']);

        $user = new stdClass();
        $user->level = 5;
        $user->user_id = null;
        $user->username = null;
        $_SESSION['userConnect'] = $user;
        $this->user_id = null;
        $this->level = 5;
        $this->username = null;
    }

    public function logout()
    {
        unset($_SESSION['social_identity'], $_SESSION['social_registration'], $_SESSION['account_authenticated_at']);
        $this->guest();

        // On efface le cookie
        if (isset($_COOKIE["pass"])) unset($_COOKIE["pass"]);
        setcookie("pass", "", time() - 3600, "/"); // Note: time() - 3600 pour supprimer le cookie

       
        echo '<script type="text/javascript">
                if (window !== window.top) {
                    // Si on est dans un iframe, on recharge le parent
                    window.parent.location.reload();
                } else {
                    // Sinon, on redirige normalement
                    window.location.href = "' . BDO_URL . '";
                }
            </script>';
       
        Bdo_Cfg::quit();
    }


    public static function minAccesslevel ($level = 5)
    {
        if (! isset($_SESSION['userConnect']->level) or ($_SESSION['userConnect']->level > $level)) {
            return false;
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    public function isAllowed ($resPriv)
    {
        // acces super administrateur
        if (User::minAccesslevel(0)) {
            return true;
        }
        // connaitre les roles
        if (empty($a_idAclRole)) {

            // tableau des roles
            $a_idAclRole[] = $_SESSION['userConnect']->level;
        }

        $acl = Bdo_Cfg::getVar('acl');

        $a_respriv = explode('.', $resPriv);
        $resource = $a_respriv[0];
        $privilege = $a_respriv[1];

        if (! $acl->isResourcePrivilege($resource, $privilege)) {
            return false;
        }

        foreach ($a_idAclRole as $id_acl_role) {
            //echo '<br/>'.$id_acl_role.'-'.$resource.'-'.$privilege;
            if ($acl->isAllowed($id_acl_role, $resource, $privilege)) {
                // echo ' OK';
                return true;
            }
        }

        return false;
    }

    public function is_openCollection ()
    {
        if ($this->OPEN_COLLEC != 'Y') {
            return false;
        }
        else {
            return true;
        }
    }

    public function setForumAccount($username,$password, $email) {
        $connexion = Db_connect(array(
                'login' => FORUM_DB_USER,
                'password' => FORUM_DB_PWD,
                'sid' => FORUM_DB_SID,
                'server' => FORUM_DB_HOST));

        if ($connexion === false) {
            error_log('[account] forum_unavailable');
            return;
        }

        
        $username = Db_Escape_String($username);
        $email = Db_Escape_String($email);
       
        // on vérifit que le compte n'existe pas déjà
        $verif = "SELECT count(*) AS nb FROM bb3_users WHERE username_clean=lower('" . $username . "')";
        $result = Db_query($verif,$connexion);
        $o = Db_fetch_object($result);
        if ($o->nb == 0) {
            // le compte n'existe pas : 2022 on va appeler une api custom du forum pour la création
            $url = BDO_URL_FORUM."bdovoreUser.php";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array("username"=>$username, "email" => $email, "password" => $password));
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);   
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);         
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $response = curl_exec($ch);
            // Check the return value of curl_exec(), too
            if ($response === false) {
                $error = curl_error($ch);
                $erno = curl_errno($ch);
                return $error;
                //throw new Exception(curl_error($ch), curl_errno($ch));
            }
            Db_close($connexion);
        }
    }

    public function hasForumAccount($username) {
         $connexion = Db_connect(array(
                'login' => FORUM_DB_USER,
                'password' => FORUM_DB_PWD,
                'sid' => FORUM_DB_SID,
                'server' => FORUM_DB_HOST));
        // on vérifit que le compte n'existe pas déjà
        $verif = "SELECT count(*) AS nb FROM bb3_users WHERE username_clean=lower('" . $username . "')";
        $result = Db_query($verif,$connexion);
        $o = Db_fetch_object($result);
        if ($o->nb == 0) {
            return false;
        } else {
            return true;
        }
    }

    public static function deleteAllDataForUser($profile_user_id) {
        /*
         * Suppression de toute les données d'un utilisateur
         */

        $user_id = intval($profile_user_id);
        require_once __DIR__ . '/SocialAccount.php';
        SocialAccount::forgetUser(Bdo_Cfg::getVar('connexion'), $user_id);
        if (BDO_MOBILE_API_ENABLED) {
            require_once __DIR__ . '/MobileAccount.php';
            (new MobileAccount())->revokeAll($user_id);
            Db_query('DELETE FROM mobile_challenges WHERE user_id=' . $user_id);
            Db_query('DELETE t FROM mobile_web_tickets t JOIN mobile_sessions s ON s.id=t.session_id WHERE s.user_id=' . $user_id);
            Db_query('DELETE FROM mobile_sessions WHERE user_id=' . $user_id);
        }

        Db_query("UPDATE `bd_edition` SET `USER_ID`=NULL WHERE `user_id`='" . $user_id . "'");
        Db_query("UPDATE `bd_edition` SET `VALIDATOR`=NULL WHERE `VALIDATOR`='" . $user_id . "'");
        Db_query("UPDATE `users_alb_prop` SET `VALIDATOR`=NULL WHERE `VALIDATOR`='" . $user_id . "'");

        // Para-BD keeps the shared catalogue and audit trail, but removes
        // personal data and anonymises the deleted member's contributions.
        Db_query("UPDATE `parabd_item` SET `CREATED_BY`=NULL WHERE `CREATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_item` SET `UPDATED_BY`=NULL WHERE `UPDATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_identifier` SET `CREATED_BY`=NULL WHERE `CREATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_media` SET `CREATED_BY`=NULL WHERE `CREATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_source` SET `CREATED_BY`=NULL WHERE `CREATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_item_author` SET `CREATED_BY`=NULL WHERE `CREATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_item_series` SET `CREATED_BY`=NULL WHERE `CREATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_item_tome` SET `CREATED_BY`=NULL WHERE `CREATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_revision` SET `AUTHOR_ID`=NULL WHERE `AUTHOR_ID`='" . $user_id . "'");
        Db_query("UPDATE `parabd_revision` SET `VALIDATED_BY`=NULL WHERE `VALIDATED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_discussion` SET `AUTHOR_ID`=NULL WHERE `AUTHOR_ID`='" . $user_id . "'");
        Db_query("UPDATE `parabd_discussion` SET `HIDDEN_BY`=NULL WHERE `HIDDEN_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_duplicate` SET `RESOLVED_BY`=NULL WHERE `RESOLVED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_report` SET `RESOLVED_BY`=NULL WHERE `RESOLVED_BY`='" . $user_id . "'");
        Db_query("UPDATE `parabd_user_profile` SET `TRUST_OVERRIDE_BY`=NULL WHERE `TRUST_OVERRIDE_BY`='" . $user_id . "'");
        Db_query("DELETE FROM `parabd_revision_vote` WHERE `USER_ID`='" . $user_id . "'");
        Db_query("DELETE FROM `parabd_report` WHERE `REPORTER_ID`='" . $user_id . "'");
        Db_query("DELETE FROM `users_parabd` WHERE `USER_ID`='" . $user_id . "'");
        Db_query("DELETE FROM `parabd_user_profile` WHERE `USER_ID`='" . $user_id . "'");

        // dans tout les cas
        Db_query("DELETE FROM `serie_comment` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_album` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_exclusions` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_list_aut` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_list_carre` WHERE `user_id`='" . $user_id . "'");
        Db_query("DELETE FROM `users_comment` WHERE `USER_ID`='" . $user_id . "'");
        Db_query("DELETE FROM `users_alb_prop` WHERE `USER_ID`='" . $user_id . "'");
        Db_query("DELETE FROM `users` WHERE `user_id`='" . $user_id . "'");
    }

    public function getUserList($search,$exactmatch=false,$max=10) {

        $username = Db_Escape_String($search);

        if ($exactmatch)
            $where = " WHERE username LIKE '" . $username . "'";
        else
            $where = " WHERE username LIKE '%" . $username . "%'";

        $orderby = " ORDER BY username";
        $limit = " LIMIT 0, " . intval($max);

        return $this->load("c", $where . $orderby . $limit);

    }

    public function countUserBy($type, $id) {


        $query = '';
        switch ($type) {
            case "edition" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                WHERE
                    ua.id_edition=" . intval($id) . "
                ";
                    break;
                }
            case "tome" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                WHERE
                    en.id_tome=" . intval($id) . "
                ";
                    break;
                }
            case "collection" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                WHERE
                    en.id_collection=" . intval($id) . "
                ";
                    break;
                }
            case "editeur" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_collection c ON en.id_collection = c.id_collection

                WHERE
                    c.id_editeur=" . intval($id) . "
                ";
                    break;
                }
            case "serie" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                WHERE
                    t.id_serie=" . intval($id) . "
                ";
                    break;
                }
            case "genre" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                    INNER JOIN bd_serie s ON t.id_serie = s.id_serie
                WHERE
                    s.id_genre=" . intval($id) . "
                ";
                    break;
                }
            case "auteur" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(ua.user_id)) as nbr
                FROM
                    users_album ua
                    INNER JOIN bd_edition en ON en.id_edition=ua.id_edition
                    INNER JOIN bd_tome t ON t.id_tome = en.id_tome
                WHERE
                    t.id_scenar = " . intval($id) . "
                    or t.id_dessin = " . intval($id) . "
                    or t.id_scenar_alt = " . intval($id) . "
                    or t.id_dessin_alt = " . intval($id) . "
                    or t.id_color = " . intval($id) . "
                    or t.id_color_alt = " . intval($id) . "
                ";
                    break;
                }
            case "tomeComment" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(uc.user_id)) as nbr
                FROM
                    users_comment uc
                WHERE
                    uc.id_tome=" . intval($id) . "
                ";
                    break;
                }
            case "serieComment" :
                {
                    $query = "
                SELECT COUNT(DISTINCT(sc.user_id)) as nbr
                FROM
                    serie_comment sc
                WHERE
                    sc.id_serie=" . intval($id) . "
                ";
                    break;
                }
        }
        if (! empty ( $query )) {
                    $result = Db_query( $query );
            $o = Db_fetch_object($result);
            return $o->nbr;
        }
        return false;
    }

    public function getToken($username, $password, $pseudo = '') {
        try { $id = AccountLogin::resolve(Bdo_Cfg::getVar('connexion'), $username, $pseudo); }
        catch (AccountLoginAmbiguous $e) { AccountSecurity::dummyPasswordCheck($password); return array('Token' => null, 'Timestamp' => null, 'Error' => $e->getMessage()); }
        $this->load('c', 'WHERE user_id = ' . intval($id));
        $error = "";
        $API_TOKEN = null;
        if (1 == $this->dbSelect->nbLineResult) {

            $authenticated = false;

            $authenticated = $this->verifyAndMigratePassword($password);

            if ($authenticated) {
                if ($this->level < 98) {

                   $API_TOKEN = $this->user_id . '-' . bin2hex(random_bytes(32));
                   $this->DATE_TOKEN =date('Y-m-d H:i:s');
                   $a_data = array(
                       "user_id" => $this->user_id,
                        "API_TOKEN" => md5($API_TOKEN) ,
                        "DATE_TOKEN" =>  date('Y-m-d H:i:s')
                    );

                   $issued = Db_query("UPDATE users SET API_TOKEN='" . Db_Escape_String($a_data['API_TOKEN']) . "', DATE_TOKEN='" . Db_Escape_String($a_data['DATE_TOKEN']) . "'
                       WHERE user_id=" . intval($this->user_id) . " AND COALESCE(password_v2, '')='" . Db_Escape_String($this->password_v2 ?? '') . "' AND password='" . Db_Escape_String($this->password) . "'");
                   if (!$issued || Db_affected_rows() !== 1) { $API_TOKEN = null; $error = 'Identifiant ou Mot de passe de connexion invalide'; }
                }
                else {
                    $error = 'Compte bloqué';
                }
            }
            else {
                $error = 'Identifiant ou Mot de passe de connexion invalide';
            }
        }
        else {
            AccountSecurity::dummyPasswordCheck($password);
            $error = 'Identifiant ou Mot de passe de connexion invalide';
        }
        
        if ($error !== '' && $pseudo === '' && AccountLogin::emailAttempt($username, $this->dbSelect->nbLineResult === 1 ? $this : null)) $error = AccountLogin::PSEUDO_REQUIRED;
        return (array("Token" => $API_TOKEN, "Timestamp" => $this->LAST_UPDATE ?? null, "Error" => $error));
    }
    
    public function verifyToken ($token) {
        if (!is_string($token) || !preg_match('/^([1-9][0-9]*)-([a-f0-9]+)$/D', $token)) return false;
        $tab = explode("-", $token, 2);
        $user_id = $tab[0];
        $this->load('c', "WHERE user_id =" . Db_Escape_String($user_id) . "");
         if (1 == $this->dbSelect->nbLineResult) {
             if (intval($this->level) < 98 && hash_equals((string) $this->API_TOKEN, md5($token))) {
                 if ($this->DATE_TOKEN ) {
                     // todo : rendre le token temporaire
                     return ($user_id);
                 } else {
                     $this->error = "Token périmé";
                 }
             } else {
                 $this->error = "Token invalide";
             }
         } else {
              $this->error = "Utilisateur inconnu";
         }
    }

    private function verifyAndMigratePassword($password)
    {
        if (!AccountSecurity::verifyPassword($password, $this)) return false;
        if (empty($this->password_v2)) {
            $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
            if (!Db_query("UPDATE users SET password_v2='" . Db_Escape_String($hash) . "', password='migrated', LAST_UPDATE=LAST_UPDATE WHERE user_id=" . intval($this->user_id) . " AND COALESCE(password_v2, '')='' AND password='" . Db_Escape_String($this->password) . "'")) return false;
            $this->load('c', 'WHERE user_id = ' . intval($this->user_id));
            if (!AccountSecurity::verifyPassword($password, $this)) return false;
        }
        return true;
    }

    private function rejectApiSession($status = 401, $code = 'invalid_session', $message = 'Session invalide. Veuillez vous reconnecter.')
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode(array('data' => null, 'error' => array('code' => $code, 'message' => $message)));
        exit;
    }
}
