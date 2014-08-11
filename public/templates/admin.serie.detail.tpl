<table class="tableaux_admin">
    <tr class="petite_police" align="center"> 
        <td>&nbsp;</td>
        <td>#id-BDo</td>
        <td>titre</td>
        <td>&nbsp;</td>
        <td>ser</td>
    </tr>
    <tr align="center"> 
        <td width="13%" height="27">Série</td>
        <td width="9%"><input name="txtSerieId" type="text" value="{IDSERIE}" size="3" class="{CHAMPFORMSTYLE_NEWFSERIE}" /></td>
        <td width="60%"><input name="txtSerie" type="text" value="{SERIE}" style="width: 100%" class="{CHAMPFORMSTYLE_NEWFSERIE}" /></td>
        <td width="12%">
            <a href="{URLEDITSERIE}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('serie',document.myform.txtSerie.value,'myform','txtSerie',true)" />
        </td>
        <td width="9%"><input name="txtTri" type="text" id="txtTri" value="{TRI}" size="1" maxlength="3" class="{CHAMPFORMSTYLE}" /></td>
    </tr>
    <tr> 
        <td height="28" align="center">Genre</td>
        <td align="center">
            <input name="txtGenreId" type="text" id="txtGenreId" value="{IDGENRE}" size="1" class="{CHAMPFORMSTYLE}" />
        </td>
        <td colspan="3">
            <input name="txtGenre" type="text" id="txtGenre" value="{GENRE}" size="35" class="{CHAMPFORMSTYLE}" />
            <a href="{URLEDITGENRE}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('genre',document.input_form.txtGenre.value,'input_form','txtGenre',false)" />
        </td>
    </tr>
    <tr> 
        <td align="center">Status</td>
        <td colspan="4"><select name="chkFini" size="1" class="{CHAMPFORMSTYLE}">{OPTSTATUS}</select></td>
    </tr>
    <tr> 
        <td align="center">Nb final</td>
        <td colspan="4"><input name="txtNbTome" type="text" id="txtNbTome" value="{NBTOME}" size="2" class="{CHAMPFORMSTYLE}" style="text-align: center;" /> tomes</td>
    </tr>
    <tr align="center"> 
        <td>Intro Série</td>
        <td colspan="4"><textarea name="txtSerieHist" id="txtSerieHist" class="{CHAMPFORMSTYLE}champ_commentaire">{HISTOIRE_SERIE}</textarea></td>
    </tr>
</table>