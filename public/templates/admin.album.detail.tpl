<table class="tableaux_admin">
    <!--DWLayoutTable-->
    <tr> 
        <td width="15%" class="label">
            <div align="center">Titre</div>
        </td>
        <input name="txtTomeId" type="hidden" id="txtTomeId" value="{IDTOME}" />
        <td colspan="3" width="85%"><input name="txtTitre" type="text" id="txtTitre" value="{TITRE}" size="50" /></td>
    </tr>
    <tr> 
        <td colspan="4" class="label">
            <table width="100%">
                <tr>
                    <td width="25%" align="center">Type</td>
                    <td width="25%" align="center">Tome nº</td>
                    <td width="25%" align="center">Int&eacute;grale</td>
                    <td width="25%" align="center">Prix (ex:12.00)</td>
                </tr>
                <tr>
                    <td align="center">
                        <select name="lstType" size="1">{OPTTYPE}</select>
                    </td>
                    <td align="center"><input name="txtNumTome" type="text" id="txtNumTome" value="{TOME}" size="1" style="text-align: center;" /></td>
                    <td align="center"><input type="checkbox" name="chkIntegrale" value="checkbox" {ISINT} /></td>
                    <td align="center"><input name="txtPrixVente" type="text" id="txtPrixVente" value="{PRIX_VENTE}" size="3" style="text-align: center;" /></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr align="center"> 
        <td class="label">Intro Tome</td>
        <td colspan="3" valign="top">
            <textarea name="txtHistoire" class="champ_commentaire">{HISTOIRE}</textarea>
        </td>
    </tr>
        
        
    <tr> 
        <td width="88" class="label">Scenariste</td>
        <td width="37"><input name="txtScenarId" type="text" value="{IDSCEN}" size="5" /></td>
        <td width="278"><input name="txtScenar" type="text" value="{SCENARISTE}" size="30" /></td>
        <td width="62">
            <a href="{URLEDITSCEN}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenar.value,'myform','txtScenar',true)" />
        </td>
    </tr>
    <tr> 
        <td class="label">(Optionnel)</td>
        <td><input name="txtScenarAltId" type="text" value="{IDSCENALT}" size="5" /></td>
        <td><input name="txtScenarAlt" type="text" value="{SCENARISTEALT}" size="30" /></td>
        <td>
            <a href="{URLEDITSCENALT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtScenarAlt.value,'myform','txtScenarAlt',true)" />
        </td>
    </tr>
    <tr> 
        <td class="label">Dessinateur</td>
        <td><input name="txtDessiId" type="text" value="{IDDESS}" size="5" /></td>
        <td><input name="txtDessi" type="text" value="{DESSINATEUR}" size="30" /></td>
        <td>
            <a href="{URLEDITDESS}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0"></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessi.value,'myform','txtDessi',true)" />
        </td>
    </tr>
    <tr> 
        <td class="label">(optionnel)</td>
        <td><input name="txtDessiAltId" type="text" value="{IDDESSALT}" size="5" /></td>
        <td><input name="txtDessiAlt" type="text" value="{DESSINATEURALT}" size="30" /></td>
        <td>
            <a href="{URLEDITDESSALT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0"></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtDessiAlt.value,'myform','txtDessiAlt',true)" />
        </td>
    </tr>
    <tr> 
        <td class="label">Coloriste</td>
        <td> <input name="txtColorId" type="text" value="{IDCOLOR}" size="5" /></td>
        <td> <input name="txtColor" type="text" value="{COLORISTE}" size="30" /></td>
        <td>
            <a href="{URLEDITCOLOR}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtColor.value,'myform','txtColor',true)" />
        </td>
    </tr>
    <tr>
        <td class="label">(Optionnel)</td>
        <td><input name="txtColorAltId" type="text" value="{IDCOLORALT}" size="5" /></td>
        <td><input name="txtColorAlt" type="text" value="{COLORISTEALT}" size="30" /></td>
        <td>
            <a href="{URLEDITCOLORALT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('auteur',document.myform.txtColorAlt.value,'myform','txtColorAlt',true)" />
        </td>
    </tr>
</table>