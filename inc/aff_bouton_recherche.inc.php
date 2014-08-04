<?php

function aff_bouton_recherche ()
{
    if (isset($_POST['baff']) and ! isset($baff)) $baff = $_POST['baff'];
    
    if (($baff < BDO_NBLINEBYPAGE_MIN) or ($baff > BDO_NBLINEBYPAGE_MAX)) $baff = BDO_NBLINEBYPAGE_DEFAULT;
    
    /*
    $html = "
    <table border=0 cellspacing=1 cellpadding=2 class=information>
    <tr><td>" . LANG_AIDESEARCH2 . "</td></tr>
    <tr><td>" . LANG_AIDESEARCH3 . "</td></tr>
    </table>";
    */
    $html = "
    <table align=center border=0 cellpadding=0 cellspacing=0 width=100%>
    <INPUT type='hidden' name='SEL_ALL' value='0'>
    	<tr>
    	<td align=center width=100%>" . CadreVide("navigation", "100%", 2) . "</td>
    	" . EspaceTD() . "
    	<td align=center>" . Bouton("submit", "validSubmitSearch", LANG_RECHERCHER, "", "action_modification") . "</td>
    
    </tr>
    </table>
    <table align=center border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
    	<td class='cfg0'><input type=text name=baff value='" . $baff . "' size=3 maxlength=3 class=navigation> " . LANG_NBLIGNEPARPAGE . " (10 < ... < " . BDO_NBLINEBYPAGE_MAX . ")</td>
    	<td class='cfg0'  align=right><label for='chckall'>" . LANG_COCHERDECOCHER . "</label></td>
    	<td class='cfg0' nowrap><INPUT type='checkbox' id='chckall' name='rr' onClick='SelectAllCheckbox(this.form)'></td>
    </tr>
    </table>
    ";
    return $html;
}