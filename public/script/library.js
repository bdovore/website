
function getObj(objID) {
	if (document.getElementById) {
		return document.getElementById(objID);
	} else if (document.all) {
		return document.all[objID];
	} else if (document.layers) {
		return document.layers[objID];
	}
}

function goUrl(url) {
	window.location.href = url;
}

function Block_None_Div(id_div) {
	var div = getObj(id_div);
	if (div.style.display == "none")
		div.style.display = "block";
	else
		div.style.display = "none";
}

function SelectAllCheckbox(formulaire) {
	var nb;
	var chk;
	if (formulaire.SEL_ALL.value == 0) {
		chk = 1
	} else {
		chk = 0
	}
	nb = formulaire.elements.length;
	for ( var i = 0; i < nb; i++) {
		var e = formulaire.elements[i];
		e.checked = chk
	}
	formulaire.SEL_ALL.value = chk;
}

function ListSelectCheckbox(formulaire) {
	var nb;
	var chk;
	var listeid = '';
	nb = formulaire.elements.length;
	for ( var i = 2; i < nb; i++) {
		if (formulaire.elements[i].checked == 1) {
			var id = formulaire.elements[i].name;
			if (listeid == '') {
				listeid = id;
			} else {
				listeid = listeid + ',' + id;
			}
		}
	}
	return listeid;
}

// bug ie corrigé : cache les <select> quand le menu est visible
function voirSelect(v) {
	if (ie & !op) {
		elem = document.getElementsByTagName('select');
		for (i = 0; i < elem.length; i++)
			elem[i].style.visibility = v;
	}
}

// -------------------------------------------------------------------------------------------
// Affichage des bulles d'informations
// -------------------------------------------

document
		.write("<div ID='div_info_aide' STYLE='position:absolute; display:none; z-index:1000;background:'></div>");

var marge_x = 4;
var marge_y = 4;
var div_info_w = 350;
var div_width = 0;
var mouse_x = 0; // position x de la souris
var mouse_y = 0; // position y de la souris
var objDivInfo = null;

D = document;
ie = D.all ? 1 : 0;
op = window.opera ? 1 : 0;

// ---------- detection du credit de la souris. --------------

document.onmousemove = MouseMove;

function MouseMove(e) {
	if (navigator.appName != "Microsoft Internet Explorer") {
		if (!e)
			var e = window.event;
		if (e.pageX || e.pageY) {
			mouse_x = e.pageX;
			mouse_y = e.pageY;
		} else if (e.clientX || e.clientY) {
			mouse_x = e.clientX + document.body.scrollLeft
					+ document.documentElement.scrollLeft;
			mouse_y = e.clientY + document.body.scrollTop
					+ document.documentElement.scrollTop;
		}

	} else {
		// Modif proposé par TeDeum, merci à lui
		if (document.documentElement.clientWidth > 0) {
			mouse_x = event.x + document.documentElement.scrollLeft;
			mouse_y = event.y + document.documentElement.scrollTop;
		} else {
			mouse_x = event.x + document.body.scrollLeft;
			mouse_y = event.y + document.body.scrollTop;

		}
	}
}

// --------------------------------------------------------------
// utile pour l'utilisation de la iframe
function HandleTTTextSize(w, h) {
	var DocFrame_Aide = document.getElementById('Frame_Aide');
	DocFrame_Aide.style.width = w;
	DocFrame_Aide.style.height = h;

	div_info = self.div_info_aide.style;
	var Y = div_info.top;
	var X = div_info.left;

	if (div_info_y > parseInt(Y, 10)) {
		div_info.top = div_info_y - h - div_info_oy;
	}
	if (div_info_x > parseInt(X, 10)) {
		div_info.left = div_info_x - w - div_info_ox;
	}
	// div_info.display = "block";
}
var ctrl_time = 0;

function Affiche_Info(nom_div, type, fstyle) {

	// voirSelect('hidden');

	ctrl_time = 0;
	var texte = '';
	var type;
	var X, Y;

	objDivInfo = getObj('div_info_aide');

	switch (type) {
	case "info":
		break;
	case "desc":
		nom_div = "DESC_CHAMP_" + nom_div;
		break;
	case "ldap_fiche":
		texte = "<IFRAME ID=Frame_Aide SRC='"
				+ SCP_URL_APPLI
				+ "acces/aff_ldap_info_iframe.php?idft="
				+ nom_div
				+ "&cfstyle="
				+ fstyle
				+ "' NORESIZE FRAMEBORDER=0 SCROLLING=NO MARGINHEIGHT=0 MARGINWIDTH=0></IFRAME>";
		break;
	// default :
	// texte = "<IFRAME width=" + div_info_wif.toString() + " SRC='" +
	// SCP_URL_APPLI + "outils/affiche_aide.php?ID_AIDE=" + nom_div + "'
	// NORESIZE FRAMEBORDER=0 SCROLLING=NO MARGINHEIGHT=0
	// MARGINWIDTH=0></IFRAME>";
	// break;
	}

	if (getObj(nom_div + '_titre')) {

		type = "div";
	} else {
		type = "lib";
	}
	if (texte == '') {
		switch (type) {
		case "lib":
			texte = "<table class='" + fstyle + "'><tr><td>" + nom_div
					+ "</td></tr></table>";
			break;
		default:
			var titre = document.getElementById(nom_div + '_titre').innerHTML;
			var corps = document.getElementById(nom_div + '_corps').innerHTML;

			texte += "<table border=0 cellpadding=3 cellspacing=0 class="
					+ fstyle + ">";
			texte += "<tr><td class=" + fstyle + "1 with=100% nowrap>" + titre
					+ "</td>";
			texte += "</tr><tr><td class=" + fstyle + "0 nowrap>" + corps
					+ "</td></tr></table>";

			break;
		}
	}
	objDivInfo.innerHTML = texte
			+ "<!--[if lte IE 6.5]><iframe class=tom></iframe><![endif]-->";
	objDivInfo.style.display = "block";

	div_width = objDivInfo.offsetWidth; // largeur du div a afficher
	div_height = objDivInfo.offsetHeight; // hauteur du div a afficher

	aff_width = self.document.body.clientWidth; // largeur d'affichage
	aff_height = self.document.body.clientHeight; // hauteur d'affichage

	if ((mouse_x + div_width + marge_x) > aff_width)
		X = mouse_x - div_width - marge_x;
	else
		X = mouse_x + marge_x;

	if ((mouse_y + div_height + marge_y) > aff_height)
		Y = mouse_y - div_height - marge_y;
	else
		Y = mouse_y + marge_y;

	objDivInfo.style.left = X; // postion x de la bulle info
	objDivInfo.style.top = Y; // postion y de la bulle info

	return true;
}

function Cache_Info() {

	ctrl_time = 1;

	if (objDivInfo != null) {
		objDivInfo.style.display = "none";
		// voirSelect('visible');
	}

	return true;
}

// FIN Affichage des bulles d'informations
// --------------------------------------------
// -------------------------------------------------------------------------------------------
// Changement de tri des colonnes pour l'affichage
function ChangeTriCol(col_tri, sens_tri, formulaire, mode) {
	if (formulaire === undefined) {
		formulaire = document.affichage;
	}

	formulaire.col_tri.value = col_tri;
	formulaire.sens_tri.value = sens_tri;
	formulaire.daff.value = 0;

	if (mode === undefined) {
		formulaire.submit();
	} else {
		ValidFormXhr(formulaire);
	}
}

function ptq(q)
{
    /* parse the query */
    /* semicolons are nonstandard but we accept them */
    var x = q.replace(/;/g, '&').split('&'), i, name, t;
    /* q changes from string version of query to object */
    for (q={}, i=0; i<x.length; i++)
    {
        if (x[i].length > 0) {
            t = x[i].split('=', 2);
            name = unescape(t[0]);
            if (!q[name])         q[name] = [];
            if (t.length > 1) {
                    q[name][q[name].length] = unescape(t[1]);
            } else { /* nonstandard */
                 q[name][q[name].length] = true;
            }
        }
	}
   return q;
}

function param() {
	//substring(1) to remove the '?' at the begining of the querystring in the URL
    return ptq(location.search.substring(1).replace(/\+/g, ' '));
}

function string_to_slug(str) {
  str = str.replace(/^\s+|\s+$/g, ''); // trim
  str = str.toLowerCase();
  
  // remove accents, swap ñ for n, etc
  var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
  var to   = "aaaaeeeeiiiioooouuuunc------";
  for (var i=0, l=from.length ; i<l ; i++) {
    str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
  }

  str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
    .replace(/\s+/g, '_') // collapse whitespace and replace by _
    .replace(/-+/g, '-'); // collapse dashes

  return str;
}