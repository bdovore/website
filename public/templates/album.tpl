<!DOCTYPEhtml PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">

<html>
<head>
    <title>{WNDTITLE}</title>
    <meta name="description" content="{DESCRIPTION}" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link href="{URLSITE}style/style1.css" rel="stylesheet" type="text/css" />
    <style>
    body {
    background-color: #EBDFD8;
    background-image: none;
    }
    </style>
    <script language="JavaScript">
        <!--
        function MM_swapImgRestore() { //v3.0
            var i,x,a=document.MM_sr;
            for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
        }

        function MM_preloadImages() { //v3.0
            var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
            var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
            if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
        }

        function MM_findObj(n, d) { //v4.01
            var p,i,x;
            if(!d) d=document;
            if((p=n.indexOf("?"))>0&&parent.frames.length) {
                d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
            }
            if(!(x=d[n])&&d.all) x=d.all[n];
            for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
            for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
            if(!x && d.getElementById) x=d.getElementById(n);
            return x;
        }

        function MM_swapImage() { //v3.0
            var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array;
            for(i=0;i<(a.length-2);i+=3)
            if ((x=MM_findObj(a[i]))!=null){
                document.MM_sr[j++]=x;
                if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];
            }
        }

        function changeEdition () {
            window.location="album.php?id_tome={TOMEID}&id_edition="+ document.getElementById('sel_edition').value;
        }

        function liencorrection () {
            parent.location.href="prop_correct.php?alb_id={TOMEID}&id_edition="+ document.getElementById('sel_edition').value;
            window.close();
        }

        function openSerie(){
            if (window.name == "Album") {
                window.opener.location="{URLSITE}serie.php?id_serie={SERID}";}
            else {
            parent.location="{URLSITE}serie.php?id_serie={SERID}";
            }
        }

        function addCollection(flg) {// flg = "O" : Achat futur ; = "N" : Achat;
            window.location="album.php?act=add&id_tome={TOMEID}&flg_achat="+flg+"&id_edition="+ document.getElementById('sel_edition').value;
        }
        //-->
    </script>

</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<a href="" onClick="parent.location.href='{URLSITE}simil?ID_TOME={TOMEID}'" title="albums similaires">[...]</a>
<div class="conteneuralb">
            <div class="alb-img">
                <img src="{URLIMAGE}couv/{COUVALBUM}" width="160" border="1" name="imgEdition" id="imgEdition" alt="couverture">
                <div class="petite_police">{COPYRIGHT}</div>
                <a href="{URLAMAZON}" target="_blank" title="Commander cet album sur Amazon">
                    <img src="{URLIMAGE}amazon.gif" alt="logo Amazon" border="0">
                </a>
                 <br />
                {COMMENT_EDITION}

            </div>

        <div class="contentalb" id="alb-description">

                                <h3>{ALBTITLE}  <br>  <a href="#" onclick="openSerie();">
                            {SERTITLE}
                        </a> </h3>
                        <p align="center">
                <font size="-1">{ALBNOTE} </font>
            </p>
                        {ALBGENRE}  -   {ALBSCENAR} /  {ALBDESSIN}
                    <br> <b>Edition </b> <font size="-1">
                            {OPTEDITION}
                        {COLLECTION}
                    <br>
                        <span class="petite_police">
                    ISBN-13 (EAN) : {EAN} || ISBN-10 : {ISBN} || ID-BDovore : {TOMEID} </span>

            <div class="action" id="action-menu">
                <div align="center">
                    <a href="#" onclick="addCollection('N');">
                        <font color="#900000" size="-1">
                            <b>Ajouter à ma collection</b>
                        </font>
                    </a> ||


                    <a href="#" onclick="addCollection('O')">
                        <font color="#90000" size="-1">
                            <b>Achat futur</b>
                        </font>
                    </a>
                </div>
                <div align="center">
                    <a href="#" onclick="javascript:liencorrection()">
                        <font color="#900000" size="-1">
                            <b>Proposer une correction</b>
                        </font>
                    </a> ||

                    <a href="comment.php?id_tome={TOMEID}&lib_tome={ALBTITLE}&aut=({NSCENAR},{NDESSIN})">
                        <font color="#90000" size="-1">
                            <b>Donner un avis de lecture</b>
                        </font>
                    </a>
                </div>
        <div class="contentalb">

            <br style="line-height: 30%;">

                        <!-- AddThis Button BEGIN -->
                        <script type="text/javascript">var addthis_pub="zeas";var addthis_brand="BDovore";var addthis_header_color="#990000";var addthis_header_background="#DAC1B3";</script><a href="http://www.addthis.com/bookmark.php?v=20" onmouseover="return addthis_open(this, '', '{URLALBUM_RSS}', '{TITLE}')" onmouseout="addthis_close()" onclick="return addthis_sendto()"><img src="{URLSITEIMAGE}site/lg-addthis-fr.gif" alt="Partager cette page" style="border:0" width="125" height="16"></a>
                        <noscript>
                            <span class="petite_police"><br />Javascript absent/désactivé dans votre navigateur : bouton inactif</span>
                        </noscript>
                        <script type="text/javascript" src="http://s7.addthis.com/js/200/addthis_widget.js"></script>
                        <!-- AddThis Button END -->

        </td>
        </tr>
        </tbody></table>
    </div>
    </div>
        </div>


    <div class="synopsis">
    <strong>Synopsis :</strong>

             <font size="-1">
                {ALBSTORY}
                </font>





        </div>
        <div class="comment">
        <font color="#990000" face="Arial, Helvetica, sans-serif" size="-1">
                <strong>Commentaires des membres :</strong>
            </font>
            <br>
            <font size="-1">
                    {COMMENT}
            </font>
        </div>
    </div>
</body>
</html>