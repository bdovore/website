// Creation et utilisation d'éléments xmlhttprequest

var retourXhr = null; // données de retour

document.write('<div id="XhrChargementDiv" style="position:absolute; left:0; top:0; background-color: #FFFFFF ; z-index:10000; border: 1px solid #999999; display:none"></div>');

function getXhr(){
    if(window.XMLHttpRequest) // Firefox et autres
    {
        xhr = new XMLHttpRequest();
    }
    else if(window.ActiveXObject){ // Internet Explorer
        try {
            xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    else { // XMLHttpRequest non supporté par le navigateur
        alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
        xhr = false;
    }
    return (xhr);
}

function CreateFormQuerystring(frm,flag) {
    // Fonction qui recherche le nom et la valeur de tous les éléments d'un formulaire frm
    // flag (booléen) indique si les valeurs null doivent être renvoyer
    var tab_data = new Array();

    for (var l = 0; l < frm.elements.length; l++)
    {
        var eleform=frm.elements[l];

        var E_name=eleform.name;
        var E_value='';

        switch(eleform.type)
        {
            case 'file' :
            break;
            case 'radio' :
            case 'checkbox' :
            if (eleform.checked)
            {

                E_value+=encodeURIComponent(eleform.value);
                if ((E_value != '') || (flag)) tab_data.push(E_name+'='+E_value);
            }
            break;
            case 'select-one' :
            E_value=encodeURIComponent(eleform.options[eleform.selectedIndex].value);
            if ((E_value != '') || (flag)) tab_data.push(E_name+'='+E_value);
            break;
            case 'select-multiple' :
            for (var i=0; i<eleform.options.length; i++) {
                if (eleform.options[i].selected) {

                    // E_value+=encodeURIComponent(eleform.options[i].value);
                    // modification Loren 20100215
                    E_value=encodeURIComponent(eleform.options[i].value);

                    if ((E_value != '') || (flag)) tab_data.push(E_name+'='+E_value);
                }
            }
            break;
            default :
            //E_value=encodeURIComponent(eleform.value);
            E_value=eleform.value;

            if ((E_value != '') || (flag)) tab_data.push(E_name+'='+E_value);

        }
    }
    return tab_data.join("&");
}

function SubmitXhr(FormOrData,FunctionOrDiv,UrlXhr,flagnull)
{
    // création de l'élément XMLHTTPRequest
    var xhr = getXhr(); // objet XMLHttpRequest

    // construction de la chaine de paramètres
    if(FormOrData.method)
    {
        // c'est un formulaire
        var datas = CreateFormQuerystring(FormOrData,flagnull);
        var method_frm=FormOrData.method;
    }
    else
    {
        // c'est une chaine pré-formatée
        var datas = FormOrData;
        var method_frm='post';

    }
    document.body.style.cursor='wait';

    // On défini ce qu'on va faire quand on aura la réponse
    xhr.onreadystatechange = function(){
        switch (xhr.readyState)
        {
            case 1 :
            //$msg = "<img src='" + BDO_URL + "images/picto_loading.gif' border='0'>";
            $msg = "Chargement en cours...";
            MsgEvolXhr($msg);
            break;
            case 2 :
            //                  MsgEvolXhr('Envoi...');
            break;
            case 3 :
            //                  MsgEvolXhr('Réception...');
            break;
            case 4 :
            document.body.style.cursor='default';
            document.getElementById('XhrChargementDiv').style.display = "none";
            if (xhr.status == 200)
            {
                retourXhr = xhr.responseText;
                if(document.getElementById(FunctionOrDiv))
                {
                    // mise à jour du contenu du div
                    //document.getElementById(FunctionOrDiv).innerHTML=retourXhr;
                    setInnerHTMLiD(FunctionOrDiv,retourXhr);
                }
                else
                {
                    // ou execution de la fonction
                    // alert(retourXhr);
                    eval(FunctionOrDiv);
                }
            }
            else
            {
                MsgEvolXhr(xhr.status + '<br />' + UrlXhr);
            }
            break;
        }

    }
    method_frm = method_frm.toLowerCase();

    // envoi de la requete en fonction de la méthode
    switch(method_frm) {
        case "get":
        UrlXhr = (datas.length > 0) ? UrlXhr + "?" + datas : UrlXhr;
        xhr.open("GET", UrlXhr, true);
        xhr.send(null);
        break;
        case "post":
        xhr.open("POST", UrlXhr, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
        xhr.send(datas);
        break;
        default :
        return false;
        break;
    }

}

function MsgEvolXhr(msg)
{
    posMsgX = (largeur_fenetre() - 200) / 2;
    //posMsgY = (hauteur_fenetre() - 80) / 2;
    posMsgY = mouse_y;

    var msggtt = '<table border=0 algn=center width=200 height=80>';
    msggtt += '<tr><td align=center valign=middle><font color=#999999>';
    msggtt += msg;
    msggtt += '</font></td></tr>';
    msggtt += '</table>';

    document.getElementById('XhrChargementDiv').style.left = posMsgX+"px";
    document.getElementById('XhrChargementDiv').style.top = posMsgY+"px";

    document.getElementById('XhrChargementDiv').innerHTML=msggtt;
    document.getElementById('XhrChargementDiv').style.display = "block";
}

function largeur_fenetre()
{
    if (window.innerWidth) return window.innerWidth;
    else if (document.body && document.body.offsetWidth) return document.body.offsetWidth;
    else return 0;
}

function hauteur_fenetre()
{
    return screen.height;
    /*
    if (window.innerHeight) return window.innerHeight  ;
    else if (document.body && document.body.offsetHeight) return document.body.offsetHeight;
    else return 0;
    */
}

function innerDiv(id)
{
    document.getElementById(id).innerHTML=retourXhr;
}

function ValidFormXhr(formulaire)
{

    var dirname="dd"+formulaire.name;
    SubmitXhr(formulaire,'setInnerHTMLiD(\"'+dirname+'\",retourXhr)',formulaire.action,false);
}

function ValidFormBox(formulaire,widthDiv,heightDiv)
{
    var dirId="lightboxDetails";
    showLightbox('div','',widthDiv,heightDiv);
    SubmitXhr(formulaire,"innerDiv(\'"+dirId+"\')",formulaire.action,false);
}

function ValidUrlBox(url,widthDiv,heightDiv)
{
    var dirId="lightboxDetails";
    showLightbox('div','',widthDiv,heightDiv);
    SubmitXhr("typepage=xhr","innerDiv(\'"+dirId+"\')",url,false);
}


function ValidFormXhrReload(formulaire)
{
    var dirname='dd'+formulaire.name;
    SubmitXhr(formulaire,'setInnerHTMLiD(\"'+dirname+'\",retourXhr)',formulaire.action,false);
}

// setInnerHTML Sécurisé
function setInnerHTMLiD(idContent, HTML) {
    divContent = document.getElementById(idContent);
    divContent.innerHTML=HTML;
//    try {
//      var All=divContent.getElementsByTagName("*");
//      for (var i=0; i<All.length; i++) {
//        All[i].id=All[i].getAttribute("id")
//        All[i].name=All[i].getAttribute("name")
//        All[i].className=All[i].getAttribute("class")
//      }
//    } catch (ex) {}
    try {
      var AllScripts=HTML.extractTags("script");
      AllScripts.forEach(function (v) {

        eval(v);
      })
    } catch (ex) {}
    try {
      var AllStyles=HTML.extractTags("style");
      AllStyles.forEach(function (v) {
        var s=document.createStyleSheet()
        s.cssText=v;
        s.enabled=true;
      }, true)
    } catch (ex) {}
}

String.prototype2.extractTags=function(tag) {
    var matchAll = new RegExp('(?:<'+tag+'.*?>)((\n|\r|.)*?)(?:<\/'+tag+'>)', 'img');
    var matchOne = new RegExp('(?:<'+tag+'.*?>)((\n|\r|.)*?)(?:<\/'+tag+'>)', 'im');
    return (this.match(matchAll) || []).map(function(scriptTag) {
      return (scriptTag.match(matchOne) || ['', ''])[1];
    });
  }

Object.prototype2.forEach=function(delegate, ownpropertiesonly) {
        if (typeof(delegate)=="function") {
            if (this instanceof Array && typeof(ownpropertiesonly)=="undefined") {
                ownpropertiesonly=true;
            }
            for (key in this) {
                var ok = (!ownpropertiesonly);
                if (!ok) {
                    try {
                        ok=this.hasOwnProperty(key)
                    } catch (ex) {}
                }
                if (ok) {
                    try { delegate(this[key], key, this) } catch(e) {
                        // ...
                    }
                }
            }
        }
        return false;
    }

Object.prototype2.map=function(iterator) {
    var results = [];
    this.forEach(function(value, index) {
      results.push(iterator(value, index));
    });
    return results;
  }
