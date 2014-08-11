<?php



include (BDO_DIR."inc/queryfunction.php");

minAccessLevel(2);

if ($act == 'add') {

	$verif = "select flg_achat,id_edition
				  from users_album 
				  where id_edition=".$DB->escape($id_edition)." and 
				  		user_id=".$DB->escape($_SESSION["UserId"]);
	$DB->query($verif);
	if ($DB->num_rows() > 0) {
		$DB->next_record();
		if ($DB->f("flg_achat") == 'O' and $flg_achat == "N") {
			$DB->query("
			update users_album set 
				flg_achat = 'N', 
				date_achat = CURRENT_TIMESTAMP()
			where
				id_edition=".$DB->f("id_edition")."  
				and	user_id=".$DB->escape($_SESSION["UserId"]));
			echo GetMetaTag(2,"L'album a été ajouté dans votre collection !",("javascript:window.close()"));
			exit();
		}
		else {
			echo GetMetaTag(2,"L'album est déjà présent dans votre collection !",("javascript:window.close()"));
			exit();
		}
	}
	else {
		// ajout de l'album dans la colection
		if ($id_edition) {
			
			$insert_new = "
			insert into users_album (
				user_id, 
				date_ajout,
				flg_achat, 
				id_edition
			) VALUES (
				 ".$DB->escape($_SESSION["UserId"]).",
				 CURRENT_TIMESTAMP(), 
				 '".$DB->escape($flg_achat)."', 
				 ".$DB->escape($id_edition)."
			 )";

			$DB->query($insert_new);

		}
		else {
			
			$insert_new = "
			insert into users_album (user_id, date_ajout, flg_achat, id_edition ) 
			 select 
				 ".$DB->escape($_SESSION["UserId"]).",
				 CURRENT_TIMESTAMP(),
				 '".$DB->escape($flg_achat)."', 
				 t.id_edition 
			 from 
				bd_tome t
			 where 
				 t.id_tome =".$DB->escape($id_tome);
			
			$DB->query($insert_new);

		}

		echo GetMetaTag(1,"L'album a été ajouté à votre collection.",("javascript:window.close()"));
		exit();
	}
}
