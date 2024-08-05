function dosubmit(form) {
	if(document.forms[form].keyword.value=='' || document.forms[form].keyword.value=='E.g: Brothersoft' || document.forms[form].keyword.value=='Brothersoft'){
		var	stype	= document.forms[form].stype.value;
		switch (stype) {
			case 'windows' :
				de_keyword	=	'Brothersoft';
				break;
			case 'mobile' :
				de_keyword	=	'ebuddy';
				break;
			case 'games' :
				de_keyword	=	'farmville tools';
				break;
			case 'mac' :
				de_keyword	=	'bootcamp';
				break;
			case 'widgets' :
				de_keyword	=	'virtual family';
				break;
			default :
				de_keyword	=	'Brothersoft';
		}
		document.forms[form].keyword.value	=	de_keyword;
	}
	document.forms[form].submit();
}
