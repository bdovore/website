<!--// Michel Deboom septembre 2004 menu largeur auto,flèches txt -->
var delai,menu,dul,db,class_menu;
pause=1000;
D=document;
wul1=0;
ie=D.all?1:0;
op=window.opera?1:0;
dtd=D.compatMode=="CSS1Compat"?1:0;

// paramétrage :
// flèches pour niveau horizontal, vertical ou vertical à droite
flh='';
flg='&#8250;';
fld='&#8249;';
flc=ie?4:8 ;// espace pour la flèche
//correction bug padding+border ie et opéra non dtd
da=15; // des liens
dul=(ie&!dtd)?4:0; // des ul
danc=(ie&!dtd)?10:0;// hauteur du div "ancre_menu"

function initMenu(id_menu,class_menu)
{
	wul1=0;
	var name_menu = id_menu;
	var nom_ancre = "ancre_menu"+id_menu;

	de=ie&!op&&dtd?D.documentElement:D.body //exception IE6 dtd
	fx=ie&!op?de.clientWidth:innerWidth-20  //l fenêtre

	menu=D.getElementById('menu');
	menu.style.display='block';
	menu.innerHTML=D.getElementById(id_menu).innerHTML;
	id_menu = 'menu';

	wul=[] // tableau des largeurs de sous-menus
	as=menu.getElementsByTagName('a');
	sousMenu=menu.getElementsByTagName('ul');
	elem=document.getElementsByTagName('select');

	// ajoute a href="#" dans les li sans lien .
	lis=menu.getElementsByTagName('li');
	for(i=0;i<lis.length;i++)
	{
		var L=lis[i];
		if(L.firstChild.tagName!="A")
		{
			titre=L.firstChild.data;
			L.removeChild(L.firstChild);
			L.innerHTML='<a href="#">'+titre+'<\/a>'+L.innerHTML;
			L.className="titremenu" + name_menu;
		}
		else L.className="lienmenu" + name_menu; // avec url
	}
	//ajoute un id aux sous-menus
	for(j=0;j<sousMenu.length;j++)
	{
		sousMenu[j].id="ul"+j;
		wul[j]=0;
	}
	//construction du menu :
	for(i=0;i<as.length;i++)
	{
		lien=as[i];
		wa=lien.offsetWidth;
		smenu=lien.parentNode.getElementsByTagName('ul')[0]?1:0;
		if (smenu) wa+=flc;
		// 1er niveau horizontal
		if(lien.parentNode.parentNode.id==id_menu)
		{
			if(class_menu=="mh")
			{
				if(smenu&&lien.innerHTML!="") lien.innerHTML+=flh;
				wa=lien.offsetWidth;
				wul1+=wa+da;
			}
			else { // vertical
				if(wa>wul1){wul1=wa+da;};
				// ajoute les flèches
				if(smenu){
					lien.innerHTML=(class_menu=="vd")?'<span class="fl">'+fld+'</span>'+
					lien.innerHTML:lien.innerHTML+'<span class="fl">'+flg+'</span>&nbsp;';}
			}
		}
		//autres niveaux verticaux
		else
		{
			n=eval(lien.parentNode.parentNode.id.substr(2,2));
			if(wa>wul[n]){wul[n]=wa;};
			// ajoute les flèches
			if(smenu)lien.innerHTML=(class_menu=="vd")?'<span class="fl">'+fld+'</span>'+
			lien.innerHTML:lien.innerHTML+'<span class="fl">'+flg+'</span>&nbsp;';
		}
		//ajoute les évènements.
		lien.onmouseover=lien.onfocus=function()
		{
			ul_parent=this.parentNode.parentNode;
			ul=this.parentNode.getElementsByTagName('ul')[0]
			ef(ul_parent); // cache tout aprés l'ul parent
			if(ul)
			{
				ul.style.visibility="visible";// montre l'ul enfant
				(class_menu=="vd")?ul.style.right="100%":
				(class_menu=="mh"&&ul_parent.id!=id_menu)||(class_menu=="vg")?ul.style.left="100%":0;
				this.className="suivi"; // marquage du suivi de lien
				//voirSelect('hidden');
			}
		};
		lien.onmouseout=function(){delai=setTimeout('eftout()',pause)};
	}

	//fixe la largeur du 1er menu :
	if(wul1) menu.style.width=wul1+dul+"px";

	//largeur des sous-menu verticaux et de leurs liens :
	for(var x=0;x<wul.length;x++)
	{
		ula=sousMenu[x].getElementsByTagName('a');
		dda=(ie&!dtd)?da:0;
		for(var i=0;i<ula.length;i++)
		{
			ula[i].style.width=wul[x]+dda+"px";
		}
		sousMenu[x].style.width=wul[x]+da+dul+"px";
	}
	lien.onblur=D.onclick=eftout // pour navigation clavier IE
	menu.className=class_menu; //activation retardée de la feuille de style

	// replace les flèches des menus verticaux au bons endroits.
	span = menu.getElementsByTagName('span');
	for(i=0;i<span.length;i++)
	{
		A=span[i].parentNode;c=span[i].style;
		if(span[i].className=="fl")
		{
			c.top=A.offsetTop+2+"px";
			c.left=(class_menu=="vd")?4+"px":A.offsetWidth-8+"px";
		}
	}
	ancremenu(nom_ancre);
}

function ancremenu(nom_ancre)
{
	ancre=D.getElementById(nom_ancre);
	if(ancre)
	{
		ancre.appendChild(menu);
		with(ancre.style)
		{
			//			height=menu.offsetHeight+danc+'px';
			// 			width=menu.offsetWidth+dul+'px';
		}
	}
}

function ef(ul)
{
	//cache les uls qui suivent cet ul.
	clearTimeout(delai);
	var li=ul.getElementsByTagName('li');
	for(i=0;i<li.length;i++)
	{
		var ul=li[i].getElementsByTagName('ul')[0];
		if(ul)
		{
			ul.style.visibility="hidden";
			li[i].firstChild.className=""; //rétabli le marquage initial
		}
	}
}

function eftout()
{
	ef(menu);
	//voirSelect('visible');
	menu.style.visibility="hidden";
	menu.style.visibility="visible";// bug ie
}


