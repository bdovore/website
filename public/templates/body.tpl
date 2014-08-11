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


	<div id="header" style="position: relative; z-index: 10;">
   <div class="fondNoir"></div>
		<div id="logo">
			<a href="{URLSITE}"><img src="{URLSITEIMAGE}site/logo_bdovore2.gif" border="0" alt="logo BDovore" id="logo" /></a>
		</div>
		<div id="searchtable" style="position: relative; z-index: 10;">
   <div class="fond"></div>
			<ul id="menusite1">
				<li><a class="menuGene" href="{URLSITE}bdovore-tuto.php" title="C'est quoi ce bidule ?">Présentation</a></li>
				<li><a class="menuGene" href="http://forum.bdovore.com/index.php" title="Le forum">Forum</a></li>
				<li><a class="menuGene" href="{URLSITE}contacts.php" title="Les coordonnées des tauliers du bidule au cas où...">Contacts</a></li>
				<li><a class="menuGene" href="{URLSITE}browser.php" title="Le BDo'Browser ! Oh Yeah!">L'index BD</a></li>
				<li><a class="menuGene" href="{URLSITE}listop.php" title="Le top des votes, les albums les plus répandus, les derniers commentaires, les dernières sorties...">Le Guide BD</a></li>
				{ACCESADMIN}
			</ul>
			<form action="{URLSITE}membres/search.php" method="post" name="search" target="_self">
			<input name="str_search" type="text" id="str_search" onblur="if (!this.value) this.value='Rechercher...';" onfocus="if (this.value=='Rechercher...') this.value='';" size="20" maxlength="50" value="Rechercher..." /> 
			<input type="submit" name="Submit" value="Ok" />
			</form>
		</div>
		<div id="login">
			{LOGINBARRE}
		</div>
		<hr class="expSep"/>
	</div>
	

		
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
	        <!-- Debut de la partie de droite -->
	        <div class="col2">
	            <!-- Column 2 start -->
	            {MENUBARRE}
	            {ACTUAIR}
	            {LASTAVIS}
				<!-- Column 2 end -->
	        </div>
	        <!-- Fin de la partie de droite -->
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