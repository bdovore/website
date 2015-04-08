function addAutocomplete (idInput, idSpan, idHiddenField, url, min){
/* 
* Crée un champs de saisie autocompletion sur l'input d'id "idinput" et 
*	affiche le code dans le span idspan
*   remplit le champ hidden avec l'id dans idhidden
*/      
    
    if (! min) {
       var min = 2;
    }
    var cache = {};
    $( "#"+idInput ).autocomplete({
    minLength: min,
    source: function( request, response ) {
        var term = request.term;
        if ( term in cache ) {
        response( cache[ term ] );
        return;
        }
        $.getJSON( url, request, function( data, status, xhr ) {
        cache[ term ] = data;
        response( data );
        });
    },
    select: function( event, ui ) {
        $("#"+idHiddenField).val(ui.item.id);
        $("#"+idSpan).text(ui.item.id);
        
    }
    });
}
    
function addSelectOption(idInput,url,id_select){
    
    $("#"+idInput).empty();
    $.getJSON( url, function( data) {
        
       $.each(data, function( item ) {
           
           if (id_select == data[item].id) {
               select = "selected";
           }
           else {
               select = "";
           }
           $("#"+idInput).append("<option value='"+data[item].id+"'"+ select + ">"+htmlEscape(data[item].label)+"</option>")
       });
       }); 
    }
function htmlEscape(str) {
return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
