if(typeof(_main_name)!='string'){
	_main_name='';
}
if(typeof(_sub_name)!='string'){
	_sub_name='';
}
var THIS_A_INFO_ID=null;
try{eval('THIS_A_INFO_ID=_BS_A_INFO_'+_BSAD_ID);}
catch(e){}
if(typeof(THIS_A_INFO_ID)=='string'){
	_BSAD_WIDTH=_BS_A_INFO_WIDTH;
	_BSAD_HEIGHT=_BS_A_INFO_HEIGHT;
}else{
	try{THIS_A_INFO_ID_WIDTH=null;THIS_A_INFO_ID_HEIGHT=null;eval('THIS_A_INFO_ID_WIDTH=_BS_A_INFO_WIDTH_'+_BSAD_ID);eval('THIS_A_INFO_ID_HEIGHT=_BS_A_INFO_HEIGHT_'+_BSAD_ID);}
	catch(e){}
	if(typeof(THIS_A_INFO_ID_WIDTH)=='number' && typeof(THIS_A_INFO_ID_HEIGHT)=='number'){
		_BSAD_WIDTH=THIS_A_INFO_ID_WIDTH;
		_BSAD_HEIGHT=THIS_A_INFO_ID_HEIGHT;
	}
}
	if(typeof(_BSAD_WIDTH)!='undefined' && _BSAD_WIDTH>0)
	{
	var BASD_str="<iframe id=\"_BSAD_iframe_show_"+_BSAD_ID+"\" width=\""+_BSAD_WIDTH +"\" scrolling=\"no\" height=\""+ _BSAD_HEIGHT+"\" frameborder=\"0\" vspace=\"0\"  src=\"http://g.brothersoft.com/show.html?bs_id="+_BSAD_ID+"&main_name="+escape(_main_name)+"&sub_name="+escape(_sub_name)+"&location_url="+encodeURIComponent(window.location.href)+"\"  marginwidth=\"0\" marginheight=\"0\"  hspace=\"0\" allowtransparency=\"true\" ></iframe>";
	document.write(BASD_str);
	}
	_BSAD_HEIGHT=0;_BSAD_HEIGHT=0;