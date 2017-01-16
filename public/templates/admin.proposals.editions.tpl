<div class="fond_admin">
    <div class="titre_admin">Nouvelles éditions en attente</div>
    <div style="margin: 0 0 7px 14px; font-weight: bold; text-align: center">{DATEBEFOREVALID}</div>
    <table class="donnees" align="center">
        <tr>
            <td width="70" class="entete_admin"><a href="../admin/adminpropeditions.php?cle=1&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" /></a>
                ID
            <a href="../admin/adminpropeditions.php?cle=1&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" /></a></td>
            <td width="83" class="entete_admin"><a href="../admin/adminpropeditions.php?cle=2&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" /></a>
                Date
            <a href="../admin/adminpropeditions.php?cle=2&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" /></a></td>
            <td width="131" class="entete_admin"><a href="../admin/adminpropeditions.php?cle=3&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" /></a>
                Utilisateur
            <a href="../admin/adminpropeditions.php?cle=3&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" /></a></td>
            <td width="166" class="entete_admin"><a href="../admin/adminpropeditions.php?cle=5&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" /></a>
                Série
            <a href="../admin/adminpropeditions.php?cle=5&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"/ ></a></td>
            <td width="166" class="entete_admin"><a href="../admin/adminpropeditions.php?cle=4&sort=asc"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" /></a>
                Titre
            <a href="../admin/adminpropeditions.php?cle=4&sort=DESC"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" /></a></td>
            <td width="30" class="entete_admin"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" /></td>
            <td width="30" class="entete"><img src="{URLSITEIMAGE}poubelle.gif" width="25" height="24" /></td>
        </tr>

        <!-- BEGIN PropBlock -->
        <tr>
            <td height="25" class="data_admin">{ID}</td>
            <td width="128" class="data_admin_petit">{DATE}</td>
            <td width="111" class="data_admin">{USER}</td>
            <td width="166" class="data_admin">{SERIE}</td>
            <td width="166" class="data_admin">{TITRE}</td>
            <td width="30" class="data_admin"><a href="{URLEDIT}"><img src="{URLSITEIMAGE}edit.gif" width="18" height="13" border="0" /></a></td>
            <td width="30" class="data_admin"><a href="{URLDELETE}"><img src="{URLSITEIMAGE}poubelle.gif" width="20" height="21" border="0" /></a></td>
        </tr>
        <!-- END PropBlock -->

  </table>
</div>