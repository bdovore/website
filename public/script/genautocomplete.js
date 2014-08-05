function addAutocomplete (idInput, idSpan, idHiddenField, url){
/* 
* Crée un champs de saisie autocompletion sur l'input d'id "idinput" et 
*	affiche le code dans le span idspan
*   remplit le champ hidden avec l'id dans idhidden
*/
        var cache = {};
        $( "#"+idInput ).autocomplete({
        minLength: 2,
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
            $(idHiddenField).val(ui.item.id);
            $(idSpan).text(ui.item.id);
        }
        });
    }
	