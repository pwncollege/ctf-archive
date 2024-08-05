//location navigation setup
	var menuids=["lnb_list"];
	function buildsubmenus_horizontal()
	{
		for (var i=0; i<menuids.length; i++)
		{
			var ultags=document.getElementById(menuids[i]).getElementsByTagName("ul");
			for(var t=0; t<ultags.length; t++)
			{
				if(ultags[t].parentNode.parentNode.id==menuids[i]) ultags[t].style.top=ultags[t].parentNode.offsetHeight+"px";
				else
				{
					ultags[t].style.left=(ultags[t-1].getElementsByTagName("a")[0].offsetWidth-3)+"px";
					ultags[t].parentNode.getElementsByTagName("a")[0].className="sub_item png_bg";
				}
				ultags[t].parentNode.onmouseover=function(){this.getElementsByTagName("ul")[0].style.visibility="visible"}
				ultags[t].parentNode.onmouseout=function(){this.getElementsByTagName("ul")[0].style.visibility="hidden"}
			}
		}
	}
	if (window.addEventListener) window.addEventListener("load", buildsubmenus_horizontal, false);
	else if (window.attachEvent) window.attachEvent("onload", buildsubmenus_horizontal);

	function intro_msg(object)
	{
		if(object.value==object.title){
			object.value ="";
			object.style.fontStyle="normal";
			object.style.color="#4C4C4C";
		}
	}