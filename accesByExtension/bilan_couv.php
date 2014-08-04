<?php

minAccessLevel();

//exec("cp ".BDO_DIR."images/couv/gif_desentrelace.php ".BDO_DIR);

if (issetNotEmpty($_GET['supp']))
{
	$a_fileName=array($_GET['supp']);
}
else {
	$a_fileName=array(
	"gif_desentrelace.php",
	"Thumbs.db",
	"couv.jpg",
	);
}

foreach ($a_fileName as $fileName)
{
	$fileSupp = BDO_DIR.'images/couv/'.$fileName;
	if (is_file($fileSupp) and unlink($fileSupp))
	{
		echo '<br />'.$fileName." supprimé.";
	}
	else {
				echo '<br />'.$fileName." inexistant.";
	}
}

/*
SELECT couv.* FROM couv WHERE couv.IMG_COUV NOT IN (SELECT bd_edition.IMG_COUV FROM bd_edition)
SELECT couv.* FROM couv WHERE NOT EXISTS (SELECT NULL FROM bd_edition WHERE couv.IMG_COUV = bd_edition.IMG_COUV)
SELECT couv.* FROM couv LEFT JOIN bd_edition ON couv.IMG_COUV = bd_edition.IMG_COUV WHERE bd_edition.IMG_COUV IS NULL

SELECT bd_tome.IMG_COUV FROM bd_tome WHERE bd_tome.IMG_COUV NOT IN (SELECT bd_edition.IMG_COUV FROM bd_edition)
SELECT bd_tome.IMG_COUV FROM bd_tome WHERE bd_tome.IMG_COUV IS NOT NULL AND NOT EXISTS (SELECT NULL FROM bd_edition WHERE bd_tome.IMG_COUV = bd_edition.IMG_COUV);
SELECT bd_tome.IMG_COUV FROM bd_tome LEFT JOIN bd_edition ON bd_tome.IMG_COUV = bd_edition.IMG_COUV WHERE bd_tome.IMG_COUV IS NOT NULL AND bd_edition.IMG_COUV IS NULL;
*/