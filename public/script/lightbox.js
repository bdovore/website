/*
LightboxIframe JS: Fullsize Image OverlayIframes
by Lokesh Dhakar - http://www.huddletogether.com

For more information on this script, visit:
http://huddletogether.com/projects/lightboxIframe/

Licensed under the Creative Commons Attribution 2.5 License - http://creativecommons.org/licenses/by/2.5/
(basically, do anything you want, just leave my name and link)

Table of Contents
-----------------
Configuration

Functions
- getPageScroll()
- getPageSize()
- pause()
- showLightbox()
- hideLightbox()
- initLightbox()
- addLoadEvent()

Function Calls
- addLoadEvent(initLightboxIframe)

*/

//
// Configuration
//

// If you would like to use a custom loading image or close button reference them in the next two lines.
var closeButton = 'image/close.gif';

//
// getPageScroll()
// Returns array with x,y page scroll values.
// Core code from - quirksmode.org
//
function getPageScroll(){

	var xScroll;
	var yScroll;

	if (self.pageYOffset) {
		xScroll = self.pageXOffset;
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		xScroll = document.documentElement.scrollLeft;
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		xScroll = document.body.scrollLeft;
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array(xScroll,yScroll)
	return arrayPageScroll;
}



//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
function getPageSize(){

	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY) {
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else {
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}

	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight);
	return arrayPageSize;
}


//
// pause(numberMillis)
// Pauses code execution for specified time. Uses busy code, not good.
// Code from http://www.faqts.com/knowledge_base/view.phtml/aid/1602
//
function pauseIE(numberMillis) {
	var now = new Date();
	var exitTime = now.getTime() + numberMillis;
	while (true) {
		now = new Date();
		if (now.getTime() > exitTime)
		return;
	}
}


//
// showLightboxIframe()
// Preloads images. Pleaces new image in lightboxIframe then centers and displays.
//
function showLightbox(boxType,url,widthDiv,heightDiv)
{
	if (!document.getElementById('overlay'))
	{
		initLightbox();
	}

	// prep objects
	if (boxType == 'div')
	{
		var objoverlay = document.getElementById('overlay');
		var objLightbox = document.getElementById('lightbox');
	}
	else
	{
		var objoverlay = document.getElementById('overlayIframe');
		var objLightbox = document.getElementById('lightboxIframe');
	}

	var arrayPageSize = getPageSize();
	var arrayPageScroll = getPageScroll();

	//	alert('pageWidth=[' +arrayPageSize[0] + ']\npageHeight=[' + arrayPageSize[1] + ']\nwindowWidth=[' + arrayPageSize[2] + ']\nwindowHeight=[' + arrayPageSize[3] + ']\nxScroll=[' + arrayPageScroll[0] + ']\nyScroll=[' + arrayPageScroll[1] + ']');

	// set height of overlay to take up whole page and show
	objoverlay.style.width = (arrayPageSize[0] + 'px');
	objoverlay.style.height = (arrayPageSize[1] + 'px');
	objoverlay.style.display = 'block';


	// center Lightbox and make sure that the top and left values are not negative
	// and the image placed outside the viewport
	var LightboxTop = arrayPageScroll[1] + ((arrayPageSize[3] - 35 - heightDiv) / 2);
	var LightboxLeft = arrayPageScroll[0] + ((arrayPageSize[2] - 20 - widthDiv) / 2);

	objLightbox.style.width = widthDiv + "px";
	objLightbox.style.height = heightDiv + "px";


	if (boxType == 'div')
	{
		var objLightboxDetails = document.getElementById('lightboxDetails');
	}
	else
	{
		var objLightboxDetails = document.getElementById('lightboxDetailsIframe');
		objLightboxDetails.setAttribute('src',url);
	}

	objLightboxDetails.style.width = widthDiv + "px";
	objLightboxDetails.style.height = heightDiv + "px";

	objLightbox.style.top = (LightboxTop < 0) ? "0px" : LightboxTop + "px";
	objLightbox.style.left = (LightboxLeft < 0) ? "0px" : LightboxLeft + "px";


	// A small pause between the image loading and displaying is required with IE,
	// this prevents the previous image displaying for a short burst causing flicker.
	if (navigator.appVersion.indexOf("MSIE")!=-1){
		pauseIE(250);
	}

	// Hide select boxes as they will 'peek' through the image in IE
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "hidden";
	}

	objLightbox.style.display = 'block';

	// After image is loaded, update the overlay height as the new image might have
	// increased the overall page height.
	arrayPageSize = getPageSize();
	objoverlay.style.height = (arrayPageSize[1] + 'px');

}


//
// hideLightbox()
//
function hideLightbox(stmt)
{
	//----------------------div
	// get objects
	objOverlay = document.getElementById('overlay');
	objLightbox = document.getElementById('lightbox');

	// hide lightbox and overlay
	objOverlay.style.display = 'none';
	objLightbox.style.display = 'none';

	//----------------------iframe
	// get objects
	objOverlayIframe = document.getElementById('overlayIframe');
	objLightboxIframe = document.getElementById('lightboxIframe');

	if (stmt != 'self')
	{
		var objLightboxDetails = document.getElementById('lightboxDetailsIframe');
		objLightboxDetails.setAttribute('src','design/noForward');
	}

	// hide lightboxIframe and overlayIframe
	objOverlayIframe.style.display = 'none';
	objLightboxIframe.style.display = 'none';

	// make select boxes visible
	selects = document.getElementsByTagName("select");
	for (i = 0; i != selects.length; i++) {
		selects[i].style.visibility = "visible";
	}
}


//
// initLightboxIframe()
// Function runs on window load, going through link tags looking for rel="lightboxIframe".
// These links receive onclick events that enable the lightboxIframe display for their targets.
// The function also inserts html markup at the top of the page which will be used as a
// container for the overlayIframe pattern and the inline image.
//
function initLightbox()
{

	if (!document.getElementsByTagName){ return; }

	var objBody = document.getElementsByTagName("body").item(0);

	// create overlay div and hardcode some functional styles (aesthetic styles are in CSS file)
	var objOverlay = document.createElement("div");
	objOverlay.setAttribute('id','overlay');
	objOverlay.onclick = function () {hideLightbox(''); return false;}
	objOverlay.style.display = 'none';
	objOverlay.style.position = 'absolute';
	objOverlay.style.top = '0';
	objOverlay.style.left = '0';
	objOverlay.style.zIndex = '90';
	objOverlay.style.width = '100%';
	objBody.insertBefore(objOverlay, objBody.firstChild);

	// create lightbox div, same note about styles as above
	var objLightbox = document.createElement("div");
	objLightbox.setAttribute('id','lightbox');
	objLightbox.style.display = 'none';
	objLightbox.style.position = 'absolute';
	objLightbox.style.zIndex = '100';
	objBody.insertBefore(objLightbox, objOverlay.nextSibling);

	// create link
	var objLink = document.createElement("a");
	objLink.setAttribute('href','#');
	//objLink.setAttribute('title','Click to close');
	objLink.onclick = function () {hideLightbox(''); return false;}
	objLightbox.appendChild(objLink);

	// preload and create close button image
	var imgPreloadCloseButton = new Image();

	// if close button image found,
	imgPreloadCloseButton.onload=function(){

		var objCloseButton = document.createElement("img");
		objCloseButton.src = closeButton;
		objCloseButton.setAttribute('id','closeButton');
		objCloseButton.style.position = 'absolute';
		objCloseButton.style.zIndex = '200';
		objLink.appendChild(objCloseButton);

		return false;
	}

	imgPreloadCloseButton.src = closeButton;

	// create details div, a container for the caption and keyboard message
	var objLightboxDetails = document.createElement("div");
	objLightboxDetails.setAttribute('id','lightboxDetails');
	objLightbox.appendChild(objLightboxDetails);


	// create overlayIframe div and hardcode some functional styles (aesthetic styles are in CSS file)
	var objOverlayIframe = document.createElement("div");
	objOverlayIframe.setAttribute('id','overlayIframe');
	objOverlayIframe.style.display = 'none';
	objOverlayIframe.style.position = 'absolute';
	objOverlayIframe.style.top = '0';
	objOverlayIframe.style.left = '0';
	objOverlayIframe.style.zIndex = '90';
	objOverlayIframe.style.width = '100%';
	objOverlayIframe.onclick = function () { hideLightbox(); };
	objBody.insertBefore(objOverlayIframe, objBody.firstChild);

	// create lightboxIframe div, same note about styles as above
	var objLightboxIframe = document.createElement("div");
	objLightboxIframe.setAttribute('id','lightboxIframe');
	objLightboxIframe.style.display = 'none';
	objLightboxIframe.style.position = 'absolute';
	objLightboxIframe.style.zIndex = '100';
	objBody.insertBefore(objLightboxIframe, objOverlayIframe.nextSibling);


	//	iframe id="frmMain" src="javascript:void(0)" name="frmMain" frameborder="0" height="100%" width="100%" scrolling="auto"

	// create iframe
	var objLightboxIframeDetails = document.createElement("iframe");
	objLightboxIframeDetails.setAttribute('id','lightboxDetailsIframe');
	objLightboxIframeDetails.setAttribute('name','lightboxDetailsIframe');
	objLightboxDetails.setAttribute('src','design/noForward');
	objLightboxIframeDetails.setAttribute('frameborder','0');
	objLightboxIframeDetails.setAttribute('scrolling','auto');
	objLightboxIframe.appendChild(objLightboxIframeDetails);

}
