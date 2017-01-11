<?php
require "LightOpenID.php";

class Bdo_Auth_Provider
{
    public function __construct ($openid_identifier)
    {
        $this->openid_identifier  = $openid_identifier;
        // Instanciation, on passe en paramètre le domaine le domaine
        // du site sur lequel établir la connexion. Il doit absolument
        // correspondre à la réalité !
        $this->openid = new LightOpenID('beta.bdovore.com');

        if($this->openid->mode){
            $this->confirmConnect();
        }
        else {
            $this->connect();
        }
    }

    public function connect ()
    {

        // Identifiant du service vers lequel établir la connexion, ici
        // on se connecte à Google.
        $this->openid->identity = $this->openid_identifier;

        // Champs qui seront demandés à l'internaute. Les deux derniers
        // sont spécifiques à Google, voyez la documentation de la
        // classe LightOpenID.
        $this->openid->required = array('contact/email', 'namePerson');

        // URL de retour qui sera appelée par le formulaire de Google
        // pour l'envoi des informations de connexion de l'internaute
        $this->openid->returnUrl = 'http://beta.bdovore.com/auth/provider';

        // Finalement on redirige l'internaute vers le formulaire de
        // connexion chez Google pour l'inviter à se connecter.
        header('Location: '.$this->openid->authUrl());
        exit();
    }



    public function confirmConnect ()
    {
        // On doit être dans le contexte d'un retour de connexion
        // cette variable sera remplie si c'est le cas.


        if( isset($_GET['openid_mode']) && $_GET['openid_mode'] == 'cancel' ) {

            throw new Exception("Vous n'avez pas autorisé
      l'application à accéder à vos données, la procédure
      ne peut pas se poursuivre.");

        }
        else {

            // On récupère ainsi les variables qui ont été
            // transmises
            $attributs = $this->openid->getAttributes();

            // Mode "standard", il faut découper nom et prénom.
            if(isset($attributs['namePerson'])) {

                list($prenom,$nom) = explode(' ',$attributs['namePerson']);

                $_SESSION['userConnect']->email = $attributs['contact/email'];
                header('Location: '.BDO_URL);

                /*
                 return Internaute::connexion(
                         $attributs['contact/email'],
                         $prenom,
                         $nom
                 );*/

                // Mode non-standard -> Google
            } else {

                $_SESSION['userConnect']->email = $attributs['contact/email'];
                header('Location: '.BDO_URL);

                // Google découpe déjà le prénom et le nom.
                /*
                 return Internaute::connexion(
                         $attributs['contact/email'],
                         $attributs['namePerson/first'],
                         $attributs['namePerson/last']
                 );
                */
            }
        }
    }
}