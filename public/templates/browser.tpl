{MENUGUEST}
<div class="cadre1">
	<div align="center">
	    <a href="{ACTBROWSER}rb_browse=ser">Par Série</a> &nbsp;-&nbsp;
	    <a href="{ACTBROWSER}rb_browse=aut">Par Auteur</a> &nbsp;-&nbsp;
	    <a href="{ACTBROWSER}rb_browse=genr">Par Genre</a> &nbsp;-&nbsp;
	    <a href="{ACTBROWSER}rb_browse=edit">Par Editeur</a>
	    <br />
	</div>
	<table width="100%" border="0">
	    <tr>
	        <td> 
	            <div align="center">
	                <!-- BEGIN NavBlock -->
	                <a href="{URLLETTER}">{LETTER}&nbsp;</a>
	                <!-- END NavBlock -->         
	            </div>
	        </td>
	    </tr>
	    <tr>
	        <td>
	            <form name="form1" method="get" action="">
	                <div align="center">
	                    Ou commence par : 
	                    <input name="let" type="text" id="let" />
	                    <input type=hidden name="rb_browse" value="{TYPBROWSE}" /> 
	                    <input type=hidden name="user" value="{USERID}" />
	                </div>
	            </form>
	        </td>
	    </tr>
	</table>
	</div>
	
	<div class="cadre1">
	<!-- BEGIN DataBlock -->
	<div class="cadre1">
	        <p id="{LEVSIGN}">
	            <img src="{URLSITEIMAGE}site/spacer.gif" width={WSPACER} height={HSPACER} />
	            <img src="{URLSITEIMAGE}site/{IMGNAVIG}" />
	            <a href="{URLEVEL}#{LEVSIGN}" {ACTLEVEL}>{NAMELEVEL}</a> {URLEDIT}
	        </p>
	    </div>
	<!-- END DataBlock -->
	<div class="cadre1">
	        <p align="center">
	            <a href="{URLPREVPAGE}">Pr&eacute;c&eacute;dent</a> -
	             <a href="{URLNEXTPAGE}">Suivant</a> 
	        </p>
	</div>
</div>
