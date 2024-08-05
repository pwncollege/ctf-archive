var Common = {
    /**
     * 필드가 비어 있는 지 검사
     */
    isValue: function(str){
    	if (( str == null) || (str == "") || Common.isblank(str))
    		return false;		
    	return true;		
    },

    /**
     * 문자열에 공백 문자만 포함된 경우에는 true를 반환하는 유틸리티 함수 
     */
    isblank: function(str) {
    	for(var i = 0; i < str.length ; i++ ){
    		var c = str.charAt(i);
    		if ( (c != ' ') && (c != '\n') && (c != '\et')) {
    			return false;
    		}
    	}
    	return true;
    },
    /**
     * 정규식(Regular Expression)을 사용한 앞뒤 트림임다.
     */
    trim: function(str) {
    	 regExp = /([^\s*$]?)(\s*$)/;
    	 newStr = str.replace(regExp, "$1");
    	 regExp = /(^\s*)(.+)/;
    	 newStr = newStr.replace(regExp, "$2");
       
    	 return newStr;
     },
     /**
      * 파일 확장자 체크.
      * @param {checkExt} 허용하는 확장자 (eg. jpg|gif|bmp|...)
      */
     checkExt: function(str, checkExt) {
    	  
    	 if(str == "") return true;
    	 
    	 var dotIndex = str.lastIndexOf(".");
    	 var ext = str.substring(dotIndex+1).toLowerCase();
    	 var pattern = eval("/^(" + checkExt.toLowerCase() + "){1}$/");
		
    	 return (ext.search(pattern) != -1);
     },
     /**
     * 로그인 체크
     * @param {redirct url}
     */
     login : function(url) {
    	 
    	 var from_url = (url != "")? url : location.href; 
    	 
    	 location.href = "/kr/site/login/loginForm.do?from_url="+escape(from_url);
     },
     /**
     * 로그인 체크(메세지 포함, 세션키존재시:true)
     * @param {redirct url, sessionKey}
     */     
     checkLogin : function(url, sessionKey) {
    	 
    	 var bRtn = false;
    	 var from_url = (url != "")? url : location.href; 
    	 
    	 if(sessionKey == "") {
    		 
    		 if(confirm("로그인 후 사용이 가능합니다.\n\n로그인 하시겠습니까?"))
    			 location.href = "/kr/site/login/loginForm.do?from_url="+escape(from_url);
    	 }
    	 else
    		 bRtn = true;
    	 
    	 return bRtn;
     },
     /**
     * 필드가 비어 있는 지 검사
     */
    chkPasswdContinue: function(msg){
    	var cnt = 0;
		for( var i=0; i < msg.length; ++i)
		{
			if( msg.charAt(0) == msg.substring( i, i+1 ) ) ++cnt;
		}
		if( cnt != msg.length ) {
			return true;
		}else{
			return false;
		}		
    },
    
    chkPasswdDanger: function(msg){
    	if(msg.length > 16 && msg.length < 8){
			return false;
		}else if(Common.IsAlphabet(msg)){
			return false;
		}else if(Common.IsNumber(msg)){
			return false;
		}else{
			return true;
		}
    },
    
    checkMix: function(msg){
    	var valid1 = true;
		var valid2 = true; 
		var as="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		var bs="0123456789"; 
		var count=0;
		
		   if (Common.IsEmpty(msg)){
		      return false;
		   }
		   for (var i=0; i < msg.length; i++) {
			   if(as.indexOf(msg.charAt(i)) > -1 ){
					valid1 = false;
				}
				if(bs.indexOf(msg.charAt(i)) > -1){
					valid2 = false;
				} 
		   }
		   
		  if(valid1){
		  	count++;
		  }
		  if(valid2){
		  	count++;
    	  } 
		  
		  if(count > 0){
			  return false;		  
		  }
		  
		  return true;
    },
    
    IsNumber: function(msg){
    	if (Common.IsEmpty(msg))
	      return false;
	
	   for (var i=0; i < msg.length; i++) {
	      if ( (msg.charAt(i) < "0") || (msg.charAt(i) > "9") )
	         return false;
	   }
	
	   return true;
    },
    
    IsAlphabet: function(msg){
    	if (Common.IsEmpty(msg))
	      return false;
	
	   	for (var i=0; i < msg.length; i++) {
	      if ( ( (msg.charAt(i) < "A") || (msg.charAt(i) > "Z") ) &&
	           ( (msg.charAt(i) < "a") || (msg.charAt(i) > "z") ) )
	         return false;
	   	}
	
	   	return true;
    },
    
    IsEmpty: function(msg){
    	 return !Common.CheckValid(msg, false);
    },
    
    CheckValid: function(msg, SpaceCheck){
    	var retvalue = false;

	   	for (var i=0; i<msg.length; i++) {
	
	      if (SpaceCheck == true) {
	         if (msg.charAt(i) == ' ') {
	            retvalue = true;
	            break;
	         }
	      } else {
	         if (msg.charAt(i) != ' ') {
	            retvalue = true;
	            break;
	         }
	      }
	   }
	
	   return retvalue;
    }
     
};

var POPUP = {
    open: function(url, popname, options) {
		var width = options.width;
		var height = options.height;
		var top = (screen.height - height) / 2 - 50;
		var left = (screen.width - width) / 2;
		var scroll = options.scroll;
		var resize = options.resize;
		var properties = 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left;
		properties += scroll && scroll === true ? ",scrollbars=yes" : "";
		properties += resize && resize === true ? ",resizable=yes" : "";
		var pop = window.open(url, popname, properties);
		pop.focus();
	},
	reload: function() {
		location.reload();
	},
	reloadAndClose: function() {
		opener.location.reload();
		self.close();
	},
	close: function() {
		self.close();
	}
}

var Check = {
    /**
     * 영문만 사용가능
     */
    english: function(str) {
    	var valid_reg = /[A-Za-z]/;
    	if ( valid_reg.test( str ) )
    	{
    		return true;
    	}
    	
    	return false;
    },
    /**
     * 한글만 사용가능
     */
    korea: function(msg) {
    	var str = new String(msg);
    	len = str.length;
    
    	for (k=0 ; k<len ; k++){
    		temp = str.charAt(k);
    
    		if (escape(temp).length > 4) {
    			return true;
    		}
    	}
    	
    	return false;
    },
    /**
     * 전화번호 유효성 체크
     */
    phone: function(num) {
    	var tels = num.split('-');
    	if(tels.length == 2){
    		num = '02-' + num;
    		tels = num.split('-');
    	}
    	
    	var valid_reg = /^[0-9]{1,}\-[0-9]{1,}\-[0-9]{1,}$/
    	if ( !valid_reg.test( num ) )
    	{
    		return false;
    	}
    
    	if(tels[1].length > 4 || tels[2].length != 4)
    		return false;
    		
    	return true;
    },
    /**
     * E-mail 체크
     */
    email: function(strMail) {
        var check1 = /(@.*@)|(\.\.)|(@\.)|(\.@)|(^\.)/; 
    
        var check2 = /^[a-zA-Z0-9\-\.\_]+\@[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4})$/;
        
        if ( !check1.test(strMail) && check2.test(strMail) ) {
            return true;
        } else {
            return false;
        }
    },
    /**
     * 숫자 체크
     */
    number: function(msg) {
    	re = /[0-9]/;
    
        if(re.test(msg)){
        	return true;
        }else{
        	return false;
        }
    },
    /**
     * 숫자 체크
     */
    engandnumber: function(msg) {
    	re = /[A-Za-z0-9]/;
    
        if(re.test(msg)){
        	return true;
        }else{
        	return false;
        }
    },
    /**
     * 주민번호 체크
     */
    jumin: function(val1, val2){
		var tmp1,tmp2
		var t1, t2, t3, t4, t5, t6, t7
		tmp1 = val1.substring(2,4);
		tmp2 = val1.substring(4);
		//alert(tmp1 + " - " + tmp2);
		if ((tmp1 < "01") || (tmp1 > "12")) return false;
		if ((tmp2 < "01") || (tmp2 > "31")) return false;
		t1 = val1.substring(0,1);
		t2 = val1.substring(1,2);
		t3 = val1.substring(2,3);
		t4 = val1.substring(3,4);
		t5 = val1.substring(4,5);
		t6 = val1.substring(5,6);
		t11 = val2.substring(0,1);
		t12 = val2.substring(1,2);
		t13 = val2.substring(2,3);
		t14 = val2.substring(3,4);
		t15 = val2.substring(4,5);
		t16 = val2.substring(5,6);
		t17 = val2.substring(6,7);

		var tot = t1*2 + t2*3 + t3*4 + t4*5 + t5*6 + t6*7;
		tot += t11*8 + t12*9 + t13*2 + t14*3 + t15*4 + t16*5 ;

		var result = tot % 11;
		result = (11 - result) % 10;
		if (result != t17) return false;
		return true;
	},
    /**
    * 10자리의 사업자등록번호에 대해 유효성 체크 결과(true/false)를 돌려주는 함수.(999-99-99999)
    */
    business_id: function(businessId){
		if (businessId.length != 12) {
		    return false;
		}
		  var strCk1 = businessId.substring(0, 3);
		  var strCk2 = businessId.substring(4, 6);
		  var strCk3 = businessId.substring(7, 12);
		  arrCkValue = new Array(10);

		if((strCk1.length==3) && (strCk2.length==2) && (strCk3.length==5)) {
		    arrCkValue[0] = ( parseFloat(strCk1.substring(0 ,1))  * 1 ) % 10;
		    arrCkValue[1] = ( parseFloat(strCk1.substring(1 ,2))  * 3 ) % 10;
		    arrCkValue[2] = ( parseFloat(strCk1.substring(2 ,3))  * 7 ) % 10;
		    arrCkValue[3] = ( parseFloat(strCk2.substring(0 ,1))  * 1 ) % 10;
		    arrCkValue[4] = ( parseFloat(strCk2.substring(1 ,2))  * 3 ) % 10;
		    arrCkValue[5] = ( parseFloat(strCk3.substring(0 ,1))  * 7 ) % 10;
		    arrCkValue[6] = ( parseFloat(strCk3.substring(1 ,2))  * 1 ) % 10;
		    arrCkValue[7] = ( parseFloat(strCk3.substring(2 ,3))  * 3 ) % 10;
		    intCkTemp     = parseFloat(strCk3.substring(3 ,4))  * 5  + "0";
			arrCkValue[8] = parseFloat(intCkTemp.substring(0,1)) + parseFloat(intCkTemp.substring(1,2));
			arrCkValue[9] = parseFloat(strCk3.substring(4,5));
			intCkLastid = ( 10 - ( ( arrCkValue[0]+arrCkValue[1]+arrCkValue[2]+arrCkValue[3]+arrCkValue[4]+arrCkValue[5]+arrCkValue[6]+arrCkValue[7]+arrCkValue[8] ) % 10 ) ) % 10;
	    if (arrCkValue[9] != intCkLastid) {
		  //alert ("잘못된 사업자등록번호입니다.\n사업자등록번호를 다시 입력하세요.");
		  //thisObj.select();
	      return false;
	    } else {
	      return true;
	    }
		} else {
		  //  alert("22사업자등록번호의 자릿수가 잘못 입력되었습니다.");
		  //thisObj.select();
		    return false;
		}
	}
};

/**
* CheckBox 컨트롤 및 밸리데이션
*/
var CheckBox = {
		
	/**
	* CheckBox 전체선택/전체해제 컨트롤
	* @param {chkBoxId}	전체선택용 체크박스 Id
	* @param {chkListId}다중체크박스 Id
	* @param {btnSpnId}	전체선택용 버튼 Span Id
	*/
	allCheck: function(chkBoxId, chkListId, btnSpnId) {

		if($("#"+chkBoxId).is(':checked')) {
			$("input:checkbox[id="+chkListId+"]").each(function() {
				$(this).attr("checked", true);
			});
			if(btnSpnId != "") 
				$("#"+btnSpnId).find("a").html("전체해제");
		}
		else {
			$("input:checkbox[id="+chkListId+"]").each(function() {
				$(this).attr("checked", false);
			});
			if(btnSpnId != "") 
				$("#"+btnSpnId).find("a").html("전체선택");
		}
	},
	/**
	* CheckBox 선택여부 판별(다중체크박스포함)
	* @param {chkBoxId} 체크박스 Id
	*/
	valChecked: function(chkBoxId) {
		
		if($("input:checkbox[id="+chkBoxId+"]:checked").length < 1)
			return false;
		else
			return true;
	},
	/**
	* CheckBox Multi Append
	* @param {divId} 	: 체크박스 그룹의 부모 div테그 Id
	* @param {chkBoxId} : 체크박스 Id
	* @param {labelName}: 체크박스 라벨명
	* @param {value}	: 체크박스 Value
	* @param {chkDiv} 	: 체크여부 (0:미체크/1:체크)
	*/
	append: function(divId, chkBoxId, labelName, value, chkDiv) {
		
		var htmlCheckBox= "";
		var strChecked	= "";
		
		if(chkDiv == 1)
			strChecked = "checked";
			
		htmlCheckBox += " <input type='checkbox' name='"+chkBoxId+"' id='"+chkBoxId+"' value='"+value+"' "+strChecked+" style='padding-bottom:5px;'/> "+labelName+"<br/>";
		
		return htmlCheckBox;
		
		/*
	    $("#"+divId).append(
        $(document.createElement("input")).attr({
                id		: chkBoxId
                ,name	: chkBoxId
                ,type	: "checkbox"
                ,value	: value
				,style	: "padding-bottom:5px;"
				,checked: chkDiv
        })).append(
             $(document.createElement('label')).attr({
            	 id		: "label"
        	}).text(" "+labelName)
		).append("<br/>");
	    */
	}
}

/**
* Dynamic Input 컨트롤
*/
var Input = {
		
	/**
	* Input Dynamic Append
	* @param {divId} 	: Append시킬 부모 노드의 Id
	* @param {inpType}	: Input Type (text, hidden..)	
	* @param {inpId}	: Input Id
	* @param {inpName}	: Input Name	
	* @param {inpValue}	: Input Value
	*/
	append: function(divId, inpType, inpId, inpName, inpValue) {

	    $("#"+divId).append(
        $(document.createElement("input")).attr({
                 id		: inpId
                ,name	: inpName
                ,type	: inpType
                ,value	: inpValue
        }));
	}
}

/**
* Dynamic SelectBox 컨트롤
*/
var selWidth=0;
var Select = {
	
	add : function(selId, keyValue, textValue) {

		var addOpt = document.createElement('option');
		var attWidth = (keyValue == "")? 50 : 30;
		var tmpWidth = (textValue.length * 11) + attWidth;
		
		if(selWidth < tmpWidth)
			selWidth = tmpWidth;
	
		addOpt.value = keyValue;
		addOpt.appendChild(document.createTextNode(textValue));
	
		$("#"+selId).css("width",selWidth);
		$("#"+selId).append(addOpt);
	},
	addStaticWidth : function(selId, keyValue, textValue, selWidth) {

		var addOpt = document.createElement('option');

		addOpt.value = keyValue;
		addOpt.appendChild(document.createTextNode(textValue));
	
		$("#"+selId).css("width",selWidth);
		$("#"+selId).append(addOpt);
	},	
	removeAll : function(selId) {
		$("#"+selId).find('option').each(function() {
			$(this).remove();
		});
	},
	removeUnit : function(selId) {
		$("#"+selId+" option:selected").remove();
	},
	length : function(selId) {
		var i = 0;
		$("#"+selId).find('option').each(function() {
			++i;
		});
		return i;
	}
}

/**
* 문자열 교체 
* ex) str.replaceAll("a", "b") : "a"->"b"로 모두 교체
*/
String.prototype.replaceAll = replaceAll;
function replaceAll(strValue1, strValue2)
{
	var strTemp = ""+this;
	strTemp = strTemp.replace(new RegExp(strValue1, "g"), strValue2);
	return strTemp;
}

/**
 * @author Daegeun Kim (machone@machone.kr)
 */
$.fn.ajaxSubmit = function(options) {
	var self = $(this);
	$(this).attr("showerrors", "true");
	if (self.valid()) {
		var params = {};
		self.find("input[checked], input[type=text], input[type=hidden], input[type=password], option[selected], textarea") .each(function() {var k = this.name || this.id || this.parentNode.name || this.parentNode.id;if (params[k]) {if ($.isArray(params[k])) {params[k].push(this.value);} else {params[k] = [params[k], this.value];}} else {params[k] = this.value;}});
		$.post(self.attr("action") + "?callType=ajax", params, function(json) {
			if (options && $.isFunction(options.success)) {
				options.success.call(null, json);
			}
		}, "json");
	}
	return this;
}

/**
* ajax - json 처리
* @param {String} 요청 url
* @param {String} callbackFunc
*/
$.fn.ajaxSubmitJSON = function(url, callbackFunc) {
	var self = $(this);
	$(this).attr("showerrors", "true");
	if (self.valid()) {
		var params = {};
		self.find("input[checked], input[type=text], input[type=hidden], input[type=password], option[selected], textarea") .each(function() {var k = this.name || this.id || this.parentNode.name || this.parentNode.id;if (params[k]) {if ($.isArray(params[k])) {params[k].push(this.value);} else {params[k] = [params[k], this.value];}} else {params[k] = this.value;}});
       $.getJSON(url + "?callType=ajax", params,
           function(data) {   // callback 후 수행할 부분
    	   	  //if(data['return']['code'] == '0'){
                   eval(callbackFunc(data));
               //}else{
                   //에러처리
               //}
           });

	}
	return this;
}

if ($.datepicker) {
	$.datepicker.setDefaults({
		dateFormat: "yy.mm.dd",
		monthNames: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
		monthNamesShort: ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"],
		dayNamesMin: ["일", "월", "화", "수", "목", "금", "토"],
		dayNamesShort: ["일", "월", "화", "수", "목", "금", "토"],
		showOn: 'button', buttonImage: "/kr/common/img/calendar/ic_calendar.gif", buttonImageOnly: true
	});

	$(function() {
		var options = null;
		try {
			options = datepickerOptions.call(null);
		} catch (e) {
		}
		if (options != null) {
			$("input.ui-calendar").datepicker(options);
		} else {
			$("input.ui-calendar").datepicker();
		}
	});
}

/**
* ajax - html 처리
*/
$.fn.ajaxSubmitHtml = function(options) {
	var self = $(this);
	$(this).attr("showerrors", "true");
	if (self.valid()) {
		var params = {};
		self.find("input[checked], input[type=text], input[type=hidden], input[type=password], option[selected], textarea") .each(function() {var k = this.name || this.id || this.parentNode.name || this.parentNode.id;if (params[k]) {if ($.isArray(params[k])) {params[k].push(this.value);} else {params[k] = [params[k], this.value];}} else {params[k] = this.value;}});
		$.post(self.attr("action") + "?callType=ajax", params, function(html) {
			if (options && $.isFunction(options.success)) {
				options.success.call(null, html);
			}
		}, "html");
	}
	return this;
}

if ($.validator) {
	$.validator.addMethod("gt", function(value, element, params) {return isNaN(params) ? (value > params) : (parseInt(value) > params);}, "gt error");
	$.validator.addMethod("lt", function(value, element, params) {return isNaN(params) ? (value < params) : (parseInt(value) < params);}, "lt error");
	$.validator.addMethod("eq", function(value, element, params) {return value > params;}, "eq error");
	$.validator.addMethod("not", function(value, element, params) {return value != params;}, "not error");
	$.validator.addMethod("downToPoint", function(value, element, params) {return ((Math.pow(10,params)*value).toString()).indexOf(".")<0;}, "downToPoint error");
	$.validator.addMethod("greatThan", function(value, element, params) {var type = params && params.type ? params.type : null; var equal = params && params.equal === true ? true : false;var current = $(element).val(); var target = params && params.target ? $(params.target).val() || params.target : "";if (type == "calendar" && current) {current = parseInt(current.replace(/[-. :]/g, ''));target = parseInt(target.replace(/[-. :]/g, ''));if (current > target) return true;else if (current == target)	 return equal;else return false;}return true;});
	$.validator.addMethod("lessThan", function(value, element, params) {var type = params && params.type ? params.type : null;var equal = params && params.equal === true ? true : false;var current = $(element).val();var target = params && params.target ? $(params.target).val() || params.target : "";if (type == "calendar" && current) {current = parseInt(current.replace(/[-. :]/g, ''));target = parseInt(target.replace(/[-. :]/g, ''));if (current < target) return true;else if (current == target)	 return equal;else return false;}return true;});
}

function popAsecWarning(){
	POPUP.open('/kr/site/securitycenter/asec/popAsecWarning.do', 'popAsecWarning', {width: 600, height: 680});
}

function checkEmailHangle(){
	if(Check.korea($("#email1").val()) == true){
		$("#email1").attr("value", "");
		$("#email1").focus();
	}
}

function checkHangle(obj){
	if(Check.korea($("#" + obj).val()) == true){
		$("#" + obj).attr("value", "");
		$("#" + obj).focus();
	}
}

