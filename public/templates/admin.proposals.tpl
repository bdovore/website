<div class="fond_admin">
    <div class="titre_admin">{TITRE_ADMIN}</div>
    <div class="date_before_valid">{DATEBEFOREVALID}</div>
    <table class="donnees" align="center">
        <tr>
            <td width="70" class="entete_admin"><a href="../admin/adminproposals.php?cle=1&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" /></a>
                ID
            <a href="../admin/adminproposals.php?cle=1&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" /></a></td>
            <td width="83" class="entete_admin"><a href="../admin/adminproposals.php?cle=2&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>
                Date
            <a href="../admin/adminproposals.php?cle=2&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></td>
            <td width="131" class="entete_admin"><a href="../admin/adminproposals.php?cle=3&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>
                Utilisateur
            <a href="../admin/adminproposals.php?cle=3&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></td>
            <td width="166" class="entete_admin"><a href="../admin/adminproposals.php?cle=5&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>
                Série
            <a href="../admin/adminproposals.php?cle=5&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></td>
            <td width="166" class="entete_admin"><a href="../admin/adminproposals.php?cle=4&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>
                Titre
            <a href="../admin/adminproposals.php?cle=4&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></td>
            <td width="70" class="entete_admin">
                Statut
            </td>
            <td width="30" class="entete_admin"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" title="Consulter la proposition" /></td>
            <td width="30" class="entete_admin"><img src="{URLSITEIMAGE}poubelle.gif" width="25" height="24" title="Supprimer la proposition" /></td>
        </tr>

        <!-- BEGIN PropBlock -->
        <tr>
            <td class="data_admin">{ID}</td>
            <td class="data_admin_petit">{DATE}</td>
            <td class="data_admin">{USER}</td>
            <td class="data_admin">{SERIE}</td>
            <td class="data_admin">{TITRE}</td>
            <td class="data_admin" style="background-color: {COLOR_STATUS};">{OPTIONSTATUS}</td>
            <td class="data_admin"><a href="{URLEDIT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a></td>
            <td class="data_admin" {STYLE_VALIDDELAY}><a href="{URLDELETE}"><img src="{URLSITEIMAGE}poubelle.gif" width="20" height="21" border="0" /></a></td>
        </tr>
        <!-- END PropBlock -->
    </table>
</div>

