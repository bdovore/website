
<!-- cadre central de la page -->

<div class="cadre1">
<h1>Bienvenue sur BDovore</h1>
Gérer votre collection de bandes dessinées en ligne via une gargantuesque liste de plus de <strong>140 000</strong>
<a href="{URLSITE}/listlast.php">BDs</a> : <a href="{URLSITE}/browser.php?lev_id=51&totalRows_RecAuteur=11&let=manga&user=&rb_browse=genr&pageNum_RecAuteur=0#L151">Mangas</a>,
<a href="{URLSITE}/browser.php?lev_id=12&totalRows_RecAuteur=2&let=comics&user=&rb_browse=genr&pageNum_RecAuteur=0#L112">Comics</a>,
<a href="{URLSITE}/browser.php?lev_id=82&let=revue&user=&rb_browse=genr&pageNum_RecAuteur=0">revues</a>,
<a href="{URLSITE}/browser.php?lev_id=82&let=periodique&user=&rb_browse=genr&pageNum_RecAuteur=0">périodiques</a>,
<a href="{URLSITE}/browser.php?lev_id=14&totalRows_RecAuteur=1&let=documentaire&user=&rb_browse=genr&pageNum_RecAuteur=0#L114">ouvrages d'étude</a>,
<a href="{URLSITE}/browser.php?lev_id=4&totalRows_RecAuteur=2&let=art&user=&rb_browse=genr&pageNum_RecAuteur=0#L14">recueils d'illustrations</a>
et puis des <a href="{URLSITE}/browser.php?lev_id=12&totalRows_RecAuteur=2&let=comics&user=&rb_browse=genr&pageNum_RecAuteur=0#L112">BD</a>, des <a href="{URLSITE}/browser.php?lev_id=65&totalRows_RecAuteur=1&let=autobiographie&user=&rb_browse=genr&pageNum_RecAuteur=0#L165">BD</a>, des <a href="{URLSITE}/browser.php?lev_id=6&totalRows_RecAuteur=4&let=aventure&user=&rb_browse=genr&pageNum_RecAuteur=0#L16">BD</a>, des <a href="{URLSITE}/browser.php?lev_id=51&totalRows_RecAuteur=11&let=manga&user=&rb_browse=genr&pageNum_RecAuteur=0#L151">BD</a> et encore <a href="{URLSITE}/browser.php?lev_id=37&totalRows_RecAuteur=69&rb_browse=genr&pageNum_RecAuteur=1#L137">des BD</a>. 
<a href="./bdovore-tuto.php" style="font-weight: bold;">Découvrez BDovore !</a> 
</div>
		 
<div class="middle">
	<div class="middle_title">
		<h3><a href="{URLSITE}listlast.php?rb_mode=last_par">Dernières sorties</a></h3>
	</div>
	<div class="top_content">
		<a href={URLALLAST1} title="{TITLAST1}">
							<img src={IMGLAST1} alt="Couverture 1" class="imgtop" /> 
						</a>
		 <!-- BEGIN LastSortieBlock -->				
		<br><font class="pucenum">{NUMERO} - </font><a href={URLLASTITEM} title="{TITLASTITEM}">{TITLASTITEM}</a>
		<!-- END LastSortieBlock -->
	</div>
</div>
<div class="middle">
	<div class="middle_title">
		<h3><a href="{URLSITE}listlast.php?rb_mode=futur">Sorties à venir</a></h3>
	</div>
	<div class="top_content">
		<a href={URLALFS1} title="{TITFS1}">
							<img src='{IMGFS1}' alt="Couverture 1" class="imgtop" /> 
						</a>
		 <!-- BEGIN FuturSortieBlock -->				
		<br> <font class="pucenum">{NUMERO}- </font><a href={URLFSITEM} title="{TITFSITEM}">{TITFSITEM}</a>
		<!-- END FuturSortieBlock -->
	</div>
</div>

<div class="middle">
	<div class="middle_title">
		<h3><a href="listlast.php?rb_mode=last_cmt">Derniers avis</a></h3>
	</div>
	<div class="top_content">
		<a href={URLALLASTCMT1}	 title="{TITLASTCMT1}"><img src='{IMGLASTCMT1}' alt="Couverture 1" class="imgtop" /></a>
		 <!-- BEGIN ComBlock -->				
		<br><font class="pucenum">{NUMERO} - </font><a href={URLLASTCMT} title="{CMTLAST}">{CMTLAST}</a>
		<!-- END ComBlock -->
	</div>
</div>
<div class="clear"></div>
  	
<div class="contenu" id="news">
	<img src="{URLSITEIMAGE}site/rub_news.gif" alt="Rubrique des news" class="rub_title" /><a href="./rss-news.php"><img src="{URLSITEIMAGE}site/feed.png" class="rub_title" style="border: 0; margin: 0 0 13px -7px;" alt="logo fil rss" title="Suivez les news du site grace à ce fil rss" />
    </a>
	<p>{LASTNEWS}</p>
</div>
