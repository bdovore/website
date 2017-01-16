<?php

class Bdo_Onglet
{

    var $nom;

    var $id = 1;

    var $text;

    var $width = '100%';

    var $height = '';

    var $style = "cfgonglet";

    var $pack_div;

    var $arbo;

    var $tabElement = array();

    var $display = "none";

    var $display_plus = "block";

    var $display_moins = "none";

    var $css_onglet = "";

    var $css_onglet_on = "";

    var $css_onglet_out = "";

    var $abscisse = 2;

    var $contenu;

    function __construct ($nom, $text, $style = "cfgonglet")
    {
        if (! isset($_SESSION['id_onglet_div'])) {
            $_SESSION['id_onglet_div'] = $this->id;
        }
        else {
            $_SESSION['id_onglet_div'] ++;
            $this->id = $_SESSION['id_onglet_div'];
        }

        $this->nom = $nom;
        $this->text = $text;
        $this->style = $style;
        $this->arbo = $this->id;
        $this->css_onglet = $this->style . '_line_trbl';
        $this->css_onglet_on = $this->style . '_line_trbl_on';
        $this->css_onglet_out = $this->style . '_line_trbl_out';
    }

    function setAbscisse ($abscisse)
    {
        $this->abscisse = $abscisse;
    }

    function setWidth ($val)
    {
        $valx = $val + 0;
        if ($val === $valx) {
            $this->width = $val . 'px';
        }
        else {
            $this->width = $val;
        }
    }

    function setText ($val)
    {
        $this->text = $val;
    }

    function changeDisplay ($display = '')
    {
        if ($display != '') {
            $this->display = $display;
        }
        else {
            if ($this->display == 'none') {
                $this->display = 'block';
            }
            else {
                $this->display = 'block';
            }
        }
        if ($this->display == 'none') {
            $this->display_plus = 'block';
            $this->display_moins = 'none';

            $this->css_onglet = $this->style . '_line_trbl';
            $this->css_onglet_on = $this->style . '_line_trbl_on';
            $this->css_onglet_out = $this->style . '_line_trbl_out';
        }
        else {
            $this->display_plus = 'none';
            $this->display_moins = 'block';

            $this->css_onglet = $this->style . '_line_trl';
            $this->css_onglet_on = $this->style . '_line_trl_on';
            $this->css_onglet_out = $this->style . '_line_trl_out';
        }
    }

    function AddElement ($element)
    {
        // if (is_a($element,'Bdo_Onglet_Pack')) php5.3.9
        if ($element instanceof Bdo_Onglet_Pack) {
            foreach ($element->tab_onglet as $onglet) {
                $onglet->arbo .= ';' . $this->arbo;
            }
        }

        $this->tabElement[] = $element;
    }

    function affOnglet ()
    {
        $html = '';
        if (0 < count($this->tabElement)) {
            foreach ($this->tabElement as $contenu) {

                // if (is_a($contenu,'Bdo_Onglet_Pack')) //php5.3.9
                if ($contenu instanceof Bdo_Onglet_Pack) {
                    $html .= $contenu->affPack();
                }
                else {
                    $html .= $contenu;
                }
            }
        }
        else {
            $html .= $this->id . '-' . $this->text;
        }
        return $html;
    }

    function vueOnglet ()
    {
        $html = "
        <div class='" . $this->style . "' style='width:" . $this->width . "'>
            <div id='onglet_div_" . $this->id . "'
            style='DISPLAY: block; cursor:pointer'
            onClick='Block_None_Div(\"div_aff_" . $this->id . "\");
            Block_None_Div(\"onglet_div_plus_" . $this->id . "\");
            Block_None_Div(\"onglet_div_moins_" . $this->id . "\")'
            class='" . $this->style . "0'
            onmouseover='this.className=\"" . $this->style . "2\"'
            onmouseout='this.className=\"" . $this->style . "0\"'>
                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                <tr>
                <td id='" . $this->style . "_td_" . $this->id . "' width=100% nowrap align=center>" . $this->text . "</td>
                <td id='" . $this->style . "_td_" . $this->id . "' align=center nowrap>
                <div id='onglet_div_plus_" . $this->id . "' style='DISPLAY: " . $this->display_plus . "'><img src='" . CFG_URL_ROOT . "image/b_agrandir.gif' width='20' border=0 title='Agrandir'></div>
                <div id='onglet_div_moins_" . $this->id . "' style='DISPLAY: " . $this->display_moins . "'><img src='" . CFG_URL_ROOT . "image/b_diminuer.gif' width='20' border=0 title='Diminuer'></div>
                </td>
                </tr>
                </table>
            </div>

            <div id='div_aff_" . $this->id . "' style='DISPLAY:" . $this->display . ";" . ($this->height ? ("height:" . $this->height . ";overflow:auto") : "") . " '>
            <input type=hidden name='vue_" . $this->id . "' id='id_vue_" . $this->id . "' value='vue_" . $this->id . "'>
            ";

        $html .= $this->affOnglet();

        // &nbsp; pour bug IE7
        $html .= ($this->height ? "&nbsp;" : "") . "</div>
        </div>
        ";
        return $html;
    }
}
