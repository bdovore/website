<?php
switch ($_GET ['error']) {
	case "403" :
		{
			echo "Erreur 403...";
			break;
		}
	case "404" :
		{
			echo "Erreur 404 : Désolé, page inconnue !";
			break;
		}
}
