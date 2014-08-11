<?php

/*
$query = "SELECT couv.IMG_COUV FROM couv
WHERE NOT EXISTS (SELECT NULL FROM bd_edition WHERE couv.IMG_COUV = bd_edition.IMG_COUV)
and couv.IMG_COUV NOT LIKE ('%defau%')
";


SELECT IMG_COUV
FROM bd_edition
WHERE NOT
EXISTS (

SELECT NULL
FROM couv
WHERE couv.IMG_COUV = bd_edition.IMG_COUV
)



932 131f591a0b156f3df0413413b9b061fc
242 79ce8181bac19317b43994d2c57473a5
47 	78139803803aa7e0725ee267fdcaf8a0

*/
$query = "SELECT couv.IMG_COUV FROM couv WHERE `md5`='be48a1df346243a31115d25cd32eb131'
AND img_couv NOT IN ('CV-108057-112420.jpg','CV-083023-084934.jpg','CV-087516-089915.jpg') order by img_couv limit 50";
$DB->query ($query);
if ($DB->nf() != 0)
{
	$a_imgcouv = array();
	while ($DB->next_record()){
		$a_imgcouv[] = $DB->f ("IMG_COUV");
	}

	foreach ($a_imgcouv as $couv)
	{
		$query1 = "update bd_edition set img_couv=null where img_couv='".$couv."'";
		$query2 = "delete from couv where img_couv='".$couv."'";

		if (file_exists(BDO_DIR."images/couv/".$couv)){

			$a_couv = explode('-',$couv);



			if(isset($_GET['s']))
			{
				echo 'supp '.$couv.'<br />';

				if (@unlink(BDO_DIR."images/couv/".$couv))
				{
					$DB->query($query1);
					$DB->query($query2);
				}
			}
			else
			{
				echo "<a href='".BDO_URL."/admin/adminalbums.php?alb_id=".($a_couv[1]+0)."' target='_blank'><img src='".BDO_URL_IMAGE."couv/".$couv."' border=0> - ".$couv."</a><br />";
			}
		}
		else
		{
			$DB->query($query1);
			$DB->query($query2);
		}
	}
	if(isset($_GET['s']))
	{
		$redirection = BDO_URL."img.php";
		echo '<META http-equiv="refresh" content="1; URL='.$redirection.'">';
		exit();
	}
	else
	{
		echo "<br /><br /><a href='".BDO_URL."/img.php?s'>supprimer</a>";
	}


}
else
{
	echo "plus d'occurences.";
}