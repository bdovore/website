{MENUCOLL}
<div class="cadre1">
            <h3>Mon actualit&eacute;</h3>
            <form action="{ACTION}" method="post">
                <table border="0">
                    <tr>
                        <td>
                            <span class="texte">
                                Voir les nouveautés concernant :
                            </span>
                        </td>
                        <td>&nbsp;</td>
                        <td>
                            <span class="texte">
                                Parues depuis :
                            </span>
                        </td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <select name="lstSource" size="1">
                                {OPTSOURCE}
                            </select>
                        </td>
                        <td>&nbsp;</td>
                        <td>
                            <select name="lstDuree">
                                {OPTDUREE}
                            </select>
                        </td>
                        <td>&nbsp;</td>
                        <td>
                            <input type="submit" name="Submit" value="Rafraîchir" />
                        </td>
                        <td>&nbsp;</td>
                        <td>
                            <a href="{URLSITE}membres/aut_favoris.php">
                                Gérer mes auteurs favoris
                            </a>
                        </td>
                    </tr>
                </table>
            </form>
            <table width="100%" height="29" class="donnees">
                <tr>
                    <td width="15%" class="entete"><div align="center"><font color="#0000FF"><strong><a href="{URLSITE}membres/actualites.php?cle=1&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>Genre<a href="{URLSITE}membres/actualites.php?cle=1&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></strong></font></div></td>
                    <td width="20%" class="entete"><div align="center"><font color="#0000FF"><strong><a href="{URLSITE}membres/actualites.php?cle=4&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>Auteur<a href="{URLSITE}membres/actualites.php?cle=4&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></strong></font></div></td>
                    <td width="20%" class="entete"><div align="center"><font color="#0000FF"><strong><a href="{URLSITE}membres/actualites.php?cle=2&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>S&eacute;rie<a href="{URLSITE}membres/actualites.php?cle=2&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></strong></font></div></td>
                    <td width="5%" class="entete"><div align="center"><strong>Tome</strong></div></td>
                    <td width="25%" class="entete"><div align="center"><strong>Titre</strong></div></td>
                    <td width="15%" class="entete"><div align="center"><font color="#0000FF"><strong><a href="{URLSITE}membres/actualites.php?cle=3&sort=asc&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0"></a>Date de Parution <a href="{URLSITE}membres/actualites.php?cle=3&sort=DESC&duree={DUREE}&lstSource={SRC}"><img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0"></a></strong></font></div></td>
                </tr>
                <!-- BEGIN DetailBlock -->
                <tr>
                    <td width="15%" class="data"><div align="center">{GENRE}</div></td>
                    <td width="20%" class="data"><div align="center">{AUTEUR}</div></td>
                    <td width="20%" class="data"><div align="center"><a href="{URLSITE}serie.php?id_serie={SERID}">{SERIE}</a></div></td>
                    <td width="5%" class="data"><div align="center">{TOME}</div></td>
                    <td width="25%" class="data"><a href="{URLTITRE}">{TITRE}</a></td>
                    <td width="15%" class="data"> <div align="center">{DTEPAR}</div></td>
                </tr>
                <!-- END DetailBlock -->
            </table>
            <p class="texte">{BARRENAVIGATION}</p>
        </div>