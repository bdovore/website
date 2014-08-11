<SCRIPT language="Javascript"> 
<!-- 
  
function Chargement(){ 
	d = document.add_collect;
	for (i = 0; i < d.elements.length; i++){
		if (d.elements[i].name == "sel_alb[]"){
			d.elements[i].checked = true;
		}
	}
} 

function test_value(){
	if (document.form1.lstEdition.value == '0'){
		mywindow=open("addedition.php?id_tome={TOMEID}",'myEdition','scrollbars=no,resizable=yes,width=530,height=380');	
	}
}

function add_element(elName, elValue){
	var newOpt = new Option(elName, elValue);
	var selLength = document.form1.lstEdition.length;
	document.form1.lstEdition.options[selLength] = newOpt;
	mywindow.close();
}

//--> 
</SCRIPT>

{MENUCOLL}
<form name="up_serie" method="post" action="userserie.php">
<div class="cadre1">
				<p>
					<font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
						<strong>Série : </strong>
					</font>
					{SERTITLE}
					<br />
					<font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
						<strong>Genre : </strong>
					</font>
					{SERGENRE}
					<br />
					<font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
						<strong>Albums dans la collection : </strong>
					</font>
					{NBALB}
					<br />
					<font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
						<strong>Avancement : </strong>
					</font>
					{SERFIN}
					<br />
					<font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif"> 
						<strong>Album(s) manquant(s) : </strong>
					</font>
					{SERCOMPLETE}
				</p>
				<p>
					<font color="#990000" size="-1">
						<strong>Note sur 10 : </strong>
					</font>
					<select name="ser_note" id="ser_note">
						{OPTIONSERNOTE}
					</select>
					<br />
					<font color="#990000" size="-1">
						<strong>Avis général de lecture</strong> (les notes et avis sont des "données partagées", accessibles par tous) :
					</font>
					<textarea name="cmtserie" class="champ_commentaire">{CMTSERIE}</textarea>
				</p>
			</div>

	<div class="cadre1">
				<input type="hidden" name="id_serie" value="{SERID}" /> 
				<input type="hidden" name="page" value="{NUMPAGE}" />
				<input type="hidden" name="src" value="serie" />
				<!-- BEGIN AlbBlock -->
				<table class="fond_fiche_album">
					<tr> 
						<td width="185px" valign="top">
							<img src="{URLSITEIMAGE}couv/{COUVALBUM}" width="180" />
							<a href="{URLAMAZON}" target="_blank" title="Commander cet album sur Amazon">
								{LINKAMAZON}
							</a>
						</td>
						<td align="left" valign="top">
							<table width="100%" height="75%" border="1" cellpadding="3" cellspacing="0" bordercolor="#000000" frame="border" rules="all">
								<tbody>
									<tr> 
										<td width="126" class="case_entete_fonce">
											Titre :
										</td>
										<td width="709" class="case_fond_clair">{ALBTITLE}</td>
									</tr>
									<tr> 
										<td class="case_entete_fonce">
											Série :
										</td>
										<td class="case_fond_clair">{SERTITLE}</td>
									</tr>
									<tr> 
										<td class="case_entete_fonce">
											Tome :
										</td>
										<td class="case_fond_clair">{NUMTOME}</td>
									</tr>
									<tr> 
										<td class="case_entete_fonce">
											Genre :
										</td>
										<td class="case_fond_clair">{ALBGENRE}</td>
									</tr>
									<tr> 
										<td class="case_entete_fonce">
											Scénario :
										</td>
										<td class="case_fond_clair">{ALBSCENAR}</td>
									</tr>
									<tr> 
										<td class="case_entete_fonce">
											Dessin :
										</td>
										<td class="case_fond_clair">{ALBDESSIN}</td>
									</tr>
									<tr> 
										<td class="case_entete_fonce">
											Couleur :
										</td>
										<td class="case_fond_clair">{ALBCOLOR}</td>
									</tr>
									<tr> 
										<td height="30" class="case_entete_fonce">
											Mon édition :</strong></font>
										</td>
										<td class="case_fond_clair">{ALBEDITION}</td>
									</tr>
									<tr> 
										<td class="case_entete_fonce">
											Identifiant :</font></strong>
										</td>
										<td class="case_fond_clair">
											ISBN-13 (EAN) : {EAN}
											<br/ >
											ISBN-10 : {ISBN}
											<br />
											ID-BDovore : {TOMEID}
										</td>
									</tr>
									{COMMENT_EDITION}
									<tr> 
										<td height="30" align=center valign=top class="case_entete_fonce">
											Note sur 10<br /><!-- A garder sur la meme ligne ou une ligne supplémentaire s'affiche après 10 sous IE -->
											&amp;
											<br />
											Avis de lecture
											<div style="margin-top: 10px; font-weight: normal; font-size: 0.8em;">
												(données partagées, accessibles par tous)
											</div>
										</td>
										<td class="case_fond_clair">
											<input name="id_tome[]" type="hidden" id="id_tome[]" value="{TOMEID}"  {DISABLEDUSERCOMMENT}/>
											<select name="note[]" id="note[]" {DISABLEDUSERCOMMENT}>
												{OPTIONNOTE}
											</select>
											<br />
											<textarea name="comment[]" class="champ_commentaire"  {DISABLEDUSERCOMMENT}>{USERCOMMENT}</textarea>
										</td>
									</tr>
								</tbody>
							</table>
							<p>
								<strong><font color="#990000" size="-1">Synopsis :</font></strong> 
								{ALBSTORY}
							</p>
							<p>
								<a href="prop_correct.php?alb_id={TOMEID}&id_edition={EDITIONID}" target="_blank">
									Proposer une correction
								</a>
							</p>
						</td>
					</tr>
				</table>
				<table width="100%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"   frame="box" rules="groups">
					<tr> 
						<td height="18">
							<font color="#990000" size="-1"><strong>Edition originale :</strong></font>
							<input name="cb_tete[]" type="checkbox" id="cb_tete[]" value="{EDITIONID}" {TETECHECKED} /> 
							<strong><font color="#990000" size="-1">D&eacute;dicace :</font></strong> 
							<input name="cb_dedicace[]" type="checkbox" id="cb_dedicace[]" value="{EDITIONID}" {DEDICHECKED} />
						</td>
					</tr>
					<tr> 
						<td height="18">
							<font color="#990000" size="-1"><strong>
								A acheter prochainement :
							</strong></font>
							<input name="cb_achat[]" type="checkbox" id="cb_achat[]" value="{EDITIONID}" {ACHATCHECKED} /> 
							<font color="#990000" size="-1"><strong>Date d'achat :  </strong></font>
							<input name="dte_achat[]" type="text" id="dte_achat[]" value="{DTEACHAT}" size="10" maxlength="10" />
							<font color="#990000" size="-1"><strong>Prix/cote : </strong></font>
							<input name="prix[]" type="text" id="prix[]" value="{PRIX}" size="4" maxlength="8" />
						</td>
					</tr>
					<tr> 
						<td>
							<font color="#990000" size="-1"><strong>Pr&ecirc;t :</strong></font> 
							<input name="cb_pret[]" type="checkbox" id="cb_pret[]" value="{EDITIONID}" {PRETCHECKED} /> 
							<font color="#990000" size="-1"><strong>Dernier emprunteur :</strong></font> 
							<input name="name_pret[]" type="text" id="name_pret[]" value="{NOMPRET}" size="15" maxlength="100" /> 
							<strong><font color="#990000" size="-1">Email :</font></strong> 
							<input name="email_pret[]" type="text" id="email_pret[]" value="{EMAILPRET}" size="20" maxlength="100" />
						</td>
					</tr>
					<tr> 
						<td>
							<font color="#990000" size="-1"><strong>Remarque personnelle (consultable par vous uniquement) :</strong></font> 
							<textarea name="remarque[]" class="champ_commentaire">{USERRQUE}</textarea>
						</td>
					</tr>
				</table>
				<p align="center"> 
					<input name="id_edition[]" type="hidden" id="id_edition[]" value="{EDITIONID}" />
				</p> 
				<!-- END AlbBlock -->
				<div align="right"></div>
				<div align="center">{NAVPAGE} </div>
				<div align="center"></div>
				<div align="center">
					<table width="20%" border="0">
						<tr> 
							<td>
								<div align="center">
									<input name="B_Add" type="submit" id="B_Add" value="Envoyer" />
								</div>
							</td>
						</tr>
					</table>
				</div>
	</div>
</form>