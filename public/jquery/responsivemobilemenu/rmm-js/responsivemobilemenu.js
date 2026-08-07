/*

Responsive Mobile Menu v1.0
Plugin URI: responsivemobilemenu.com

Author: Sergio Vitov
Author URI: http://xmacros.com

License: CC BY 3.0 http://creativecommons.org/licenses/by/3.0/

*/

function responsiveMobileMenu() {	
		$('.rmm').each(function() {
			
			
			
			$(this).children('ul').addClass('rmm-main-list');	// mark main menu list
			
			
			var $style = $(this).attr('data-menu-style');	// get menu style
				if ( typeof $style == 'undefined' ||  $style == false )
					{
						$(this).addClass('graphite'); // set graphite style if style is not defined
					}
				else {
						$(this).addClass($style);
					}
					
					
			/* 	width of menu list (non-toggled) */
			
			var $width = 0;
				$(this).children('.rmm-main-list').children('li').each(function() {
					$width += $(this).children('a').outerWidth(true);
				});

			// La largeur calculee sert uniquement de seuil de repli. Le conteneur
			// reste libre de prendre toute la place disponible sur grand ecran.
			$(this).data('menu-width', $width);
		
	 	});
}
function getMobileMenu() {

	/* 	build toggled dropdown menu list */
	
	$('.rmm').each(function() {	
				var menutitle = $(this).attr("data-menu-title");
				if ( menutitle == "" ) {
					menutitle = "Menu";
				}
				else if ( menutitle == undefined ) {
					menutitle = "Menu";
				}
				var $menulist = $(this).children('.rmm-main-list').html();
				var $menucontrols ="<div class='rmm-toggled-controls'><div class='rmm-toggled-title'>" + menutitle + "</div><div class='rmm-button'><span>&nbsp;</span><span>&nbsp;</span><span>&nbsp;</span></div></div>";
				$(this).prepend("<div class='rmm-toggled rmm-closed'>"+$menucontrols+"<ul>"+$menulist+"</ul></div>");

		});
}

function adaptMenu() {
	
	/* 	toggle menu on resize */
	
	$('.rmm').each(function() {
			var $width = $(this).data('menu-width');
			// La largeur utile peut etre reduite par des elements voisins (compte,
			// logo...). On se base donc sur la place reelle du menu plutot que sur
			// la largeur totale de son parent.
			var availableWidth = this.getBoundingClientRect().width;
			var parent = this.parentNode;
			var parentStyle = window.getComputedStyle(parent);
			if ( parentStyle.display.indexOf('flex') !== -1 ) {
				availableWidth = parent.getBoundingClientRect().width;
				var visibleSiblings = 0;
				$(this).siblings().each(function() {
					if ( window.getComputedStyle(this).display !== 'none' ) {
						availableWidth -= this.getBoundingClientRect().width;
						visibleSiblings++;
					}
				});
				var gap = parseFloat(parentStyle.columnGap) || 0;
				availableWidth -= gap * visibleSiblings;
			}
			if ( availableWidth + 0.5 < $width ) {
				$(this).children('.rmm-main-list').hide(0);
				$(this).children('.rmm-toggled').show(0);
			}
			else {
				$(this).children('.rmm-main-list').show(0);
				$(this).children('.rmm-toggled').hide(0);
			}
		});
		
}

$(function() {

	 responsiveMobileMenu();
	 getMobileMenu();
	 adaptMenu();
	 // Une seconde passe prend en compte la largeur finale de la barre flex.
	 setTimeout(adaptMenu, 0);
	 
	 /* slide down mobile menu on click */
	 
	 $('.rmm-toggled, .rmm-toggled .rmm-button').click(function(){
	 	if ( $(this).is(".rmm-closed")) {
		 	 $(this).find('ul').stop().show(300);
		 	 $(this).removeClass("rmm-closed");
	 	}
	 	else {
		 	$(this).find('ul').stop().hide(300);
		 	 $(this).addClass("rmm-closed");
	 	}
		
	});	

});
	/* 	hide mobile menu on resize */
var rmmResizeTimer;
$(window).resize(function() {
	// Laisse le navigateur recalculer la largeur des elements flexibles avant
	// de choisir entre le menu complet et sa version repliee.
	clearTimeout(rmmResizeTimer);
	rmmResizeTimer = setTimeout(adaptMenu, 0);
});
