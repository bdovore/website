<!-- BEGIN AlbBlock -->
	<div class = "album {ALBCLASS}" id="{IDTOME}">
		<div class="main">
			<img src="{COUVALBUM}" class="couverture">
			<button type="button" class="clickable" id="addrem-{IDTOME}-{IDEDITION}" {ISDISABLED}>{BUTTONTEXT}</button>
			<button type="button" class="clickable" id="exclude-{IDTOME}-{IDEDITION}" {ISDISABLEDREM}>Exclude</button>
			<button type="button" class="clickable" id="af-{IDTOME}-{IDEDITION}" {ISDISABLED}>Achat Futur</button>
			<br />
			<br />{ALBTITLE}<br />
		</div>
		<div class="additional">
		ici, on peut placer des infos supplémentaires (auteur, dessinateur, collection, editeur, date de parution etc...)
		</div>
	</div>
<!-- END AlbBlock -->