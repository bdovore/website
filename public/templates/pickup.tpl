<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>

<title>{DOCTITRE}</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<script type="text/javascript"><!--
function changeSource(myForm, myElement, myTitle, withID) {
    window.opener.document.forms[myForm].elements[myElement].value = myTitle;
	window.opener.document.forms[myForm].elements[myElement+'Id'].value = withID;
	window.close(self);
}
//--></script>

</head>

<body style="background-color: #CF5C5C;">
{QUERY}
<br />
{VAR}
<!-- BEGIN ResultBlock -->
{ID} <a href="{RETURNURL}" style="color: #F7E4D9;">{FULLNAME}</a><br />
<!-- END ResultBlock -->

</body>
</html>
