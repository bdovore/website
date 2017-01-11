{MENUCOLL}

<div class="cadre1">

<p><a name="haut"></a>Stats par : <a href="#genre">Genres</a> - <a href="#editeur">Editeurs</a>
  -<a href="#dessin"> Dessinateurs</a> - <a href="#scenar">Sc&eacute;naristes</a></p>
<table width="100%" class="donnees">
  <tr>
    <td class="entete"><div align="center">
        <p><strong><a name="genre"></a>R&eacute;partition par Genres</strong></p>
      </div></td>
  </tr>
  <tr>
    <td class="data" bgcolor="#FFFFCC"><div align="center">
        <p align="left"><img src="{URLSITE}graphs/rep_genres.php?user={IDUSER}"></p>
      </div>
      <div align="center"><a href="#haut">Haut de page</a></div></td>
  </tr>
  <tr>
    <td class="entete"><div align="center">
        <p><strong><a name="editeur"></a>R&eacute;partition par Editeurs</strong></p>
      </div></td>
  </tr>
  <tr>
    <td class="data" bgcolor="#FFFFCC"><div align="center">
        <p align="left"><img src="{URLSITE}graphs/rep_editeurs.php?user={IDUSER}"></p>
      </div>
      <div align="center"><a href="#haut">Haut de page</a></div></td>
  </tr>
  <tr>
    <td class="entete"><div align="center">
        <p><strong>R&eacute;partition par Auteur</strong></p>
      </div></td>
  </tr>
    <tr>
    <td class="data" bgcolor="#FFFFCC"><div align="center">


<table width="100%" class="donnees">
  <tr>
    <td align=center valign=top> <a name="dessin"></a>
        <table width="80%" class="donnees">
          <tr>
            <td width="62%" class="entete">Dessinateur</td>
            <td width="38%" class="entete">Nb d'albums</td>
          </tr>
          <!-- BEGIN DessinBlock -->
          <tr>
            <td class="data" bgcolor="#FFFFCC">{DESSIN}</td>
            <td class="data" bgcolor="#FFFFCC">{NBBYDESSIN}</td>
          </tr>
          <!-- END DessinBlock -->
        </table>
    </td>
    <td align=center valign=top> <a name="scenar"></a>
        </div>
        <table width="80%" class="donnees">
          <tr>
            <td width="62%" class="entete">Sc&eacute;nariste</td>
            <td width="38%" class="entete">Nb d'albums</td>
          </tr>
          <!-- BEGIN ScenarBlock -->
          <tr>
            <td class="data" bgcolor="#FFFFCC">{SCENAR}</td>
            <td class="data" bgcolor="#FFFFCC">{NBBYSCENAR}</td>
          </tr>
          <!-- END ScenarBlock -->
        </table>
      </td>
        </tr>
           <tr>
    <td colspan=2 align=center>
    <a href="#haut">Haut de page</a>
      </td>
  </tr>
</table>

</td>
  </tr></table>
        </div>