<div class="cadre1">
<h2>Connexion</h2>
</div>
<div class="cadre1">
<table width="64%" border="1" cellpadding="5" cellspacing="0" bordercolor="#FFFFCC" bgcolor="#DAC1B3"  frame="box" rules="groups">
    <tr>
        <td width="65%">
            <div align="center">
                <form name="form1" method="post" action="../membres/login.php?log=1">
                    <table width="90%" border="0">
                        <tr>
                            <td width="41%">
                                <font color="#990000" size="-1">
                                    <strong> Pseudo :</strong>
                                </font>
                            </td>
                            <td width="59%">
                                <input name="txtlogin" type="text" id="txtlogin" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <font color="#990000" size="-1">
                                    <strong>Mot de passe</strong>
                                </font>
                            </td>
                            <td>
                                <input name="txtmot2pass" type="password" id="txtmot2pass" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <font color="#990000" size="-1">
                                    <strong>Se connecter automatiquement &agrave; chaque visite</strong>
                                </font>
                                <input name="chkvisit" type="checkbox" id="chkvisit" value="1" />
                            </td>
                        </tr>
                        <tr>
                            <td height="32">&nbsp;</td>
                            <td>
                                <div align="center">
                                    <input name="LoginConnecter" type="submit" id="LoginConnecter" value="Valider" />
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </td>
        <td width="35%" align="center" valign="middle">{ERREURTYPE}</td>
    </tr>
    <tr>
        <td colspan="2">
            Mot de passe oubli&eacute; : Cliquez <a href="../membres/login.php?act=forgotpass">ici</a>
        </td>
    </tr>
</table>
</div>