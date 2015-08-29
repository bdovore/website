<?php

/**
* Fonction encodage du user pour partie guest
*
* @param        type
* $id        string
*/

function encodeUserId ($id)
{
    if (! empty($id)) {
        return ($id * 1209) + 951;
    }
    return false;
}

/**
 * Fonction decodage du user pour partie guest
 *
 * @param type $id
 *            string
 *            
 */
function decodeUserId ($id)
{
    return ($id - 951) / 1209;
}

/**
 * Fonction regex sur texte
 *
 * @param type $texte
 *            string
 *            
 */
function regextexte ($texte)
{
    $texte = eregi_replace("<([http|news|ftp]+://[^ >\n\t]+)>:([^[:space:]]*)", "<a href=\"\\1\" target=\"_blank\">\\2</a>", $texte);
    $texte = eregi_replace("<([http|news|ftp]+://[^ >\n\t]+)>", "<a href=\"\\1\" target=\"_blank\">\\1</a>", $texte);
    $texte = eregi_replace("(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-])\.([^[:space:]]*)([[:alnum:]-]))", "<a href=\"mailto:\\1\">\\1</a>", $texte);
    $texte = ereg_replace(10, "<br />", $texte);
    $texte = ereg_replace("<br /> ", "<br /> ", $texte);
    $espaces = ereg_replace("  ", "  ", $texte);
    while ($texte != $espaces) {
        $texte = $espaces;
        $espaces = ereg_replace("  ", "  ", $texte);
    }
    return $texte;
}

/**
 * Fonction controle de l'email saisie (REGEX)
 *
 * @param type $email
 *            string
 *            
 */
function Checkmail ($email)
{
    return (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+' . '@' . '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email));
}

/**
 * Fonction controle de password
 *
 * @param type $password
 *            string
 *            
 */
function Checkpassword ($password)
{
    if (is_integer(strrpos($password, ' ')) or strlen($password) < 6) {
        return "Le password doit contenir au moins 6 caractères et ne doit pas comporter d'espaces.";
    }
    else {
        return true;
    }
}

/**
 * Fonction controle la validité des charactères d'un login
 *
 * @param type $login
 *            string
 *            
 */
function CheckChars ($login)
{
    //seul les caractères [A-Z], [a-z], [0-9], '_', '@' et '-' sont authorisés (pas les espaces !!)
    return preg_match('/^[A-Za-z0-9_@\-]+$/',$login);
    //return ! ((strrpos($login, ' ') > 0) or (strspn($login, "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_@") != strlen($login)));
}

/**
 * Fonction controle les login protégés*
 *
 * @param type $login
 *            string
 *            
 */
function AuthorisedLogin ($login)
{
    return ! (eregi("^((root)|(bin)|(daemon)|(moderator)|(modérateur)|(adm)|(administrator)|(administrateur)|(lp)|(sync)|(shutdown)|(halt)|(mail)|(news)" . "|(uucp)|(operator)|(games)|(mysql)|(httpd)|(nobody)|(dummy)" . "|(www)|(cvs)|(shell)|(ftp)|(irc)|(debian)|(ns)|(download))$", $login));
}

/**
 * Fonction de généreation de password aléatoire
 *
 * @param type $long
 *            integer
 *            
 */
function passgen ($long)
{
    mt_srand((float) microtime() * 1000000);
    /* génération du mot de passe */
    $chaine = "abBDEFcdefghijkmnPQRSTUVWXYpqrst23456789"; // caractères
                                                          // possibles
    $mdp = '';
    srand((double) microtime() * 1000000);
    for ($i = 0; $i < $long; $i ++) // mot de passe de $long caractères
        $mdp .= $chaine[rand() % strlen($chaine)];
    return $mdp;
}

/**
 * Fonction d'extraction d'extension
 *
 * @param type $str
 *            string
 *            
 */
function getFileExtension ($str)
{
    $i = strrpos($str, ".");
    if (! $i) {
        return "";
    }
    $l = strlen($str) - $i;
    $ext = substr($str, $i + 1, $l);
    return $ext;
}

/**
 * Fonction de génération de la barre de navigation
 *
 * @param
 *            type			description
 *            $first integer numéro du premier élément à afficher
 *            $nb			integer			nb d'élements par pages
 *            $nbtotal		integer			nb total d'éléments à afficher
 *            $action		string			contient l'URL de retour
 *            
 *            
 */
function GetNavigationBar ($first, $nb, $nbtotal, $action)
{
    // Détermine si des variables ont déjà été passée dans l'URL de destination
    if (strpos($action, "?") == false) {
        $sep = "?";
    }
    else {
        $sep = "&";
    }
    // détermine s'il convient d'afficher précédent
    if ($first != 0) {
        $navigationbar = '<a href="' . $action . $sep . 'first=' . ($first - $nb) . '&nb=' . $nb . '"><<</a>';
    }
    if ($nb > 0) {
        // Affiche les numéros de page
        if (intval($nbtotal / $nb) == ($nbtotal / $nb)) {
            $nb_pages = intval($nbtotal / $nb);
        }
        else {
            $nb_pages = intval($nbtotal / $nb) + 1;
        }
        $page_courante = intval($first / $nb) + 1;
    }
    else {
        $nbtotal = 0;
    }
    for ($i = 1; $i <= $nb_pages; $i ++) {
        if ($i != $page_courante) {
            $navigationbar .= ' <a href="' . $action . $sep . 'first=' . (($i - 1) * $nb) . '&nb=' . $nb . '">' . $i . '</a> ';
        }
        else {
            // met le numéro en gras
            $navigationbar .= ' <b><a href="' . $action . $sep . 'first=' . (($i - 1) * $nb) . '&nb=' . $nb . '">' . $i . '</a></b> ';
        }
    }
    // détermine s'il convient d'afficher suivant
    if ($nbtotal > ($first + $nb)) {
        $navigationbar .= '<a href="' . $action . $sep . 'first=' . ($first + $nb) . '&nb=' . $nb . '">>></a>';
    }
    return $navigationbar;
}

/**
 * Fonction de génération d'éléments option value
 *
 * @param
 *            type			description
 *            $myarray array contient un tableau avec les choix possibles
 *            $val			string			valeur selectionnée par défaut
 *            
 *            
 */
function GetOptionValue ($myarray, $val)
{
    $option = "";
    for ($nindex = 0; $nindex < count($myarray); $nindex ++) {
        $option .= '<option value="' . $myarray[$nindex][0] . '" ' . (($myarray[$nindex][0] == $val) ? 'selected' : '') . '>' . $myarray[$nindex][1] . "</option>";
    }
    return $option;
}

function GetOption1Value ($myarray, $val)
{
    $option = "";
    for ($nindex = 0; $nindex < count($myarray); $nindex ++) {
        if ($myarray[$nindex][0] == $val) {
            $option .= $myarray[$nindex][1];
        }
    }
    return $option;
}

/**
 * Fonction de génération de la barre de login
 *
 * @param
 *            type			description
 *            $authentification Boolean Vérifie si l'user est identifié
 *            $username					string			Nom de l'utilisateur
 *            
 *            
 */
function GetIdentificationBar ()
{
    if (minAccessLevel(2, false)) {
        /*
         * $loginForm = ' <form name="loginForm" method="get" action="' .
         * BDO_URL .'/membres/logout.php"> Salut <strong><a href="' . BDO_URL .
         * 'membres/profil.php">'. $_SESSION["UserName"] .'</a></strong> ! <br
         * /> <div align="center"><input name="LoginDisConnect" type="submit"
         * id="LoginDisConnect" value="Se déconnecter" /></div> </form>';
         */
        
        $loginForm = '
		<div align="center" style="position: relative; z-index: 10;">
   <div class="fond"></div>
		<form name="loginForm" method="get" action="' . BDO_URL . '/membres/logout.php">
		Salut <strong><a href="' . BDO_URL . 'membres/profil.php">' . $_SESSION["UserName"] . '</a></strong> !
		<input name="LoginDisConnect" type="submit" id="LoginDisConnect" value="Se déconnecter" />
		</form>
		<a href="' . BDO_URL . 'membres/profil.php"><font style="font-size:10">Mon compte</font></a>
		</div>

	<ul id="menuperso1" class="menu">
		<li><a href="' . BDO_URL . 'membres/actualites.php" title="Mon actu à moi !">Mon actu</a></li>
		<li><a href="' . BDO_URL . 'membres/userbrowser.php" title="Ma super collec !">Ma collection</a></li>
		<li><a href="' . BDO_URL . 'membres/albmanquant.php" title="Y m\'en manque !">Mes albums manquants</a></li>
</ul>
	<ul id="menuperso2" class="menu">
<li><a href="' . BDO_URL . 'membres/userhome.php" title="Le tableau de commande de votre espace privé">Garde-manger</a></li>
<li><a href="' . BDO_URL . 'membres/usersearch.php?rb_mode=1"  title="Vos albums">Mes étagères</a></li>
</ul>
	<ul id="menuperso2" class="menu">
<li><a href="' . BDO_URL . 'membres/userstat.php"  title="Grand sportif devant l\'éternel, le collectionneur mérite bien d\'avoir ses stats !">Mes stats</a></li>
<li><a href="' . BDO_URL . 'guest.php" title="La présentation publique de votre collec">Ma belle collec</a></li>
<li><a href="' . BDO_URL . 'membres/addition.php" title="L\'addition SVP ! Gloups...">L\'addition</a></li>
	</ul>
	';
        
        return $loginForm;
        
        /*
         * return "Salut " . $_SESSION["UserName"] . " ! (Vous n'êtes pas " .
         * $_SESSION["UserName"] . " ? Cliquer <a href=\"" . BDO_URL .
         * "membres/logout.php\">ici</a> pour vous déconnecter)";
         */
    }
    else {
        $loginForm = '
				<div align="center" style="position: relative; z-index: 10;">
   <div class="fond"></div>
<form name="loginForm" method="post" action="' . BDO_URL . '/membres/login.php?log=1">
<table border="0" align="center" cellspacing="0" cellpadding="0">
<tr><td align="right">Pseudo : </td><td><input name="txtlogin" type="text" id="txtlogin" size=12 /><tr><td>
<tr><td align="right">Mot de passe : </td><td><input name="txtmot2pass" type="password" id="txtmot2pass" size=12 /><tr><td>
<tr><td colspan="2"><label for="chkvisit">Se souvenir de moi	<input name="chkvisit" type="checkbox" id="chkvisit" value="1" /></label><tr><td>
<tr><td colspan="2" align="center"><input name="LoginConnecter" type="submit" id="LoginConnecter" value="Se connecter" /><tr><td>
<tr><td colspan="2" align="center"><a href="' . BDO_URL . 'membres/inscription.php"><font color="#990000" style="font-size:10px">Vous n\'êtes pas inscrit ?</font></a><tr><td>
</table>
        </form></div>';

        return $loginForm;
        
        /*
         * return "Cliquez <a href=\"" . BDO_URL . "membres/login.php\">ici</a>
         * pour vous identifier - Vous n'êtes pas inscrit ? Cliquer <a href=\""
         * . BDO_URL . "membres/inscription.php\">ici</a> pour vous inscrire ";
         */
        // <a href="' . BDO_URL . 'membres/forgotpass.php"><font color="#990000"
        // style="font-size:11">Mot de passe oublié ?</font></a>
    }
}

/**
 * Fonction de génération d'éléments de type meta
 *
 * @param
 *            type			description
 *            $temps Integer Temps en seconde pdt lequel la page s'affiche
 *            $message					string			Message à afficher
 *            $URLCible					string			adresse de la page à ouvrir
 *            
 *            
 */
function GetMetaTag ($temps, $message, $URLCible)
{
    $answer = '<META http-equiv="refresh" content="' . $temps . '; URL=' . $URLCible . '">' . $message;
    return $answer;
}

/**
 * Fonction de vérification et d'homogeneisation de type d'image
 *
 * @param
 *            type			description
 *            $type string extension d'image
 *            $error					booloean		Retourne true en cas d'erreur
 *            
 *            
 */
function check_image_type (&$type, &$error)
{
    global $lang;
    switch ($type) {
        case 'jpeg':
        case 'pjpeg':
        case 'jpg':
            return '.jpg';
            break;
        case 'gif':
            return '.gif';
            break;
        case 'png':
            return '.png';
            break;
        default:
            $error = true;
            $error_msg = "Wrong image type";
            break;
    }
    return false;
}

/**
 * Fonction de vérification et de préparation de chaines de texte à l'insertion
 * en bd
 *
 * @param
 *            type			description
 *            $text						string chaine à transférer
 *            $type						string type de la variable (text,
 *            
 *            
 */
function sqlise ($text, $type)
{
    
    switch ($type) {
        case 'text':
            return ($text == '') ? 'NULL' : "'" . Db_Escape_String($text) . "'";
        case 'text_simple':
            return ($text == '') ? 'NULL' : "'" . Db_Escape_String($text) . "'";
        case 'int':
            return ($text == '') ? 0 : Db_Escape_String($text);
        case 'int_null':
            return ($text == '') ? 'NULL' : Db_Escape_String($text);
    }
}

/**
 * Fonction de conversion de date
 *
 * @param
 *            type			description
 *            $text						string chaine à convertir
 *            
 *            
 */
function cv_date ($text)
{
    $date = explode(" ", $text);
    switch ($date[0]) {
        case 'janvier':
            return $date[1] . "-01-01";
        case 'février':
            return $date[1] . "-02-01";
        case 'mars':
            return $date[1] . "-03-01";
        case 'avril':
            return $date[1] . "-04-01";
        case 'mai':
            return $date[1] . "-05-01";
        case 'juin':
            return $date[1] . "-06-01";
        case 'juillet':
            return $date[1] . "-07-01";
        case 'août':
            return $date[1] . "-08-01";
        case 'septembre':
            return $date[1] . "-09-01";
        case 'octobre':
            return $date[1] . "-10-01";
        case 'novembre':
            return $date[1] . "-11-01";
        case 'décembre':
            return $date[1] . "-12-01";
    }
}

/**
 * Fonction qui retourne les deux éléments les plus significatif dune chaine.
 *
 * @param
 *            type			description
 *            $text						string chaine à traiter
 *            
 *            
 */
function main_words ($text)
{
    // Explose la chaine de charactères
    $mots = explode(" ", $text);
    // Construit le tableau de pertinance
    $j = 0;
    $i = 0;
    while ($i < sizeof($mots)) {
        if (! is_not_relevant(clean_apostrophes($mots[$i]))) {
            $rel_mots[$j][0] = clean_apostrophes($mots[$i]);
            $rel_mots[$j][1] = strlen(clean_apostrophes($mots[$i]));
            $j ++;
        }
        $i ++;
    }
    usort($rel_mots, "cmp1");
    return array_slice($rel_mots, 0, 2);
}

/**
 * Fonction qui identifie les éléments non significatifs d'une chaine de
 * charactère
 *
 * @param
 *            type			description
 *            $text						string chaine à traiter
 *            
 *            
 */
function is_not_relevant ($text)
{
    return eregi("^le$|^la$|^les$|^un$|^une$|^de$|^des$|^au$|^aux$|^a$|^et$|^mais$|^ou$|^est$|^et$|^or$|^ni$|^car$|^qui$|^que$|^quoi$|^dont$|^ne$|^pas$|^coffret$|^nouvelle$|^edition$", $text);
}

/**
 * Fonction qui supprime les articles d'une chaîne de caractère
 *
 * @param
 *            type			description
 *            $text						string chaine à traiter
 *            
 *            
 */
function clean_article ($text)
{
    return eregi_replace("le |la |les |un |une |des |au |aux |a |et |mais |ou |est |et |or |ni |car |l'", "", $text);
}

/**
 * Fonction qui élimine les articles éludés
 *
 * @param
 *            type			description
 *            $text						string chaine à traiter
 *            
 *            
 */
function clean_apostrophes ($text)
{
    return eregi_replace("l'|d'|n'|\(|\)", "", $text);
}

/**
 * Fonction de tri pour des tableaux à deux dimension
 *
 * @param
 *            type			description
 *            $text						string chaine à traiter
 *            
 *            
 */
function cmp1 ($a, $b)
{
    if ($a[1] == $b[1]) return 0;
    return ($a[1] > $b[1]) ? - 1 : 1;
}

/**
 * Fonction retournant le temps écoulé depuis le 1 - 1 1970
 *
 * @param
 *            type			description
 *            Aucun
 *            
 *            
 */
function temps ()
{
    $time = microtime();
    $tableau = explode(" ", $time);
    return ($tableau[1] + $tableau[0]);
}

/**
 * Fonction de conversion mois
 *
 * @param
 *            type			description
 *            $month					integer mois à convertir en text
 *            
 *            
 */
function month_to_text ($month)
{
    $months = array(
            "Janvier",
            "Février",
            "Mars",
            "Avril",
            "Mai",
            "Juin",
            "Juillet",
            "Août",
            "Septembre",
            "Octobre",
            "Novembre",
            "Décembre"
    );
    return $months[$month - 1];
}

/**
 * Fonction permettant de génération de requète insert
 *
 * @param
 *            type			description
 *            $table					string table dans laquelle les données doivent être
 *            insérées
 *            $insert					array array contenant la valeur à insérer ainsi que le
 *            champ où l'insérer
 *            
 *            
 */
function insert_query ($table, $array)
{
    global $DB;
    
    $query = "INSERT INTO `$table`";
    $i = 0;
    $fields = '';
    $values = '';
    // détermine les champs dans lequel les valueurs vont être insérées
    foreach ($array as $key => $value) {
        $fields .= (($i == 0) ? '' : ', ') . "`$key`";
        $values .= (($i == 0) ? '' : ', ') . $value;
        $i ++;
    }
    $query .= " (" . $fields . ") VALUES (" . $values . ");";
    return $query;
}

/**
 * Fonction de récupération d'image sur le net
 *
 * @param
 *            type			description
 *            $month					integer mois à convertir en text
 *            
 *            
 */
function get_img_from_url ($url_file, $chemin, $dest_file)
{
    if (preg_match('/^(http:\/\/)?([\w\-\.]+)\:?([0-9]*)\/(.*)$/', $url_file, $url_ary)) { // un
                                                                                           // fichier
                                                                                           // à
                                                                                           // télécharger
        if (empty($url_ary[4])) {
            echo 'Erreur dans l\'URL de l\'image à télécharger';
            exit();
        }
        $base_get = '/' . $url_ary[4];
        $port = (! empty($url_ary[3])) ? $url_ary[3] : 80;
        // Connection au serveur hébergeant l'image
        if (! ($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr))) {
            $error = true;
            echo 'Impossible de se connecter au serveur hébergenant l\'image.';
            exit();
        }
        // Récupère l'image
        @fputs($fsock, "GET $base_get HTTP/1.1\r\n");
        @fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
        @fputs($fsock, "Connection: close\r\n\r\n");
        unset($avatar_data);
        while (! @feof($fsock)) {
            $avatar_data .= @fread($fsock, 102400);
        }
        @fclose($fsock);
        // Check la validité de l'image
        if (! preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || ! preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)) {
            $error = true;
            echo 'Erreur lors du téléchargement de l\'image.';
            exit();
        }
        $avatar_filesize = $file_data1[1];
        $avatar_filetype = $file_data2[1];
        $avatar_data = substr($avatar_data, strlen($avatar_data) - $avatar_filesize, $avatar_filesize);
        $tmp_path = $chemin;
        $tmp_filename = tempnam($tmp_path, uniqid(rand()) . '-');
        $fptr = @fopen($tmp_filename, 'wb');
        $bytes_written = @fwrite($fptr, $avatar_data, $avatar_filesize);
        @fclose($fptr);
        if ($bytes_written != $avatar_filesize) {
            @unlink($tmp_filename);
            echo 'Erreur lors de l\'écriture du fichier';
            exit();
        }
        // newfilemname
        if (! ($imgtype = check_image_type($avatar_filetype, $error))) {
            exit();
        }
        $new_filename = $dest_file . $imgtype;
        // si le fichier existe, on l'efface
        if (file_exists($chemin . $new_filename)) {
            @unlink($chemin . $new_filename);
        }
        // copie le fichier temporaire dans le repertoire image
        @copy($tmp_filename, $chemin . $new_filename);
        @unlink($tmp_filename);
        return $dest_file . $imgtype;
    }
}

/* 
 * Variante de la fonction précédente pour form et URL
 */

function imgCouvFromForm($lid_tome, $lid_edition) {
        $imageproperties = getimagesize($_FILES['txtFileLoc']['tmp_name']);
        $imagetype = $imageproperties[2];

        $newfilename = "CV-" . sprintf("%06d", $lid_tome) . "-" . sprintf("%06d", $lid_edition);
        // vérifie le type d'image
        switch ($imagetype) {
            case IMAGETYPE_GIF:
                $newfilename .=".gif";
                break;
            case IMAGETYPE_JPEG:
                $newfilename .=".jpg";
                break;
            case IMAGETYPE_PNG:
                $newfilename .=".png";
                break;
            default:
                echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Seul des fichiers PNG, JPEG ou GIF peuvent &ecirc;tre charg&eacute;s. Vous allez &ecirc;tre redirig&eacute;.';
                exit();
                break;
        }

        //move_uploaded_file fait un copy(), mais en plus il vérifie que le fichier est bien un upload
        //et pas un fichier local (genre constante.php, au hasard)
        if (!move_uploaded_file($_FILES['txtFileLoc']['tmp_name'], BDO_DIR_COUV . $newfilename)) {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors de l\'envoi de l\'image au serveur. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }
        return $newfilename;
    }
    
    function imgCouvFromUrl($url_ary, $lid_tome, $lid_edition) {
        /*
         * Récupère une image de couvertue et la copie dans le répertoire fournit en paramètre
         * Return : nom du fichier
         */
        if (empty($url_ary[4])) {
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image incompl&egrave;te. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }
        $base_get = '/' . $url_ary[4];
        $port = (!empty($url_ary[3]) ) ? $url_ary[3] : 80;
        // Connection au serveur hébergeant l'image
        if (!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr))) {
            $error = true;
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">URL image innacessible. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }

        // Récupère l'image
        @fputs($fsock, "GET $base_get HTTP/1.1\r\n");
        @fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
        @fputs($fsock, "Connection: close\r\n\r\n");

        unset($avatar_data);
        while (!@feof($fsock)) {
            $avatar_data .= @fread($fsock, 102400);
        }
        @fclose($fsock);

        // Check la validité de l'image
        if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)) {
            $error = true;
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Erreur lors du t&eacute;l&eacute;chargement de l\'image. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }
        $avatar_filesize = $file_data1[1];
        $avatar_filetype = $file_data2[1];
        $avatar_data = substr($avatar_data, strlen($avatar_data) - $avatar_filesize, $avatar_filesize);
        $tmp_path = BDO_DIR_UPLOAD;
        $tmp_filename = tempnam($tmp_path, uniqid(rand()) . '-');
        $fptr = @fopen($tmp_filename, 'wb');
        $bytes_written = @fwrite($fptr, $avatar_data, $avatar_filesize);
        @fclose($fptr);

        if ($bytes_written != $avatar_filesize) {
            @unlink($tmp_filename);
            echo '<META http-equiv="refresh" content="5; URL=javascript:history.go(-1)">Could not write avatar file to local storage. Please contact the board administrator with this message. Vous allez &ecirc;tre redirig&eacute;.';
            exit();
        }

        // newfilemname
        if (!($imgtype = check_image_type($avatar_filetype, $error))) {
            exit;
        }
        $newfilename = "CV-" . sprintf("%06d", $lid_tome) . "-" . sprintf("%06d", $lid_edition) . $imgtype;

        // si le fichier existe, on l'efface
        if (file_exists(BDO_DIR_COUV . "$newfilename")) {
            @unlink(BDO_DIR_COUV . "$newfilename");
        }

        // copie le fichier temporaire dans le repertoire image
        @copy($tmp_filename, BDO_DIR_COUV . "$newfilename");
        unlink($tmp_filename);
        return $newfilename;
    }

/**
 * Fonction générant le menu admin
 *
 * @param
 *            type			description
 *            
 *            
 */
function admin_menu ()
{
    global $DB;
    // Selections des éléments affichés dans le menu
    $a_csv = file(BDO_DIR . "cache/menu_admin.csv");
    $result = '<div id="cadre_menu_admin" class="right">';

    foreach ($a_csv as $l_csv) {
        $a_lineMenu = explode(';', $l_csv);
        if ($a_lineMenu["2"] >= $_SESSION["UserLevel"]) {
            if ($a_lineMenu["0"] == '<--space-->') {
                $result .= '<hr class="hr_menu_admin" />';
            }
            else {
                $result .= '<a href="' . BDO_URL . $a_lineMenu["1"] . '">' . $a_lineMenu["0"] . '</a><br />';
            }
        }
    }
    
    return $result;
}

/**
 * Fonction générant un tag <img>
 *
 * @param
 *            type			description
 *            $url_image				string url de l'image à afficher
 *            $alt_image				string texte à afficher en cas de survol
 *            $dest_image				string lien vers lequel l'image doit pointer
 *            " $dest type				integer			si 0, lien normal, si 1, ouverture dans
 *            une autre fenetre , si 2 ouverture dans un popup
 *            
 *            
 */
function gen_img_tag ($url_image, $weight, $height, $border, $alt_image)
{
    $tag = "";
    // Création du tag img
    $tag .= '<img src="' . $url_image . '"';
    if ($weight != "") $tag .= ' weight = "' . $weight . '"';
    if ($height != "") $tag .= ' height = "' . $height . '"';
    if ($border != "") $tag .= ' border = "' . $border . '"';
    if ($alt_image != "") $tag .= ' alt = "' . $alt_image . '"';
    $tag .= ">";
    return $tag;
}

/**
 * Fonction retournant une liste de lien aves les feuilles de style à inclure
 *
 * @param type $css_sheets
 *            tableau de strings
 *            
 */
function gen_css_links ($css_sheets)
{
    foreach ($css_sheets as $css) {
        $links .= "<link href=\"" . BDO_URL . "style/$css\" rel=\"stylesheet\" type=\"text/css\">\n";
    }
    return $links;
}

/**
 * Fonction retournant les dimensions d'une image
 */
function imgdim ($url_image)
{
    // if ($_SERVER["SERVER_NAME"] != 'localhost')
    // {
    $imgdim = getimagesize($url_image);
    return "LxH : " . $imgdim[0] . "x" . $imgdim[1] . "";
    // }
    // else {
    // return "<i>Pas de fichier image ".$url_image.".</i>";
    // }
}

function lit_rss ($xml, $objets)
{
    $resultat = array();
    // on lit tout le fichier
    if (! empty($xml)) {
        
        // on découpe la chaine obtenue en items
        $tmp = preg_split("/<\/?" . "item" . ">/", $xml);
        
        // pour chaque item
        for ($i = 1; $i < sizeof($tmp) - 1; $i += 2)
            
            // on lit chaque objet de l'item
            foreach ($objets as $objet) {
                
                // on découpe la chaine pour obtenir le contenu de l'objet
                $tmp2 = preg_split("/<\/?" . $objet . ">/", $tmp[$i]);
                
                // on ajoute le contenu de l'objet au tableau resultat
                $resultat[$i - 1][] = @$tmp2[1];
            }
    }
    
    // on retourne le tableau resultat
    return $resultat;
}

function dateParution ($date, $flag = 0)
{
    if (! empty($date)) {
        // date de parution superieur a date du jour moins 1 mois
        if (TimestampDate($date) > (time() - 2592000)) {
            $dte_parution = translate_date($date);
        }
        else {
            $dte_parution = month_to_text(substr($date, 5, 2)) . " " . substr($date, 0, 4);
        }
    }
    else {
        if ($flag == 1) $dte_parution = "Date introuvable";
        else
            $dte_parution = "Champ 'date' non-rempli";
    }
    return $dte_parution;
}

// création de la fonction curString à 4 paramètres  
// $string = la chaîne tronquer  
// $start = le caractère de départ  
// $length = la longueur de la chaîne (en caractère)  
// $endStr = paramètre optionnel qui termine l'extrait ([…] par défaut)  
function cutString($string, $start, $length, $endStr = '[...]'){  
    // si la taille de la chaine est inférieure ou égale à celle  
    // attendue on la retourne telle qu'elle  
    if( strlen( $string ) <= $length ) return $string;  
    // autrement on continue  
  
    // permet de couper la phrase aux caractères définis tout  
    // en prenant en compte la taille de votre $endStr et en   
    // re-précisant l'encodage du contenu récupéré  
    $str = mb_substr( $string, $start, $length - strlen( $endStr ) + 1, 'UTF-8');  
    // retourne la chaîne coupée avant la dernière espace rencontrée  
    // à laquelle s'ajoute notre $endStr  
    return substr( $str, 0, strrpos( $str,' ') ).$endStr;  
}  
  
function ficheAlbum ($o_album, $class = 'couvBig')
{
    if (is_array($o_album)) {
        $o_album = (object) $o_album;
    }
    //TODO
}

/*
 * urlAlbum 
 * fournit l'url d'un lien vers la iframe album
 * 
 */
function urlAlbum ($o_album, $class = 'couvBig')
{
    if (is_array($o_album)) {
        $o_album = (object) $o_album;
    }
    
    $x = getenv("HTTP_USER_AGENT");
    if (strpos($x, 'MSIE 7.0') || strpos($x, 'MSIE 6.0')) {
        return '#" onclick="window.open(' . "'" . BDO_URL . "Album?id_tome=" . $o_album->ID_TOME . "','Album','width=600,height=600,scrollbars=1')" . ';return false;';
    }
    else {
        $html = '<a class="fancybox fancybox.iframe {width:600,height:600}" 
                href="' . BDO_URL . 'Album?id_tome=' . $o_album->ID_TOME . '" title="' . $o_album->TITRE_TOME . '">';
        switch ($class) {
            case "couvBig":
                {
                    $html .= '<img src="' . BDO_URL_COUV . $o_album->IMG_COUV . '" class="' . $class . '" title="' . $o_album->TITRE_TOME . '"/>';
                    $html .= '</a>';
                    break;
                }
            case "couvMedium":
                {
                    $html .= '<img src="' . BDO_URL_COUV . $o_album->IMG_COUV . '" class="' . $class . '" title="' . $o_album->TITRE_TOME . '"/>';
                    $html .= '</a>';
                    break;
                }
            case "couvSmall":
                {
                    $html .= '<img src="' . BDO_URL_COUV . $o_album->IMG_COUV . '" class="' . $class . '" title="' . $o_album->TITRE_TOME . '"/>';
                    $html .= '</a>';
                    break;
                }
            case "albTitle":
                {
                    $html .= $o_album->TITRE_TOME . '</a>';
                    break;
                }
        }
        
        return $html;
    }
}

function urlSerie ($o_serie)
{
    if (is_array($o_serie)) {
        $o_serie = (object) $o_serie;
    }
    return '<a href="' . BDO_URL . 'serie-bd-' . $o_serie->ID_SERIE .'-'.clean_url($o_serie->NOM_SERIE) . '" title="' . $o_serie->NOM_SERIE . '">
           ' . $o_serie->NOM_SERIE . '</a>';
}

function clean_url($texte) {
	//Suppression des espaces en début et fin de chaîne
	$texte = trim($texte);
 
	//Suppression des accents
	$texte = htmlentities($texte, ENT_NOQUOTES, "UTF-8");
    
        $texte = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $texte);
        $texte = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $texte); // pour les ligatures e.g. '&oelig;'
        $texte = preg_replace('#&[^;]+;#', '', $texte); // supprime les autres caractères
	//mise en minuscule
	$texte = strtolower($texte);
 
	//Suppression des espaces et caracteres spéciaux
	$texte = str_replace(" ",'-',$texte);
	$texte = preg_replace('#([^a-z0-9-])#','-',$texte);
 
	//Suppression des tirets multiples
	$texte = preg_replace('#([-]+)#','-',$texte);
 
	//Suppression du premier caractère si c'est un tiret
	if($texte{0} == '-')
		$texte = substr($texte,1);
 
	//Suppression du dernier caractère si c'est un tiret
	if(substr($texte, -1, 1) == '-')
		$texte = substr($texte, 0, -1);
 
	return $texte;
}
function clean_rss ($str) {
    // remplace les caractères " par '
    $str_ret = $str;
    $str_ret = str_replace ( chr(0x92), '\'',  $str_ret );	
    $str_ret = str_replace ( chr(0x85), '\'',  $str_ret );	
    $str_ret = str_replace ( chr(0x9c), '\'',  $str_ret );
    $str_ret = str_replace ( chr(0x93), '\'',  $str_ret );
    $str_ret = str_replace ( chr(0x94), '\'',  $str_ret );
    $str_ret = str_replace ( chr(0x22), '\'',  $str_ret );
    return $str_ret;
	
}