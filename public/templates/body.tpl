<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>{PAGETITLE}</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="fr" />
	<meta name="description" content="{DESCRIPTION} BDovore - Le site des collectionneurs de BD. Gérer votre collection de bandes dessinées en ligne et gratuitement !" />
	<meta name="keywords" content="bd, bandes dessinées, bandes, dessinées, bande, dessinée, profiler, bdprofiler, comics, manga, collection, collections, bdvore, collectionneur, dedicace, auteurs, albums, editeurs, gratuit, base de données, {PAGEKEYWORD}" />
	<meta name="author" content="Tomlameche/Latruffe/Zéas" />
	<meta name="identifier-url" content="http://www.bdovore.com" />
	<meta name="reply-to" content="" />
	<meta name="revisit-after" content="7 days" />
	<meta name="robots" content="all" />
	  
	<link href="{URLSITE}style/style1.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="{URLSITE}script/shadowbox-3.0.3/shadowbox.css">
	<script src="{URLSITE}script/script.js" type="text/javascript"></script>
	<script src="{URLSITE}script/library.js" type="text/javascript"></script>
	<script src="{URLSITE}script/xhr.js" type="text/javascript"></script>
	<script type="text/javascript" src="{URLSITE}jquery-1.8.2.min.js"></script>
	<script type="text/javascript" src="{URLSITE}jquery.autocomplete.js"></script> 
	<script type="text/javascript" src="{URLSITE}script/shadowbox-3.0.3/shadowbox.js"></script>
	<script type="text/javascript">
	Shadowbox.init({
		language: "fr",
		players: ["iframe"],
		}); 
	</script>
	<link rel="shortcut icon" href="favicon.ico" />
	<!-- Declaration fil rss -->
	<link rel="alternate" type="application/rss+xml" title="BDovore - Les dernières news du site" href="{URLSITE}rss-news.php" />
	<link rel="alternate" type="application/rss+xml" title="BDovore - Les derniers albums ajoutés" href="{URLSITE}rss.php" />
	<link rel="alternate" type="application/rss+xml" title="BDovore - Les derniers avis sur les albums" href="{URLSITE}rss-avis.php" />
	<link rel="alternate" type="application/rss+xml" title="BDovore - Les derniers avis sur les séries/one-shots" href="{URLSITE}rss-avis-serie.php" />
	<link rel="alternate" type="application/rss+xml" title="Bruit De Bulles - Chroniques, rubriques et actu BD" href="http://www.bruitdebulles.com/spip.php?page=backend" />
</head>

<body onLoad="{BODYONLOAD}">


	
	

		
	<div id="content">
		<div class="colmask rightmenu">
	    <div class="colleft">
	        <div class="col1wrap">
	            <div class="col1" style="position: relative; z-index: 10;">
   <div class="fond"></div>
	                <!-- Column 1 start -->
					{BODY}
					<!-- Column 1 end -->
	            </div>
	        </div>
	        
	    </div>
	</div>	
	</div>	
		
	
	<div id="footer" style="position: relative; z-index: 10;">
   <div class="fond"></div>

		<div id="titre_avertissement"> 
			Avertissement
		</div>
		<div id="texte_avertissement">
			Les images présentes sur ce site sont la propriété 
			de leurs auteurs et éditeurs respectifs et sont soumises à copyright. 
			Leur utilisation n'a pour but que d'illustrer l'information contenue 
			dans la base de donnée du site. Cependant, si ces derniers le désirent, 
			ces images seront retirées sur simple demande de leur part.
		</div>
		<div id="texte_avertissement">
			<font color="#990000">
			Lien destiné au référencement par les moteurs de recherche : 
			</font>
			<a href="{URLSITE}indexation.php">Liste des séries</a>
		</div>
	</div>


</body>
</html>