<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<link href="{URLSITE}style/addition.css" rel="stylesheet" type="text/css">
<title>{DOCTITRE}</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<SCRIPT LANGUAGE="JavaScript"><!--
function changeSource(myForm, myElement, myTitle, withID) {
    window.opener.document.forms[myForm].elements[myElement].value = myTitle;
	window.opener.document.forms[myForm].elements[myElement+'Id'].value = withID;
	window.close(self);
}

//--></SCRIPT>

</head>

<body>
<table width="100%" class = "addtable">
  <tr>
    <td class="addTitle">Serie</td>
    <td class="addTitle">Tome</td>
    <td class="addTitle">Titre</td>
  </tr>
<!-- BEGIN ResultBlock -->
  <tr>
    <td class="addDataSmall">{SERIE}</td>
    <td class="addDataSmall">{NUMTOME}</td>
    <td class="addDataSmall"><a href="{RETURNURL}">{TITRE}</a></td>
  </tr>
<!-- END ResultBlock -->
</table>

</body>
</html>
