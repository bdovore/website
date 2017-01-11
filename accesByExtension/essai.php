<?php header ( 'Content-Type: text/html; charset=UTF-8' );?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Test Auto completion</title>
<link rel="stylesheet" type="text/css" href="http://beta.bdovore.com/style/jquery-ui-1.10.1.custom.css"/>
<script type="text/javascript" src="http://beta.bdovore.com/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="http://beta.bdovore.com/js/jquery-ui-1.10.1.custom.min.js"></script>
<script language="JavaScript" type="text/javascript">
$(document).ready(function() {
    $('#serie').autocomplete({
    source : 'serie_p.php',
    minLength : 3,
    select : function(event, ui){
        $('#serie_id').val(ui.item.id); // met à jour le champ caché du formulaire avec la valeur
        }
    });
});



</script>
</head>
<body>
<form action="tester.php" method="post">
        <label for="chef">Serie : </label>
        <input type="input" id="serie" value="" />
        <input type="hidden" name="serie_id" id="serie_id" value="" /><br/>
        <input type="submit" name="action" value="Parcourir" />
</form>

</body>
</html>