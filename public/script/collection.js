/* 
 * @author  Tom
 * Fonctions pour ajout, suppression, modifications des albums dans la collection
 * Principe : appel par ajax pour toutes les infos persos
 * Retours : code erreur si pb, vide sinon
 * 
 * Seul le user connecté peut modifier les infos dans la collection 
 * 
 * Avis aux hackers en herbe ou chevronnés : suis preneur d'un coup de main pour la partie sécurité, j'y connais rien...
 */

function addAlbum(id_tome, id_edition, flg_achat) {
	$("#addAlbum"+id_edition).html("<img src='"+$.bdovore.URL+ "script/ajax-loader.gif'>");
    var url = $.bdovore.URL+"macollection/majcollection?id_edition="+id_edition+"&id_tome="+id_tome+"&flg_achat="+flg_achat;
    
    $.getJSON(url, function (data) {
        
           if (data.length == 0) {
               $("#addAlbum"+id_edition).html("Album ajouté à votre collection !");
               $("#info_collection").show();
               if (flg_achat == "O") {
                   $("#cb_achat").attr('checked', true);
               }
               offset = $("#info_collection").offset().top;
                $('html, body').animate({scrollTop: offset}, 'slow');
           }
           else {
               alert("Une erreur est survenue. Veuillez contacter l'administrateur du site.");
           }
        
        }

    );
}


function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function deleteEdition(id_edition){
    if (confirm("Supprimer l'édition de votre collection ?")) {
        $("#addAlbum"+id_edition).html("<img src='./script/ajax-loader.gif'>");  
        var url = "./macollection/deleteAlbum?id_edition="+id_edition;
        
        $.getJSON(url, function (data) {
            if (data.length == 0) {
                $("#addAlbum"+id_edition).html("Album supprimé de votre collection !");
                $("#info_collection").hide();
            }
            else {
                alert(data);
            }
        });
    }
}
/* TODO :  
 * - fonction getInfoCollection pour générer le formulaire de saisie ; 
 * - fonction setInfoCollection pour envoyer mise à jour des infos d'un album de la collection
 * 
 */


