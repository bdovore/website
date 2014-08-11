<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Test de sélection des séries</title>
<link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.10.2.custom.css"/>
<link rel="stylesheet" type="text/css" href="css/ajax.serie.css"/>

<script type="text/javascript" src="jquery/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="jquery/jquery-ui/jquery-ui-1.10.1.custom.min.js"></script>
<script language="JavaScript" type="text/javascript">
	$(document).ready(function() {
		// Bouton pour afficher / masquer les albums cachés
		$("#togglehide").button()
							.click(function( event ) {
							$(".excluded").toggle('drop',{},500);
							});
		// Champs Série
		$("#serie").button();
							
		$('#serie').focus(function() {
			$(this).val('');
			});	
		
		$('#serie').autocomplete({
					source : 'serie_p.php',
					minLength : 3,
					select : function(event, ui){
						$('#serie_id').val(ui.item.id);
						$("#albums").hide('blind');
						$("#albums").load('ajax.serie.alb.php?id_serie='+ui.item.id, function() {
							
							// Prise en charge des appels serveur
							$(".clickable").click(function(event) {

								// On découpe la réponse
								var maReg = new RegExp ("-", "g");
								var resultat = ((event.target).id).split(maReg);
								jQuery.ajax({
									type : 'GET',
									url: 'ajax.serie.action.php',
									data: {
										action: resultat[0],
										idtome: resultat[1],
										idedition: resultat[2]
									},
									success: function(data, textStatus, jqXHR) {
										//alert (data);
										var resp = $.parseJSON(data);
										refresh_alb(resp);
									},
									error: function(jqXHR, textStatus, errorThrown) {
										// Une erreur s'est produite lors de la requete
									}
								});
							});							
							
							// prise en charge des éléments à afficher
							if ($("#togglehide").is(':checked'))
								{
								$(".excluded").hide();
								};
							if ($("#expandable").is(':checked'))
								{
								$(".additional").show();
								}
							else
								{
								$(".additional").hide();
								}			
						}); // Affichage de la page
						$("#albums").show('blind',{},500);// Cache les éléments masqués
						}
					});
		
		// ON cache par défaut les détails sur les albums
		$(".additional").hide();
		$("#expandable").button()
							.click(function( event ) {
							$(".additional").toggle('drop',{},500);
							});	
	});
	
	// Fonction pour mettre à jour le champs série
	function ac_return(field, item){
        // on met en place l'expression régulière
        var regex = new RegExp('[0123456789]*-idcache', 'i');
        // on l'applique au contenu
        var nomimage = regex.exec($(item).innerHTML);
        //on récupère l'id
        id = nomimage[0].replace('-idcache', '');
		//alert(id);
        // et on l'affecte au champ caché
        $(field.name+'_id').value = id;
		//alert((field.name+'_id'));
		//alert($("serie_id").value);
	}	
	
	// gestion de l'affichage
	function refresh_alb(resp) {
		$("#"+resp[0]["idtome"]).removeClass();
		$("#"+resp[0]["idtome"]).addClass('album');
		$('[id^=af-'+resp[0]["idtome"]+'-]').attr("id",'af-'+resp[0]["idtome"]+'-'+resp[0]["idedition"]); 
		$('[id^=addrem-'+resp[0]["idtome"]+'-]').attr("id",'addrem-'+resp[0]["idtome"]+'-'+resp[0]["idedition"]);
		$('[id^=exclude-'+resp[0]["idtome"]+'-]').attr("id",'exclude-'+resp[0]["idtome"]+'-'+resp[0]["idedition"]);
		
		
		if (resp[0]["incollec"]=='N')
			{
			$("#addrem-"+resp[0]["idtome"]+'-'+resp[0]["idedition"]).text('Add');
			$("#exclude-"+resp[0]["idtome"]+'-'+resp[0]["idedition"]).removeAttr("disabled");
			if (resp[0]["exclu"]=='O')
				{
				$("#"+resp[0]["idtome"]).addClass('excluded');
				if ($("#togglehide").is(':checked'))
					{
					$(".excluded").hide('drop');
					};
				}
			else
				{
				$("#"+resp[0]["idtome"]).addClass('default');
				}
			}
		else
			{
			$("#addrem-"+resp[0]["idtome"]+"-"+resp[0]["idedition"]).text('Remove');
			$("#exclude-"+resp[0]["idtome"]+'-'+resp[0]["idedition"]).attr("disabled", "disabled");

			if (resp[0]["flgachat"]=='O')
				{
				$("#"+resp[0]["idtome"]).addClass('alb_af');
				}
			else
				{
				$("#"+resp[0]["idtome"]).addClass('incollec');
				}
			}
			
		// inclure ici une partie bloquant les boutons
		//alert ('La rep:'+resp[0]["statut"]+resp[0]["incollec"]);
		//alert ('rafraichissement'+id);
	};
	
	
</script>
</head>
<body>



<div id="GenMenu">
	<input type="checkbox" id="togglehide" /><label for="togglehide">Cacher les albums exclus</label>
	<input type="checkbox" id="expandable" /><label for="expandable">Afficher les auteurs</label>
    <div class="ui-widget">
	<label for="chef">Serie : </label>
	<input type="input" id="serie" value="" />
	<input type="hidden" name="serie_id" id="serie_id" value="" />
	</div>
</div>

<div class="albums" id="albums">
</div>


</body>
</html>
