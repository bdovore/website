<script language="JavaScript">
function saveNews() {
    if (document.forms["nwl_form"].stat.value == "send") {
        alert("Vous ne pouvez pas modifier une newsletter déjà envoyée");

    }
    else {
        document.forms["nwl_form"].action = "admin.nwl.list.php";
        //alert(document.forms["nwl_form"].action);
        document.forms["nwl_form"].submit();
    }
}

function sendNews() {
    if (document.forms["nwl_form"].stat.value == "send") {
        alert("La newsletter a déjà été envoyée !");

    }
    else {
        if (confirm("Envoyer la newsletter à tous les abonnés ?")) {
            document.forms["nwl_form"].action = "nwlsend.php";
            document.forms["nwl_form"].submit();

        }
    }

}
</script>
<form name="nwl_form" method="post" action="">
<input name="nwl_id" type="hidden" value="{NWLID}">
<input name="action" type="hidden" value="{ACT}">
<input name="stat" type="hidden" value="{STATUS}">
  <div align="center"> </div>
  <div align="center"><br>
    <table width="80%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td> <div align="center"> Texte :<br>
            <textarea name="content" cols="50" rows="20">{NWLCONTENT}</textarea>
          </div></td>
      </tr>
      <tr>
        <td><div align="center">Avancement :
            <select name="nwl_status" id="nwl_status">
              <option value="0">En cours</option>
              <option value="1">Termin&eacute;</option>
              <option value="2">Envoy&eacute;</option>
            </select>
          </div></td>
      </tr>
      <tr>
        <td><div align="center"> Commentaire : <br>
            <textarea name="comment" cols="50" rows="5">{NWLCMT}</textarea>
          </div></td>
      </tr>
      <tr>
        <td><div align="center">
            <p> <a  href="#" onClick="saveNews()">Enregistrer&nbsp;</a> <a href="#" onclick="window.open('nwl_preview.htm','Preview','width=500,height=400,scrollbars=1');">Preview</a>
            </p>
            <p>
              <input type="button" name="Submit3" value="Envoyer" onclick="sendNews()">
            </p>
          </div></td>
      </tr>
    </table>
  </div>
</form>

