// créer/modifier la présentation d'un champ

function ModifyChamp(column_name)
{
    var largeur = 700;
    var hauteur = 500;
    posX = (screen.width - largeur) / 2;
    posY = (screen.height - hauteur) / 2;
    liste = 'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,scrollbars=yes,screenX=' + posX + ',screenY=' + posY + ',width=' + largeur + ',height=' + hauteur;
    var url = BDO_URL + 'admin/modify_champ.php?COLUMN_NAME=' + column_name;
    fenjsscp = window.open(url,'fenjsscp', liste);
    fenjsscp.moveTo(posX,posY);
}

// créer/modifier la valeur d'une constante

function ModifyConstante(name_constant,id_lang)
{
    var largeur = 680;
    var hauteur = 280;
    posX = (screen.width - largeur) / 2;
    posY = (screen.height - hauteur) / 2;
    liste = 'toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable=yes,scrollbars=yes,screenX=' + posX + ',screenY=' + posY + ',width=' + largeur + ',height=' + hauteur;
    var url = BDO_URL + 'admin/modify_constante.php?NAME_CONSTANT=' + name_constant + '&ID_LANG=' + id_lang;
    fenjsrsl = window.open(url,'fenjsrsl', liste);
    fenjsrsl.moveTo(posX,posY);
}