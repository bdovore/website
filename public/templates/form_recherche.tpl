<div id="searchtable">
    <form action="{URLSITE}membres/search.php" method="post" name="search" target="_self">
        <div >
            <img src="{URLSITEIMAGE}site/recherche.gif" alt="Recherche" class="rub_title" />
        </div>

                    <input name="str_search" type="text" id="str_search" size="20" maxlength="50" />
                    <input type="submit" name="Submit" value="Envoyer" />
                    <br />
                    Albums:
                    <input name="cb_album" type="checkbox" id="cb_album" value="cb_album" checked />
                    S&eacute;ries:
                    <input name="cb_serie" type="checkbox" id="cb_serie" value="cb_serie" checked />
                    Auteurs:
                    <input name="cb_aut" type="checkbox" id="cb_aut" value="cb_aut" checked />

    </form>
</div>