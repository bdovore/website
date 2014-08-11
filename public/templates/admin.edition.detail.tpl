<table class="tableaux_admin">
    <tr> 
        <td width="16%" class="label"><div align="center">Editeur</div></td>
        <td width="12%"><input name="txtEditeurId" type="text" value="{IDEDIT}" size="5" /></td>
        <td width="58%"><input name="txtEditeur" type="text" value="{EDITEUR}" size="30" /></td>
        <td width="14%">
            <a href="{URLEDITEDIT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('editeur',document.myform.txtEditeur.value,'myform','txtEditeur',true)" />
        </td>
    </tr>
    <tr> 
        <td class="label"><div align="center">Collection</div></td>
        <td><input name="txtCollecId" type="text" value="{IDCOLLEC}" size="5" /></td>
        <td><input name="txtCollec" type="text" value="{COLLECTION}" size="30" /></td>
        <td>
            <a href="{URLEDITCOLL}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a> 
            <img src="{URLSITEIMAGE}site/loupe.gif" width="24" height="14" onClick="newWindow('collection',document.myform.txtCollec.value,'myform','txtCollec',false,document.myform.txtEditeurId.value)" />
        </td>
    </tr>
    <tr> 
        <td colspan="4" class="label">
            <table class="tableaux_admin_sans-bord">
                <tr> 
                    <td align="center">
                        ISBN-13 (EAN) : 
                        <input name="txtEAN" type="text" value="{EAN}" size="14" />
                    </td>
                    <td align="center">
                        ISBN-10 : 
                        <input name="txtISBN" type="text" value="{ISBN}" size="10" />
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr> 
        <td colspan="4" class="label">
            <table class="tableaux_admin_sans-bord">
                <tr> 
                    <td width="22%" align="center" title="si 0000-00-00 apparaît, cela signifie que le membre a mal saisi la date">
                        Date Parution
                        <div class="petite_police" style="margin-bottom: 10px;">(aaaa-mm-jj)</div>
                        <input name="txtDateParution" type="text" id="txtDateParution" value="{DTPAR}" size="10" class="{PARUTION_0}" />
                        <br />
                        <label for="FLAG_DTE_PARUTION">Introuvable <input name="FLAG_DTE_PARUTION" type="checkbox" id="FLAG_DTE_PARUTION" value="1" {CHKFLAG_DTE_PARUTION} /></label>
                    </td>
                    <td width="1%" align="center"></td>
                    <td width="8%" align="center">
                        <div>Tirage de t&ecirc;te</div>
                        <input type="checkbox" name="chkTT" value="checkbox" {ISTT} />
                    </td>
                    <td width="1%" align="center"></td>
                    <td width="68%" align="center">
                        Description de l'édition<br />
                        <textarea name="txtComment" class="textarea_description_edition">{COMMENT}</textarea>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>