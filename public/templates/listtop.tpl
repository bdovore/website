<div class="cadre1">
<h2>Le Guide BD</h2>
		<a href="listlast.php?rb_mode=futur">A paraitre</a> 
		- <a href="listlast.php?rb_mode=last_par">Derni&egrave;res parutions</a>
		- <a href="listop.php?rb_mode=top_vote">Top des notes</a> 
		- <a href="listop.php?rb_mode=top_rep">Albums les plus répandus</a>
		- <a href="listlast.php?rb_mode=last_cmt">Derniers commentaires</a> 

		<table width="100%" align="center" cellpadding="10" cellspacing="10">	  
			<tr bordercolor="#FFFFFF">
		  		<td valign="top" bordercolor="#FFFFFF"> 
            		<form name="form1" method="post" action="listop.php">
              			<table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"   frame="box" rules="groups">
              				<tr>
                				<td><label for="rb_list1"><input id="rb_list1" name="rb_list" type="radio" value="album" {ALBCHECK}>
                  				Albums</label><br />
				  				<label for="rb_list2"><input id="rb_list2" type="radio" name="rb_list" value="serie" {SERCHECK}>
                  				Séries</label> 
				  				</td>
                				<td>
                				<label for="rb_top1"><input id="rb_top1" type="radio" name="rb_mode" value="top_vote" {TOPCHECKED}>
                  				Top des votes</label><br /> 
			                  <label for="rb_top2"><input id="rb_top2" type="radio" name="rb_mode" value="top_rep" {REPCHECKED}>
                  				Les plus r&eacute;pandus</label>
								</td>

                				<td align="center">Genre : 
                  				<select name="sel_genre" size="1" id="sel_genre">
								{OPTIONS}
        		 				 </select>
        		 				 <br /><input type="submit" name="Submit" value="Envoyer"></td>
              			</tr>
            		</table>
  				</form>
  			</td>
  		</tr>
	 </table>
</div>
<div class="cadre1">

	<table width="100%" border="1" align="center" cellpadding="5" cellspacing="0" bordercolor="#000000" frame="hsides" rules="rows">
	<!-- BEGIN AlbBlock -->
   		<tr>
        	
          <td width="110"> <div align="left"><a href={URLALBUM} title="{TITRE}"><img src='{URLSITEIMAGE}couv/{IMGCOUV}'  border="none" width="100" heigh="140"></a>
              <br>
              <font size="-2">&copy; {EDITEUR}</font> </div></td>
        	<td valign="top">
				<p><a href={URLALBUM}><font size="-1" face="Verdana, Arial, Helvetica, sans-serif" color="#990000"><strong>{TITRE}</strong></font></a> 
            	{EDITALBUM}<br/>
            	Série : <a href="{URLSERIE}">{SERIE}</a> {EDITSERIE}<br/>
            	Editeur : {EDITEUR} <br />
          		Scénario :<a href="{URLSCEN}">{SCEN}</a> <br />
				Dessin : <a href="{URLDESS}">{DESS}</a><br />
				Note : {MOYENNE} pour {NBVOTE} vote(s)<br />
				{NBUSER} utilisateurs possèdent cet album
				</p>
			</td>
      </tr>
	  <!-- END AlbBlock -->
	  <!-- BEGIN SerBlock -->
   	  <tr>
          <td width="110"> <div align="left"><a href={URLSERIE} title="{SERTITRE}"><img src='{URLSITEIMAGE}couv/{IMGCOUV}'  border="none" width="100" heigh="140"></a>
            </div></td>       
    	<td valign="top"> <a href={URLSERIE}><font size="-1" face="Verdana, Arial, Helvetica, sans-serif" color="#990000"><strong>{SERTITRE}</strong></font></a> 
		{EDITALBUM}<br/>
          Avancement : {SERAVCT} {EDITSERIE} <br>
			Note : {MOYENNE}<br>
          {NBUSER} utilisateurs possèdent un album de cette s&eacute;rie
		</td>
      </tr>
	  <!-- END SerBlock -->
  </table>
	<div align="center"><a href="listop.php?{PREC}">Pr&eacute;c&eacute;dent</a> 
  <a href="listop.php?{SUIV}">Suivant</a></div>
</div>