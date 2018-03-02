/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var listAlbum = [];
var listId = [];
var nbcall = 0;
var require_call = 5;
var nbmaxalbum = 0;
var nbcircle = 2;
function computeDiscovery(id_tome) {
    nbmaxalbum = f(2);
    listId.push( id_tome+"");
    getListSimil(id_tome);

}

function addCircle () {
    // ajoute un cercle et ajoute les albums en plus dans les places libres
    // on commence par agrandir le tableau
    // on va ajouter déjà une colonne à droite et une à gauche, puis une ligne en haut et une ligne en bas
    $("#disco_couv tr").prepend("<td></td>");
    $("#disco_couv tr").append("<td></td>");
    var row = "<td></td>";
    nbcircle++;
    for (var i = 1;i< 2*nbcircle+1;i++) {
        row +=  row;
    }
    $("#disco_couv").prepend("<tr>"+row+"</tr>");
    $("#disco_couv").append("<tr>"+row+"</tr>");
    tmpList = [];
    if (listAlbum.length > nbmaxalbum) { // on a chargé plus d'album que nécessaire la fois précédente
        var nbprevious = listAlbum.length;
        
        for (var i=0;i < nbprevious - nbmaxalbum +1;i++){
            tmpList.push(listAlbum.pop());
            listId.pop(); // on enlève également de listId
        }
       nbmaxalbum = f(nbcircle);
       require_call += (nbmaxalbum - tmpList.length - listAlbum.length)%5;
     
    } else {
        
       
        require_call = 2*nbcircle + 1;
        nbmaxalbum = f(nbcircle);
    }
    // on complète la liste avec des albums existants pour lesquels on ira chercher des similaires non calculés
    // on part des 5 derniers ajouts pour lesquels il faudra aller chercher des albums similaires si besoin
     for (var i=1;i < listAlbum.length - nbcall;i++){
            tmpList.push(listAlbum[listAlbum.length-i]);
      }
    // nbcall = 0;
     pushAlbum(tmpList);
}
function pushAlbum(data) {
    // on ajuste le nb d'itération
    var add_iteration = false;
    var nbnew = 0;
    for (var i=0;i < data.length;i++) {
        if (listId.indexOf(data[i].ID_TOME) > 0 ) {
            add_iteration = true;
        } else {
            nbnew++;
        } 
        
    }
    if (add_iteration && (listAlbum.length + nbnew) < nbmaxalbum) require_call ++; // il manque un ou plusieurs albums pour compléter : on fera un tour de plus
   
   // ajout des albums
    for (var i=0;i< data.length;i++) {
        if (listId.indexOf(data[i].ID_TOME) < 0 ) {
            // l'album n'est pas dans la liste : on l'ajoute
            p = Pos(listAlbum.push(data[i]));
            listId.push(data[i].ID_TOME);
            if (listAlbum.length < nbmaxalbum) {
                $("#disco_couv tr:eq("+(nbcircle + p[0])+") td:eq("+ (nbcircle + p[1])+")").append("<a href='"+ $.bdovore.URL+ "/Album?id_tome="+data[i].ID_TOME+ ($.browser.mobile ? "&mobile=T" : "") + "' title= '" + addslashes(data[i].TITRE_TOME) + "' class='fancybox fancybox.iframe {width:600,height:600}'>" + "<img src='https://www.bdovore.com/images/couv/"+(data[i].IMG_COUV ? data[i].IMG_COUV : "default.png")+"' class='couvBig'> </a>");
            } 
            if (nbcall < require_call && listAlbum.length < nbmaxalbum) {
                
              
                 nbcall++;
                getListSimil(data[i].ID_TOME);
            }      
         } 
    }
   // console.log(listAlbum);
}
function getListSimil(id_tome) {
     var url_simil = "./simil/getTopSimil?ID_TOME="+id_tome;
      $.getJSON(url_simil, function (data) {
            if (data.length > 0) {
                pushAlbum( data);
               
            }});
        
 }
 
// retourne les carrés des nombres impairs
function f( k)
{ return (2*k+1)*(2*k+1);}

// détermine sur quel cercle on se trouve
function Trouve_k(n)
{ 
   var i;
   for( i=0; f(i)<=n;i++);
   return i;
}

// détermine la position sur le cercle
// à partir du coin inférieur gauche
function Trouve_h(n)
{  
    var k= Trouve_k(n);
    return ((8*k+(n-f(k))+1))%(8*k);
}


// calcule le décalage par rapport à ce même coin
function Offset ( k, h,O)
{ 
    
    q=Math.floor(h/(2*k));
    r=h-q*2*k;
  switch (q)
  { case 0: O[0]+=0;O[1]+=r; break;
    case 1: O[1]+=2*k;O[0]+=r; break;
    case 2: O[0]+=2*k;O[1]+=2*k-r;break;
    default: O[1]+=0;O[0]+=2*k-r;break;
  }
  return O;
}

// met dans P les coordonnées du point d'indice n de la spirale (n>1)
function Pos ( n)
{ 
  var k=Trouve_k(n);
  var h=Trouve_h(n);
  var P = [];
  P[0]=-k;
  P[1]=-k;
 return Offset(k,h,P);
}

function addslashes(ch) { 
   // ch = ch.replace(/\\/g,"\\\\"); 
    ch = ch.replace(/\'/g," "); 
    //ch = ch.replace(/\"/g,"\\\""); 
    return ch; 
}