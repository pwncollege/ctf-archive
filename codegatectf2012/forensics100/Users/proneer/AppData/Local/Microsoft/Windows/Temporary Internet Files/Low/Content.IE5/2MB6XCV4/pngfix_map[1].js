/*

Correctly handle PNG transparency in Win IE 5.5 and 6.
http://homepage.ntlworld.com/bobosola. Updated 18-Jan-2005.

Use in <HEAD> section with DEFER keyword
wrapped in conditional comments thus: 

    <!--[if lt IE 7]>
    <script defer type="text/javascript" src="pngfix.js"></script>
    <![endif]-->

This extended version includes imagemap and input image functionality.
It also requires a 1px transparent GIF.
 
*/
var strGif = "https://swdlp.apple.com/images/transparentpixel.gif"
var strFilter = "progid:DXImageTransform.Microsoft.AlphaImageLoader"
var arVersion = navigator.appVersion.split("MSIE")
var version = parseFloat(arVersion[1])

if ((version >= 5.5) && (document.body.filters)) 
{
	for(var i=0; i<document.images.length; i++)
	{
	   var img = document.images[i]
	   var imgName = img.src.toUpperCase()
	   if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
	   {
		  var imgID = (img.id) ? "id='" + img.id + "' " : ""
		  var imgClass = (img.className) ? "class='" + img.className + "' " : ""
		  var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
		  var imgStyle = "display:inline-block;" + img.style.cssText 
		  if (img.align == "left") imgStyle = "float:left;" + imgStyle
		  if (img.align == "right") imgStyle = "float:right;" + imgStyle
		  if (img.parentElement.href) imgStyle = "cursor:hand;" + imgStyle
		  if (img.useMap)
		  {  
			 strAddMap = "<img style=\"position:relative; left:-" + img.width + "px;"
			 + "height:" + img.height + "px;width:" + img.width +"\" "
			 + "src=\"" + strGif + "\" usemap=\"" + img.useMap 
			 + "\" border=\"" + img.border + "\">"
		  }
		  var strNewHTML = "<span " + imgID + imgClass + imgTitle
		  + " style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
		  + "filter:" + strFilter
		  + "(src=\'" + img.src + "\', sizingMethod='scale');\"></span>" 
		  if (img.useMap) strNewHTML += strAddMap
		  img.outerHTML = strNewHTML
		  i = i-1
	   }
	}

   for(i=0; i < document.forms.length; i++) findImgInputs(document.forms(i))
}

function findImgInputs(oParent)
{
   var oChildren = oParent.children
   if (oChildren)
   {
      for (var i=0; i < oChildren.length; i++ )
      {
         var oChild = oChildren(i)
         if ((oChild.type == 'image') && (oChild.src))
         {
			var imgName = oChild.src.toUpperCase()
			if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
			{
				var origSrc = oChild.src
				var origHeight = oChild.height
				var origWidth = oChild.width
				oChild.src = strGif
				oChild.height = origHeight 
				oChild.width = origWidth 
				oChild.style.filter = strFilter + "(src='" + origSrc + "')"
			}
         }
         findImgInputs(oChild)
      }
   }
}
