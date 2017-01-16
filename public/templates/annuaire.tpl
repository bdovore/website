<div class="cadre1">
            <div style="display: block; position: relative;">
                <span style="float: left;">
                    <form name="search_user" method="post" action="users.annu.php">
                        Rechercher un utilisateur :
                        <input type="text" name="pseudo" />
                        <input type="submit" name="Submit" value="Envoyer" />
                    </form>
                </span>
                <span style="margin-left: 20px; margin-top: 5px; background-color: #C8857C; color: #990000; padding: 3px; font-weight: bold;">
                    <a href="http://www.mappemonde.net/carte/BDovore/Monde.html" style="color: #990000">
                        Les BDovores sur la mappemonde !
                    </a>
                </span>
            </div>
            <div class="spacer">&nbsp;</div>
            <table width="30%" height="29" class="donnees">
                <tr>

                    <td width="100%" class="entete">
                        <div align="center">
                            <font color="#0000FF"><strong>
                                <a href="users.annu.php?cle=1&sort=asc{PSEUDO}">
                                    <img src="{URLSITEIMAGE}flechehaut.gif" width="12" height="12" hspace="5" border="0" alt="flèche haut"/>
                                </a>
                                Pseudo
                                <a href="users.annu.php?cle=1&sort=DESC{PSEUDO}">
                                    <img src="{URLSITEIMAGE}flechebas.gif" width="12" height="12" hspace="5" border="0" alt="flèche bas" />
                                </a>
                            </strong></font>
                        </div>
                    </td>
                </tr>

                <!-- BEGIN UsersBlock -->
                <tr>
                    <td width="40%" class="data">
                        <div align="center">
                            <font color="#000000">
                                {USERNAME}
                            </font>
                        </div>
                    </td>
                </tr>
                <!-- END UsersBlock -->

            </table>
            <br />
            <div align="center">
                {NAVBLOCK}
            </div>
        </div>
