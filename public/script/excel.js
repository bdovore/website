// Création des excel
function Creation_Excel(formulaire,type)
{
	var action_aff = formulaire.action;

	if (-1 < action_aff.indexOf("?")) {
			formulaire.action = action_aff + '&export='+type ;
	}
	else {
			formulaire.action = action_aff + '?export='+type ;
	}
	formulaire.submit();
	formulaire.action = action_aff ;

}
