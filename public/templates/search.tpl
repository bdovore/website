<SCRIPT language="Javascript">
<!--
function Chargement(){
    d = document.add_collect;
    for (i = 0; i < d.elements.length; i++){
        if (d.elements[i].name == "sel_alb[]"){
            d.elements[i].checked = true;
        }
    }
}
//-->
</SCRIPT>

<div class="cadre1">
            <div align="center">
                <form name="add_collect" method="post" action="addalbum.php">
                    <p><b>{NBREPONSES}</b></p>
                    <p>{NAVPAGE}</p>
                    <table width="90%" border="1" align="center" cellpadding="5" cellspacing="0" bordercolor="#000000" frame="border" rules="all">
                        <tbody>
                            <!-- BEGIN ResultBlock -->
                            <tr bgcolor="EED8BF">
                                <td width="75%">
                                    <a href="{URLSERIE}">
                                        <strong>
                                            <font color="#990000" size="-1" face="Verdana, Arial, Helvetica, sans-serif">
                                                {SERIE}
                                            </font>
                                        </strong>
                                    </a>
                                    {EDITSERIE}
                                    <br />
                                    Titre :
                                    <a href={URLALBUM}>
                                        <font size="-1" face="Verdana, Arial, Helvetica, sans-serif" color="#990000">
                                            {TITRE}
                                        </font>
                                    </a>
                                    {EDITALBUM}
                                    <br />
                                    Editeur : {EDITEUR}
                                </td>
                                <td width="20%">
                                    <a href="{URLSCEN}">
                                        {SCEN}
                                    </a>
                                    {SEP}
                                    <a href="{URLDESS}">
                                        {DESS}
                                    </a>
                                </td>
                                <td width="5%">
                                    <input type="checkbox" name="sel_alb[]" value="{IDALBUM}" />
                                    <cite>(*)</cite>
                                </td>
                            </tr>
                            <!-- END ResultBlock -->
                        </tbody>
                    </table>
                    <br />

                    <!-- BEGIN ButtonBlock -->
                    <table width="90%" border="0">
                        <tr>
                            <td width="75%">&nbsp;</td>
                            <td align="right">
                                <a name="ici" href="#ici" onClick="Chargement();">
                                    Tout selectionner
                                </a>
                            </td>
                        </tr>
                    </table>
                    <p>{NAVPAGE}&nbsp;</p>
                    <table width="20%" border="0">
                        <tr>
                            <td>
                                <div align="center">
                                    Achat futur :
                                    <input name="cb_achat" type="checkbox" id="cb_achat2" value="checkbox" />
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input name="B_Add" type="submit" id="B_Add2" value="Ajouter &agrave; ma collection" />
                            </td>
                        </tr>
                    </table>
                    <p>
                        <input name="str_search" type="hidden" id="str_search" value="{SEARCHSTR}" />
                        <input name="numRow" type="hidden" id="numRow" value="{NBROW}" />
                        <input name="numPage" type="hidden" id="numPage" value="{PAGENUM}" />
                        <input name="modeSearch" type="hidden" id="modeSearch" value="{MODESEARCH}" />
                    </p>
                    <p>
                        <cite>(*) Vous devez vous authentifier pour ajouter des titres &agrave; votre collection</cite>
                    </p>
                </form>
                <!-- END ButtonBlock -->
            </div>
</div>