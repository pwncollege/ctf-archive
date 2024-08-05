var cal_nm;

// 달력 레이어에 달력을 출력함.
function calendar_draw(name, set_date){
	
	var days_arr = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	var week_name_arr = new Array("일", "월", "화", "수", "목", "금", "토");
	
	var t = new Date();
    
	// 넘어온 날짜. 없으면 오늘날짜 셋팅
	if(set_date == ""){
	    set_date = t.getFullYear() + "-" + zeroNum(t.getMonth()+1) + "-" +  zeroNum(t.getDate());
	}
	
	var date_arr = set_date.split("-");

	var to_year = Number(date_arr[0]);
	var to_month = Number(date_arr[1]);
	var to_day = Number(date_arr[2]);
	
	// 선택된 해당 일자. 없으면 오늘날짜 셋팅
	var sel_date = document.getElementById(name).value;
	if(sel_date == ""){
		sel_date = t.getFullYear() + "-" + zeroNum(t.getMonth()+1) + "-" +  zeroNum(t.getDate());
	}
	
	var sel_date_arr = sel_date.split("-");
	
	var sel_year = Number(sel_date_arr[0]);
	var sel_month = Number(sel_date_arr[1]);
	
	var prev_year;
	var prev_month;
	var next_year;
	var next_month;
	
	// 윤년
    if ( (to_year % 4 == 0) && (to_year % 100 != 0) || (to_year % 400 == 0) ) {
    	days_arr[1] = 29;
    } else {
    	days_arr[1] = 28;
    }
    
    // 이전
    if(to_month == 1){
        prev_year = to_year -1;
        prev_month = 12;
    }else{
        prev_year = to_year;
        prev_month = zeroNum((to_month-1));
    }
    
    var prev_date = prev_year +"-"+ prev_month +"-"+ to_day;
    
    
    // 다음
    if(to_month == 12){
        next_year = to_year +1;
        next_month = "01";
    }else{
        next_year = to_year;
        next_month = zeroNum((to_month+1));
    }
    
    var next_date = next_year +"-"+ next_month +"-"+ to_day;
    
    var calendar_t = new Date(to_year, to_month-1, 1);
    
    var week = calendar_t.getDay();
    
    var ihtml = "";
    
	ihtml += "	<div class='date'> \n"; 
	ihtml += "		<span class='prev'><a href='javascript:calendar_draw(\"" + name + "\",\"" + prev_date + "\");'><img src='http://image.ahnlab.com/ahnlab/ahnAdmin/kr/site/images/common/blt/cal_prev.gif' alt='이전' /></a></span> \n"; 
	ihtml += "		<strong>" + to_year + " / " + to_month + "</strong> \n"; 
	ihtml += "		<span class='next'><a href='javascript:calendar_draw(\"" + name + "\",\"" + next_date + "\");'><img src='http://image.ahnlab.com/ahnlab/ahnAdmin/kr/site/images/common/blt/cal_next.gif' alt='다음' /></a></span> \n"; 
	ihtml += "	</div> \n"; 
	ihtml += "	<table cellpadding='0' cellspacing='0' border='0' width='100%' class='calTbl' summary='달력'> \n"; 
	ihtml += "		<caption>달력</caption> \n"; 
	ihtml += "		<colgroup><col /><col style='width:14%;' span='6' /></colgroup> \n";
	
	ihtml += "		<thead> \n";
	ihtml += "			<tr> \n";
	for(var i=0; i<week_name_arr.length; i++){
	    if(i == 0) ihtml += "				<th class='sun'>" + week_name_arr[i] + "</th> \n";
	    else ihtml += "				<th>" + week_name_arr[i] + "</th> \n";
    }
	ihtml += "			</tr> \n";
	ihtml += "		</thead> \n";
	ihtml += "		<tbody> \n";
	ihtml += "			<tr> \n";
    
	for(var i=0; i<week; i++){
	    if(i == 0) ihtml += "				<td class='sun'>&nbsp;</td> \n";
	    else ihtml += "				<td>&nbsp;</td> \n";
	}
	
	for(var i=1; i<=days_arr[to_month-1]; i++){
		var to_date = to_year + "-" + zeroNum(to_month) + "-" + zeroNum(i);
		
	    var class_nm = "";
	    if(to_year == sel_year && to_month == sel_month && to_day == i){
	        class_nm = " class='today'";
	    }else if(((week)+i) % 7 == 1){
	        class_nm = " class='sun'";
	    }
	    if(((week)+i) % 7 == 1) ihtml += "				<td" + class_nm + "><a href='javascript:setCalValue(\"" + name + "\",\"" + to_date + "\");'>" + zeroNum(i) + "</a></td> \n";
	    else ihtml += "				<td" + class_nm + "><a href='javascript:setCalValue(\"" + name + "\",\"" + to_date + "\");'>" + zeroNum(i) + "</a></td> \n";
	        
	    if(((week)+i) % 7 == 0){
	        ihtml += "			</tr> \n";
    	    if(i != days_arr[to_month-1])  ihtml += "			<tr> \n";
    	}
	}
	if((week + days_arr[to_month-1]) % 7 != 0){
    	for(var i=0; i<7 - ((week + days_arr[to_month-1]) % 7); i++){
    	    ihtml += "				<td>&nbsp;</td> \n";
    	}
    	ihtml += "				</tr> \n";
    }
	ihtml += "		</tbody> \n";
	ihtml += "	</table> \n";
	
    //alert(ihtml);
    document.getElementById("popCal").innerHTML = ihtml;
}

function zeroNum(n){
	
    if(n < 10) return "0" + n;
    else return n;
}

function getOffsetTop(obj){ return obj ? obj.offsetTop + getOffsetTop(obj.offsetParent) : 0; }
function getOffsetLeft(obj){ return obj ? obj.offsetLeft + getOffsetLeft(obj.offsetParent) : 0; }

function showLayCal2( hook, obj ){
	calendar_draw(obj.id, obj.value);
	
	var oPrev = document.getElementById('popCal') ;
	var oBody = ( document.compatMode && document.compatMode!="BackCompat" ) ? document.documentElement : document.body  ;

	if ( cal_nm != hook.id ){
		var nLyrWidth = 172 ;
		var nLyrInt = -150 ;
		var nArrowInt = 0 ;
		var nHookLeft = getOffsetLeft(hook) ;
		var nHookTop = getOffsetTop(hook) ;
		if ( nHookLeft + nLyrWidth > oBody.scrollWidth )
		{
			nLyrLeft = oBody.scrollWidth - nLyrWidth - 5 ;
			nArrowLeft = nHookLeft-nLyrLeft+nLyrInt+nArrowInt ;
		}else{
			nLyrLeft = nHookLeft+nLyrInt ;
			nArrowLeft = nArrowInt ;
		}
		nLyrTop = nHookTop + hook.clientHeight+5 ;
		oPrev.style.left = nLyrLeft + "px" ;
		oPrev.style.top = nLyrTop + "px" ;
		oPrev.style.display = "block" ;
		
		cal_nm = hook.id;
	}else{
		oPrev.style.display = 'none';
		cal_nm = "";
	}
}

function setCalValue(name, set_date){
	
	document.getElementById(name).value = set_date;
	document.getElementById('popCal').style.display = 'none';
	cal_nm = "";
}

