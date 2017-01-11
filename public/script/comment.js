/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function nl2br(str, is_xhtml) {
  //  discuss at: http://phpjs.org/functions/nl2br/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // improved by: Philip Peterson
  // improved by: Onno Marsman
  // improved by: Atli Þór
  // improved by: Brett Zamir (http://brett-zamir.me)
  // improved by: Maximusya
  // bugfixed by: Onno Marsman
  // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //    input by: Brett Zamir (http://brett-zamir.me)
  //   example 1: nl2br('Kevin\nvan\nZonneveld');
  //   returns 1: 'Kevin<br />\nvan<br />\nZonneveld'
  //   example 2: nl2br("\nOne\nTwo\n\nThree\n", false);
  //   returns 2: '<br>\nOne<br>\nTwo<br>\n<br>\nThree<br>\n'
  //   example 3: nl2br("\nOne\nTwo\n\nThree\n", true);
  //   returns 3: '<br />\nOne<br />\nTwo<br />\n<br />\nThree<br />\n'

  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display

  return (str + '')
    .replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
function stripslashes(str) {
  //       discuss at: http://phpjs.org/functions/stripslashes/
  //      original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //      improved by: Ates Goral (http://magnetiq.com)
  //      improved by: marrtins
  //      improved by: rezna
  //         fixed by: Mick@el
  //      bugfixed by: Onno Marsman
  //      bugfixed by: Brett Zamir (http://brett-zamir.me)
  //         input by: Rick Waldron
  //         input by: Brant Messenger (http://www.brantmessenger.com/)
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //        example 1: stripslashes('Kevin\'s code');
  //        returns 1: "Kevin's code"
  //        example 2: stripslashes('Kevin\\\'s code');
  //        returns 2: "Kevin\'s code"

  return (str + '')
    .replace(/\\(.?)/g, function(s, n1) {
      switch (n1) {
        case '\\':
          return '\\';
        case '0':
          return '\u0000';
        case '':
          return '';
        default:
          return n1;
      }
    });
}

function getComment(page,id_tome,user_id) {
    var url = "./AlbumComment?";
    if (typeof(page) != "undefined") url = url + "page=" + page;
    if (typeof(id_tome) != "undefined") url = url + "&id_tome="+ id_tome;
    if (typeof(user_id) != "undefined") url = url + "&user_id=" + user_id;
    $("#listcomment").html(""); // on vide la liste
    $.getJSON(url,function (data) {
        if (data.length == 0) {
            $("#listcomment").append("<i>Aucun commentaire pour cet album actuellement. <a onclick='viewComment();'>Soyez le premier !</a><i>");
        }
        var idcmt = "";
        $.each(data,function (i, item) {
            idcmt = item.ID_TOME + "_" + i;
            $("#listcomment").append("<div class='listcomment'> <span id='noteTome"+idcmt + "'></span>  \n\
            Posté par <a href='./guest?user="+item.user_id + "' target='parent'>"+ item.username + "</a> le "+ item.DTE_POST +"  <p>     \n\
             " + stripslashes(nl2br(item.COMMENT)) + "</p> </div>");

            $('#noteTome'+ idcmt ).raty({score: item.NOTE/2.0 , readOnly: true});
            }
           )
        }
    )
  return 0;
}

function writeComment(id_tome,note,comment) {
    //$("#writecomment").html("<img src='./script/ajax-loader.gif'>");
    var url = "./AlbumComment/writeComment?";
    url += "id_tome="+id_tome;
    url += "&note="+note;
    url += "&comment="+encodeURIComponent(comment);
    $.getJSON(url, function(data){

        if (data.length == 0) {
            alert("Commentaire / note enregistré !");
        }
    });



}

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
};

function setCommentPrivate(user_id, id_tome){
    if (confirm("Supprimer le commentaire et le passer en commentaire privé ?")) {
        var url = "./AlbumComment/setCommentPrivate?user_id="+user_id+"&id_tome="+id_tome;
        $.getJSON(url, function(data){

            if (data.length == 0) {
                alert("Le commentaire est supprimé ! ");
                window.location.reload();
            } else {
                alert("Opus ! Une erreur est survenue, contacter l'admin... ");
            }
        });
    }
}