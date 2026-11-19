<?php

/**
 *
 * @author Tom
 *
 */
require_once BDO_DIR . "inc" . DS . "mail.php";

class Compte extends Bdo_Controller {

    public function Connexion()
    {
        // Même session et mêmes contrôles que le formulaire classique, sans navigation.
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
            && postVal('account_login_form', '') === 'floating') {
            $user = Bdo_Cfg::user();
            $authenticated = User::minAccesslevel(2) && !$user->error;
            $needsPseudo = !$authenticated && !empty($user->loginNeedsPseudo);
            http_response_code($authenticated ? 200 : ($needsPseudo ? 409 : 401));
            header_remove('Location');
            header('Cache-Control: no-store');
            header('X-Content-Type-Options: nosniff');
            $this->view->layout = 'accountjson';
            $this->view->a_phtmlFile = array('BODY' => 'compte/connexionjson');
            $this->view->set_var('json', json_encode(array('authenticated' => $authenticated, 'needs_pseudo' => $needsPseudo,
                'message' => $authenticated ? '' : (is_string($user->error) && $user->error !== '' ? $user->error : 'Identifiant ou mot de passe invalide'))));
            $this->view->render();
            return;
        }
        if (User::minAccesslevel(2)) { header('Location: ' . BDO_URL); return; }
        $this->renderLogin();
    }

    private function renderLogin()
    {
        $user = Bdo_Cfg::user();
        $this->view->layout = 'account';
        $this->view->a_phtmlFile = array('BODY' => 'compte/connexion');
        $this->view->set_var(array('PAGETITLE' => 'Connexion à BDovore', 'login_error' => is_string($user->error) ? $user->error : '',
            'login_identifier' => is_string(postVal('user_login', '')) ? postVal('user_login', '') : '', 'login_pseudo' => is_string(postVal('user_pseudo', '')) ? postVal('user_pseudo', '') : '',
            'login_needs_pseudo' => !empty($user->loginNeedsPseudo)));
        $this->view->render();
    }
    public function Initialpassword()
    {
        $this->mutationGuard();
        if (!User::minAccesslevel(2) || ($_SESSION['account_authenticated_at'] ?? 0) < time() - 300) {
            $this->accountFailure('Reconnectez-vous avec Google ou Apple avant de définir votre mot de passe.'); return;
        }
        require_once dirname(__DIR__) . '/models/MobileAccount.php';
        try {
            if (postVal('txtpass1') !== postVal('txtpass2')) throw new MobileAccountException('Les mots de passe ne correspondent pas.');
            $service = new MobileAccount();
            $service->initialPassword($service->user($_SESSION['userConnect']->user_id), array('password' => postVal('txtpass1')));
            echo GetMetaTag(2, 'Votre mot de passe est défini. Reconnectez-vous avec votre pseudo ou Google/Apple.', BDO_URL . 'compte/connexion');
        } catch (MobileAccountException $e) { $this->accountFailure($e->getMessage()); }
    }

    private function mutationGuard()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !adminValidateCsrf('account', postVal('csrf_token', ''))) {
            http_response_code(403);
            exit('Formulaire expiré. Rechargez votre profil puis réessayez.');
        }
    }

    private function accountFailure($message)
    {
        http_response_code(422);
        $this->view->layout = 'account';
        $this->view->a_phtmlFile = array('BODY' => 'compte/resultat');
        $action = strtolower((string) Bdo_Cfg::getVar('action'));
        $retry = in_array($action, array('inscription', 'forgotpass'), true) ? 'compte/' . $action : 'compte';
        $this->view->set_var(array('account_message' => $message, 'account_error' => true, 'account_result_target' => $retry, 'account_result_link' => 'Revenir au formulaire'));
        $this->view->render();
    }

    private function pendingConfirmation($purpose, $result)
    {
        $_SESSION['account_confirmation'] = array('purpose' => $purpose, 'challenge_id' => $result['challenge_id']);
        $this->view->layout = 'account';
        $this->view->a_phtmlFile = array('BODY' => 'compte/confirmation');
        $this->view->set_var(array('purpose' => $purpose, 'confirmation_error' => ''));
        $this->view->render();
    }

    protected function accountService()
    {
        require_once dirname(__DIR__) . '/models/MobileAccount.php';
        return new MobileAccount();
    }

    public function Confirmation()
    {
        $this->view->layout = 'account';
        $this->view->a_phtmlFile = array('BODY' => 'compte/confirmation');
        if (empty($_SESSION['account_confirmation'])) {
            $this->accountFailure('Confirmation expirée. Recommencez votre demande.');
            return;
        }
        require_once dirname(__DIR__) . '/models/MobileAccount.php';
        $pending = $_SESSION['account_confirmation'];
        $error = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->mutationGuard();
            try {
                $service = $this->accountService();
                $service->throttle('verify-ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown', 30, 900);
                $input = array('challenge_id' => $pending['challenge_id'], 'code' => postVal('code', ''), 'password' => postVal('password', ''));
                if ($pending['purpose'] === 'register') {
                    $user = $service->verifyRegistration($input);
                    try { (new User())->setForumAccount($user->username, bin2hex(random_bytes(24)), $user->email); }
                    catch (Throwable $e) { error_log('[account] forum_unavailable'); }
                } elseif ($pending['purpose'] === 'reset') {
                    if ($input['password'] !== postVal('password_confirmation', '')) throw new MobileAccountException('Les mots de passe ne correspondent pas.');
                    $service->resetPassword($input);
                } elseif ($pending['purpose'] === 'email' && User::minAccesslevel(2)) {
                    $service->confirmEmail($service->user($_SESSION['userConnect']->user_id), $input);
                } else { throw new MobileAccountException('Confirmation invalide.'); }
                unset($_SESSION['account_confirmation']);
                $emailConfirmed = $pending['purpose'] === 'email';
                $this->view->a_phtmlFile = array('BODY' => 'compte/resultat');
                $this->view->set_var(array('account_error' => false,
                    'account_message' => $emailConfirmed ? 'Confirmation effectuée. Votre adresse e-mail est vérifiée et peut maintenant servir à vous connecter.' : 'Confirmation effectuée. Vous pouvez maintenant vous connecter.',
                    'account_result_target' => $emailConfirmed ? 'compte' : 'compte/connexion',
                    'account_result_link' => $emailConfirmed ? 'Revenir au profil' : 'Me connecter'));
                $this->view->render();
                return;
            } catch (MobileAccountException $e) { $error = $e->getMessage(); }
        }
        $this->view->set_var(array('purpose' => $pending['purpose'], 'confirmation_error' => $error));
        $this->view->render();
    }

    /**
     */
    public function Index() {
        /*
         * Affichage du profil ou de l'écran de connexion
         */
        $mobile = getVal("mobile",""); // Conserve le contexte du navigateur mobile après les formulaires.
        $profileUrl = BDO_URL . 'compte' . ($mobile === 'T' ? '?mobile=T' : '');
        if (User::minAccesslevel(2)) {
            $user_id = getVal("user_id", "");
            $act=getVal("act","");
            if (in_array($act, array('update', 'delete', 'newpass', 'setforum'), true)) $this->mutationGuard();
            $this->loadModel("User");
            if ($user_id != "" && $user_id != $_SESSION["userConnect"]->user_id) {
                //un username et un userid ont été passés via l'URL
                //On vérifie que l'utilisateur est authorisé à ouvrir cette page

                $this->User->set_dataPaste(array("user_id" =>$user_id ));
                $this->User->load();
                $currentstatus = $this->User->level;
                $username = $this->User->username;

                if ($currentstatus <= $_SESSION["userConnect"]->level) {
                    echo GetMetaTag(3, "Vous n'avez pas les authorisations n&eacute;cessaire pour afficher cette page.", (BDO_URL ));
                    exit();
                } else {
                    $profile_user_id = $user_id;
                    $profile_user_username = $username;
                }
            } else {
                $profile_user_id = $_SESSION["userConnect"]->user_id;
                $profile_user_username = $_SESSION["userConnect"]->username;
            }


            // Mettre à jour les informations

            if ($act == "update") {
                require_once dirname(__DIR__) . '/models/MobileAccount.php';
                try {
                    $service = $this->accountService();
                    $user = $service->user($profile_user_id);
                    $email = postVal('txtemail', '');
                    if (!is_string($email)) throw new MobileAccountException('Adresse e-mail invalide.');
                    $verifyEmail = trim($email) !== $user->email || postVal('verify_email', '') === '1';
                    if ($verifyEmail && intval($profile_user_id) !== intval($_SESSION['userConnect']->user_id)) {
                        throw new MobileAccountException("Le titulaire du compte doit confirmer son adresse depuis son propre profil.", 403);
                    }
                    // E-mail is never written by the profile update, including for administrators.
                    if ($verifyEmail) {
                        $result = $service->requestEmail($user, array('email' => $email, 'current_password' => postVal('current_password', '')));
                    }
                    $service->updateProfile($user, array('location' => postVal('txtlocation', $user->location ?? ''), 'birthday' => postVal('txtanniv', $user->birthday ?? ''),
                        'open_collection' => postVal('lstOpenCollec') === 'Y', 'explicit_content' => postVal('lstAdultContent') === '1',
                        'newsletter' => postVal('txtNewsletter', null) === null ? intval($user->ABT_NEWS) === 1 : postVal('txtNewsletter') === 'checked', 'square_type' => intval(postVal('lstCarre', 0))));
                    if ($verifyEmail) $this->pendingConfirmation('email', $result);
                    else echo GetMetaTag(2, 'Votre profil a été mis à jour.', $profileUrl);
                } catch (MobileAccountException $e) { $this->accountFailure($e->getMessage()); }
                return;
            }


            // suppression du compte
            else if (($act == "delete") and (User::minAccesslevel(1) or $profile_user_id == $_SESSION["userConnect"]->user_id)) {
                $this->User->set_dataPaste(array("user_id" => $profile_user_id));
                $this->User->load();
                $useremail = $this->User->email;
                $username = $this->User->username;

                // si user correcteur ou admin
                if ($this->User->level < 2) {
                    echo "Impossible de supprimer un compte admin. Modifiez le niveau du compte avant de faire cette action";
                    exit;
                }
                $actor = new User(intval($_SESSION['userConnect']->user_id));
                $actor->load();
                if (!AccountSecurity::verifyPassword(postVal('current_password', ''), $actor)) { $this->accountFailure('Mot de passe actuel invalide.'); return; }
                User::deleteAllDataForUser($profile_user_id);
                
                $mail_adress = $useremail;
                $mail_sujet = "BDOVORE - suppression de votre compte";
                $mail_entete = "From: BDoVore <no-reply@bdovore.com>";
                $mail_text = "Bonjour " . $username . ", \n\n";
                $mail_text .="Votre compte BDoVore a été supprimé.\n\n";
                $mail_text .="Votre compte sur le forum BDoVore n'a pas été supprimé.\nSi vous le désirez, merci de nous faire part des raisons ayant motivées votre départ.\n\n";
                $mail_text .="Nous respectons votre décision et esperons vous revoir trés bientot.\n\n";
                $mail_text .="L'équipe BDOVORE";

                bdovoreMail($mail_adress, $mail_sujet, $mail_text, $mail_entete);
                $this->User->logout();

                //echo "Votre profil BDoVore a été supprim&eacute; d&eacute;finitivement.";
            }


// Mofidifcation du mot de passe
            else if ($act == "newpass") {
                    $actor = new User(intval($_SESSION['userConnect']->user_id));
                    $actor->load();
                    if (!AccountSecurity::verifyPassword(postVal('current_password', ''), $actor)) { $this->accountFailure('Mot de passe actuel invalide.'); return; }
                // control du mot de pass et udpate
                    $newpass1 = postVal("txtpass1");
                    $newpass2 = postVal("txtpass2");
                    $validpassword = Checkpassword($newpass1);
                    if ($validpassword != 1) {
                        echo GetMetaTag(5, $validpassword . ' Vous allez etre redirig&eacute;.', ($profileUrl));
                    } elseif (($newpass1 != $newpass2) and ($validpassword == 1)) {
                        echo GetMetaTag(5, "Les mots de passes ne sont pas identiques. Vous allez etre redirig&eacute;", ($profileUrl));
                    } elseif (($validpassword == 1) and ($newpass1 === $newpass2)) {
                        if (BDO_MOBILE_API_ENABLED && intval($profile_user_id) === intval($actor->user_id)) {
                            require_once dirname(__DIR__) . '/models/MobileAccount.php';
                            try {
                                (new MobileAccount())->changePassword($actor, array('current_password' => postVal('current_password', ''), 'password' => $newpass2));
                                echo GetMetaTag(2, 'Votre mot de passe est modifié. Veuillez vous reconnecter.', $profileUrl);
                            } catch (MobileAccountException $e) { $this->accountFailure($e->getMessage()); }
                            return;
                        }
                        $newHash = password_hash($newpass2, PASSWORD_BCRYPT, ['cost' => 12]);
                        $this->User->set_dataPaste(array(
                            "user_id" => intval($profile_user_id),
                            "password_v2" => $newHash,
                            "password" => 'migrated',
                            "API_TOKEN" => null,
                            "DATE_TOKEN" => null
                        ));
                        $this->User->update();
                        // NULL values are not reliably handled by the legacy model writer.
                        Db_query('UPDATE users SET API_TOKEN=NULL, DATE_TOKEN=NULL WHERE user_id=' . intval($profile_user_id));
                        if (BDO_MOBILE_API_ENABLED) { require_once dirname(__DIR__) . '/models/MobileAccount.php'; (new MobileAccount())->revokeAll($profile_user_id); }

                        echo GetMetaTag(2, "Votre mot de passe est modifi&eacute;.", ($profileUrl));
                    }

            } elseif ($act == "setforum") {
                $this->User->set_dataPaste(array("user_id" => $profile_user_id));
                $this->User->load();
                $this->User->setForumAccount($this->User->username, genererMotDePasse(10), $this->User->email );

                echo GetMetaTag(2, "Votre compte forum a été créé. Vous devrez demander un nouveau mot de passe pour l'utiliser.", ($profileUrl));

            }
            // Afficher le formulaire pré-remplis
            elseif ($act == "") {
                //récupère les données utilisateur dans la base de données
                $this->User->set_dataPaste(array("user_id" =>$profile_user_id ));
                $this->User->load();

                //crée le tableau d'options
                $my_options[0][0] = 10;
                $my_options[0][1] = 10;
                $my_options[1][0] = 20;
                $my_options[1][1] = 20;
                $my_options[2][0] = 30;
                $my_options[2][1] = 30;

                $other_options[0][0] = 5;
                $other_options[0][1] = 5;
                $other_options[1][0] = 10;
                $other_options[1][1] = 10;
                $other_options[2][0] = 15;
                $other_options[2][1] = 15;

                $carre_options[0][0] = "0";
                $carre_options[0][1] = "Automatique";
                $carre_options[1][0] = "1";
                $carre_options[1][1] = "Manuel";


                $this->view->set_var(array
                    ("USERID" => $profile_user_id,
                    "UTILISATEUR" => $this->User->username,
                    "EMAIL" => $this->User->email,
                    "EMAIL_VERIFIED" => AccountSecurity::emailVerified($this->User),
                    "LOCATION" => $this->User->location,
                    "BIRTHDAY" => $this->User->birthday,
                    "YESISSELECTED" => ($this->User->OPEN_COLLEC == 'Y' ? 'Selected' : ''),
                    "NOISSELECTED" => ($this->User->OPEN_COLLEC == 'N' ? 'Selected' : ''),
                    "URLCOLLEC" => BDO_URL . 'guest?user=' . encodeUserId($profile_user_id),
                    "HASFORUMACCOUNT" => $this->User->hasForumAccount($this->User->username),
                    "IS_NEWSLETTER" => ($this->User->ABT_NEWS == 1 ? 'Checked' : ''),
                    "OPTIONCARRE" => GetOptionValue($carre_options, $this->User->CARRE_TYPE),
                    "ADULTYESISSELECTED" => ($this->User->EXPLICIT_CONTENT == "1" ? 'Selected' : ''),
                    "ADULTNOISSELECTED" => ($this->User->EXPLICIT_CONTENT == "0" ? 'Selected' : '')));
                
                if ($mobile != "T") {
                     $this->view->layout = "iframe";
                }
                $this->view->set_var("PAGETITLE", "BDOVORE.com : Mon profil");
                //$this->view->layout = "iframe";
                $this->view->render();
            }
        } else {
            $this->renderLogin();
        }
    }

    public function Inscription() {
        $this->view->layout = 'account';
        $this->view->a_phtmlFile = array('BODY' => 'compte/inscription');
        if (getVal('act') === 'post') {
            $this->mutationGuard();
            require_once dirname(__DIR__) . '/models/MobileAccount.php';
            try {
                if (postVal('NewPass1') !== postVal('Newpass2')) throw new MobileAccountException('Les mots de passe ne correspondent pas.');
                $result = $this->accountService()->register(array('username' => postVal('NewUser'), 'email' => postVal('NewEmail'), 'password' => postVal('NewPass1')), $_SERVER['REMOTE_ADDR'] ?? 'unknown');
                $this->pendingConfirmation('register', $result);
            } catch (MobileAccountException $e) { $this->accountFailure($e->getMessage()); }
            return;
        }
        $this->view->set_var(array('PAGETITLE' => 'BDOVORE.com : inscription', 'USERNAME' => '', 'EMAIL' => '', 'ERRORTEXT' => ''));
        $this->view->render();
    }

    public function forgotPass() {
        $email = getVal("email");
        $this->view->layout = 'account';
        $this->view->a_phtmlFile = array('BODY' => 'compte/forgotpass');
        $this->view->set_var(array('PAGETITLE' => 'Retrouver mon compte BDovore'));
        if ($email == "ok") {
            $this->mutationGuard();
            $user_username = postVal("txtusername");
            $user_email = postVal("txtemail");
            if (BDO_MOBILE_API_ENABLED) {
                require_once dirname(__DIR__) . '/models/MobileAccount.php';
                try {
                    $result = (new MobileAccount())->forgotPassword(array('username' => $user_username, 'email' => $user_email), $_SERVER['REMOTE_ADDR'] ?? 'unknown');
                    $this->pendingConfirmation('reset', $result);
                } catch (MobileAccountException $e) { $this->accountFailure($e->getMessage()); }
                return;
            }
            $this->loadModel("User");
            $this->User->load("c", " WHERE username= '" . Db_Escape_String($user_username) . "' and email = '" . Db_Escape_String($user_email) . "'");

            if (notIssetOrEmpty($this->User->user_id)) {
                $this->view->addAlertPage("L'utilisateur n'existe pas ou l'adresse e-mail est erronée !");
                $this->view->addPhtmlFile('alert', 'BODY');
            } else {
                // Génère un token unique
                $token = bin2hex(random_bytes(16));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valide pendant 1 heure

                // Stocke le token dans la table password_reset_tokens
                $this->loadModel("PasswordResetToken");
                $this->PasswordResetToken->set_dataPaste(array(
                    "user_id" => $this->User->user_id,
                    "token" => $token,
                    "expires_at" => $expiry
                ));
                $this->PasswordResetToken->update();

                // Prépare l'email à envoyer
                $reset_link = BDO_URL."compte/resetpassword?token=" . $token;
                $textemail = "Bonjour,\n\n";
                $textemail .= "Cliquez sur le lien suivant pour réinitialiser votre mot de passe :\n";
                $textemail .= $reset_link . "\n\n";
                $textemail .= "Ce lien expirera dans 1 heure.\n";
                $textemail .= "Si vous n'avez pas demandé de réinitialisation, ignorez cet email.\n";
                $textemail .= "Amicalement\n";
                $mail_entete = "From: no-reply@bdovore.com";
                bdovoreMail($user_email, "Réinitialisation de votre mot de passe", $textemail, $mail_entete);

                echo "Un email avec un lien de réinitialisation vous a été envoyé. Vous pouvez fermer cette fenêtre.";
                exit();
            }
        }
        
         
        $this->view->render();
    }
    
    public function resetPassword() {
        $token = getVal("token");
        $this->loadModel("PasswordResetToken");
        $this->PasswordResetToken->load("c", " WHERE token= '" . Db_Escape_String($token) . "' AND expires_at > NOW()");

        if (notIssetOrEmpty($this->PasswordResetToken->user_id ?? Null)) {
            $this->view->addAlertPage("Le lien de réinitialisation est invalide ou a expiré.");
            $this->view->addPhtmlFile('alert', 'BODY');
            $this->view->render();
        } else {
            $token_id = $this->PasswordResetToken->id;
            // Afficher un formulaire pour définir un nouveau mot de passe
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $new_password = postVal("NewPass1");
                $new_passowrd2 = postVal("NewPass2");
                if ($new_password !== $new_passowrd2 || AccountSecurity::passwordError($new_password)) {
                    $this->accountFailure(AccountSecurity::passwordError($new_password) ?: 'Les mots de passe ne correspondent pas.');
                    return;
                }
                $this->loadModel("User");
                $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
                $this->User->set_dataPaste(array(
                    "user_id" => $this->PasswordResetToken->user_id,
                    "password_v2" => $hash,
                    "password" => 'migrated'
                ));
                $this->User->update();
                $resetUserId = intval($this->PasswordResetToken->user_id);
                require_once dirname(__DIR__) . '/models/SocialAccount.php';
                SocialAccount::forgetUser(Bdo_Cfg::getVar('connexion'), $resetUserId);
                Db_query('UPDATE users SET API_TOKEN=NULL, DATE_TOKEN=NULL WHERE user_id=' . $resetUserId);
                if (BDO_MOBILE_API_ENABLED) { require_once dirname(__DIR__) . '/models/MobileAccount.php'; (new MobileAccount())->revokeAll($resetUserId); }
                // Invalidate other outstanding links for this account as well.
                Db_query('DELETE FROM password_reset_tokens WHERE user_id=' . $resetUserId);

                // Supprimer le token après utilisation

                $this->view->addAlertPage("Le mot de passe a été réinitialisé. Vous pouvez <a href='". BDO_URL."'>retourner à l'accueil</a> et vous connecter.");
                $this->view->addPhtmlFile('reset', 'BODY');
                $this->view->render();
            } else {
                $this->view->set_var("PAGETITLE", "BDOVORE.com : réinitiliasation mot de passe");
                $this->view->set_var("token", $token);

                // Afficher le formulaire de réinitialisation
                $this->view->render('resetpassword');
            }
        }
    }

    
    public function forgotLogin() {
        header('Cache-Control: no-store');
        $this->view->layout = 'account';
        $this->view->a_phtmlFile = array('BODY' => 'compte/forgotlogin');
        $email = postVal('txtemail', '');
        $error = ''; $sent = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->mutationGuard();
            require_once dirname(__DIR__) . '/models/MobileAccount.php';
            try {
                $result = (new MobileAccount())->forgotLogin(array('email' => $email), $_SERVER['REMOTE_ADDR'] ?? 'unknown');
                $sent = $result['message'];
            } catch (MobileAccountException $e) {
                http_response_code($e->status);
                $error = $e->getMessage();
            }
        }
        $this->view->set_var(array('PAGETITLE' => 'Retrouver mes pseudos BDovore', 'recovery_sent' => $sent,
            'recovery_error' => $error, 'recovery_email' => is_string($email) ? $email : ''));
        $this->view->render();
    }


}

?>
