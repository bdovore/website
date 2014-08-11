    <table width="100%" class="browser">
	<tr>
	    <td class="milieu" align="center" colspan="3">{TOTALROW}</td>
	</tr>
	<tr>
	    <td class="milieu" align="center" colspan="3">{URLPREVPAGE} {URLNEXTPAGE}</td>
	</tr>
          <!-- BEGIN DataBlock -->
        <tr>
            <td class="bord_gauche">&nbsp;</td>
            <td class="milieu">
<img src="{URLSITEIMAGE}site/spacer.gif" width={WSPACER} height={HSPACER} /><div id='onglet_div_plus_{LEVSIGN}' style='DISPLAY: block' onMouseOver='this.style.backgroundColor="#DDAB95"' onmouseout='this.style.backgroundColor=""' onClick='Block_None_Div("onglet_div_xhr_{LEVSIGN}");Block_None_Div("onglet_div_plus_{LEVSIGN}");Block_None_Div("onglet_div_moins_{LEVSIGN}");SubmitXhr("rrr=eee","onglet_div_xhr_{LEVSIGN}","{URLEVEL}#{LEVSIGN}",false)'><img src='{URLSITEIMAGE}site/aro_3_1.gif' border=0 title='Agrandir'>{NAMELEVEL} {URLEDIT}</div>
<div id='onglet_div_moins_{LEVSIGN}' style='DISPLAY: none' onMouseOver='this.style.backgroundColor="#DDAB95"' onmouseout='this.style.backgroundColor=""'  onClick='Block_None_Div("onglet_div_xhr_{LEVSIGN}");Block_None_Div("onglet_div_plus_{LEVSIGN}");Block_None_Div("onglet_div_moins_{LEVSIGN}")'><img src='{URLSITEIMAGE}site/aro_3_2.gif' border=0 title='Diminuer'>{NAMELEVEL} {URLEDIT}</div>
<div id='onglet_div_xhr_{LEVSIGN}' style='DISPLAY: none'></div>
            </td>
        </tr>
        <!-- END DataBlock -->
    </table>
