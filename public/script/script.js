//==================================================
// pop-up loupe
//==================================================
function newWindow(myCategory,myText,myForm,myElement,isMandatory,myEditeur) {
	if ((isMandatory == true) && (myText.length < 2)) {
		alert('Veuillez saisir au moins 2 caractères');
	}else {
		if (myCategory == "collection") {
			if (myEditeur == '') {
				alert("Veuillez saisir l'éditeur en premier");
			}else {
                mywindow=open("../admin/pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement + "&IdEditeur=" + myEditeur,"myname","scrollbars=yes,resizable=yes,width=350,height=270");
			}
		}else {
			mywindow=open("../admin/pickup.php?cat=" + myCategory + "&text=" + myText + "&ReturnDoc=" + myForm + "&ReturnElement=" + myElement,"myname","scrollbars=yes,resizable=yes,width=350,height=270");
		}
	}
}

function isValidDate(date) {
	return /^(\d{4}\-\d{2}-\d{2})$/.test(date);
}
