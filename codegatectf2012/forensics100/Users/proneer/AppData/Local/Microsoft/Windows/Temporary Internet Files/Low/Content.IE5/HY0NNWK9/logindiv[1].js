String.prototype.Trim=function(){
	return this.replace(/(^\s*)|(\s*$)/g, "");
}
function tryThese(){
	 var i , t , r;
	 for(i=0;
	 i < arguments.length;
	 i++){
		 t=arguments[i];
		 try{
			 r=t();
			 break;
		} catch(e){
			 
		} 
	} return r;
 }
function getAjaxObject(){
return tryThese(
function() {return new XMLHttpRequest();} ,
function() {return new ActiveXObject('Microsoft.XMLHTTP');} ,
function() {return new ActiveXObject('Msxml2.XMLHTTP');} ,
function() {return new ActiveXObject('MSXML2.XMLHttp.4.0');} ,
function() {return new ActiveXObject('MSXML2.XMLHttp.3.0');} ,
function() {return new ActiveXObject('MSXML2.XMLHttp.5.0');} ,
function() {return new ActiveXObject('MSXML.XMLHttp');})||false;
}
function F_Ajax(url,callback)
{
	this.ajax=getAjaxObject();
	this.ajax.open("GET",url,true);
	this.ajax.onreadystatechange=callback;	
	this.ajax.send(null);
}

//showDiv function

function F_openNewDiv(parentDiv,childDiv) {    
	if(document.getElementById(parentDiv)){
    var m = "mask";  
    var thisWheight=0;
    var thisWwidth=0;
    var thisWtop=0;
    cHeight=document.body.clientHeight;
    sheight=document.body.scrollHeight;
    oHeight=document.body.offsetHeight;
	thisWheight=document.body.scrollHeight>document.documentElement.scrollHeight?document.body.scrollHeight:document.documentElement.scrollHeight;
	thisWheight=thisWheight> document.documentElement.clientHeight?thisWheight:document.documentElement.clientHeight;
	thisWwidth=document.body.scrollWidth>document.documentElement.scrollWidth?document.body.scrollWidth:document.documentElement.scrollWidth;
	thisWtop=document.body.scrollTop>document.documentElement.scrollTop?document.body.scrollTop:document.documentElement.scrollTop;
   

   var newDiv=document.getElementById(childDiv) ;
   
   newDiv.style.position = "absolute";    
   newDiv.style.zIndex = "9999";  
   newDiv.style.top = (parseInt(thisWtop)+200)+ "px";     
   
   newDiv.style.left = (parseInt(document.body.scrollWidth) - parseInt(newDiv.style.width)) / 2 + "px"; 
   newDiv.style.background = "#EFEFEF";      
   newDiv.style.display="";
    
   var newMask=document.getElementById(parentDiv);
   newMask.style.position = "absolute";    
   newMask.style.zIndex = "150"; 
   newMask.style.width = thisWwidth + "px";    
   thisWheight=parseInt(thisWheight)<parseInt(document.body.clientHeight)?document.body.clientHeight:thisWheight;
   newMask.style.height = thisWheight + "px"; 
   newMask.style.top = "0px";    
   newMask.style.left = "0px";    
   newMask.style.filter = "alpha(opacity=40)";    
   newMask.style.opacity = "0.40";   
   newMask.style.display=""; 
   
   }else{
	   //alert('dd');
	   }
}  
function toCloseDiv(parentDiv,childDiv)
{
	var newDiv=document.getElementById(childDiv);
	if(newDiv){}else{return;}
	var newMask=document.getElementById(parentDiv);
	newDiv.style.display="none";
	newMask.style.display="none";  
}

function onReSize(parentDiv,childDiv)
{
	thisWwidth=document.body.scrollWidth>document.documentElement.scrollWidth?document.body.scrollWidth:document.documentElement.scrollWidth;
	thisWwidth=document.body.scrollWidth>document.documentElement.scrollWidth?document.body.scrollWidth:document.documentElement.scrollWidth;
	pDiv=document.getElementById(parentDiv);
	cDiv=document.getElementById(childDiv);
	if(pDiv.style.display==''){
		pDiv.style.width=thisWwidth+'px';
		pDiv.style.left = "0px"; 
	}
	if(cDiv.style.display==''){
		 cDiv.style.left = (parseInt(document.body.scrollWidth) - parseInt(cDiv.style.width)) / 2 + "px";	
	}
}


//WINDOWS
function logWindows(){
	this.logW='';
	this.regW='';
	
	this.logW+='<div style="  width:328px;border:3px solid #E5E5E5; overflow:visible; background:#FFFFFF;color:#333333;font-size:11px;font-weight:bold;" onkeydown="onkey(event,this,1)">';
	this.logW+='	<div style="width:320px; border:4px solid #ccc;">';
	this.logW+='		<h2 style="font-family:Arial;  background:#eaeaea; padding:10px 12px 5px 11px; width:px; margin:0px; font-size:20px; line-height:20px; height:20px; font-weight:bold;float:left;width:297px;">';
	this.logW+='			<span style=" float:right;"><img src="http://img.brothersoft.com/v1/img/down_lik_report_close.gif"  onclick="hideLogin();return false;" style="cursor:pointer"/></span>Login&nbsp;&nbsp;';
	this.logW+='		</h2>';
	this.logW+='		<ul style="margin:0px; padding:0px;font-family:Verdana;font-style:normal;clear:both;padding-top:8px;">';
	this.logW+='		<li style="line-height:20px;  margin:0px auto;  list-style:none; display:bock; width:90%;padding-left:5px;background-color:#F7FFEF;border:1px #eaeaea solid;font-size:11px;font-weight:normal;">Don&#39;t have an account?<br><a style=" text-decoration:underline; font-size:14px;cursor:pointer;color:#0066cc;font-weight:600;" onclick="hideLogin();showReg();"> Join Brothersoft now!</a>	<img id="log_AJ" src="http://img.brothersoft.com/ppd/images/ajax_load.gif" style="display:none" /></li>';
	this.logW+='		<li style="list-style:none; margin-left:15px; margin-top:5px; line-height:30px; height:30px;">Email:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="loginUserName" style="width:180px; height:16px;display:inline;margin-top:7px;"/></li>';
	this.logW+='		<li style="list-style:none; margin-left:15px; line-height:30px; height:30px;margin-top:10px;margin-bottom:10px;">Password:&nbsp;<input  type="password" id="loginPassWord"  style="width:180px; height:16px;display:inline;margin-top:7px;margin-left:2px;" /></li>';
	this.logW+='		<li style="list-style:none; margin-left:15px; line-height:20px; margin-top:10px;font-weight:normal;width:90%;margin:0px auto;font-size:11px;"><input type="checkbox" name="remember_login" id="remember_login" value="1" checked/>&nbsp;&nbsp;Keep me signed in on this computer.</li>';
	this.logW+='		<li  style="list-style:none; height:0px; line-height:0px;vertical-align:middle; text-align:center; font-weight:100;color:red;overflow:hidden; text-align:left;padding-left:20px;display:none"><div id="log_err_show" style="line-height:20px;width:90%"></div></li>';
	this.logW+='		<li style="list-style:none; line-height:40px; height:40px; margin-top:10px;text-align:center;"><span style="width: 115px; float: right; font-weight: 100; font-size: 10px;  height: 40px; line-height: 40px;"><a href="http://www.brothersoft.com/info/privacy/" target="_blank" style="color:#0066cc;">Privacy Policy</a></span><span style="width: 110px; float: right; font-weight: 100; font-size: 10px; height: 40px; line-height: 40px;"><a href="http://www.brothersoft.com/user/?act=member.passwordforget" target="_blank" style="color:#0066cc;">Forget Password</a></span><input class="send" type="image" style="margin: 10px 0px 0px 10px;" onclick="goLogin();" src="http://img.brothersoft.com/v1/ppd/submit.gif"/></li>';
	this.logW+='		<li style="list-style:none; margin-left:15px; line-height:20px; height:20px;margin-top:1px;margin-bottom:10px;">If you are an author, <a href="http://author.brothersoft.com/?act=Register.reg&figure=author" style="text-decoration:underline; font-size:14px;cursor:pointer;color:#0066cc;font-weight:600;">Click here</a> to register.</li>';
	this.logW+='		</ul>';
	this.logW+='	</div>';
	this.logW+='</div>';
	
	
	this.regW+='<div style="width:328px;border:3px solid #E5E5E5; overflow:visible; background:#FFFFFF;color:#333333; font-size:11px; font-weight:bold;"  onkeydown="onkey(event,this,2)">';
	this.regW+='	<div id="reg_body" style="width:320px; border:4px solid #ccc;">';
	this.regW+='		<h2 style="font-family:Arial;  background:#eaeaea; padding:10px 12px 5px 11px; width:px; margin:0px; font-size:20px; line-height:20px; height:20px;font-weight:bold;float:left;width:295px;width:297px;">';
	this.regW+='			<span style=" float:right;"><img src="http://img.brothersoft.com/v1/img/down_lik_report_close.gif"  onclick="hideReg();return false;" style="cursor:pointer"/></span><span id="reg_title">Join Brothersoft</span>&nbsp;&nbsp;';
	this.regW+='		</h2>';
	this.regW+='		<ul id="reg_ok_ul" style="margin:0px; padding:0px;font-family:Verdana;font-style:normal;clear:both;padding-top:8px;">';
	this.regW+='		<li style=" line-height:20px;  margin:0px auto;  list-style:none; display:bock; width:90%;padding-left:5px;background-color:#F7FFEF;border:1px #eaeaea solid;font-size:11px;font-weight:normal;"><div id="reg_to_login_but">Already a Brothersoft member?<a style=" text-decoration:underline; margin-left:5px; font-size:14px;cursor:pointer;color:#0066cc;font-weight:600;" onclick="hideReg();showLogin()">Login</a><br></div>Not yet a member? Create a free account below.<img id="reg_AJ" src="http://img.brothersoft.com/ppd/images/ajax_load.gif" style="display:none" /></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; margin-top:5px; line-height:30px; height:30px;"><label style="width:80px;float:left;display:inline;">Your Email:</label><input type="text" id="regUserName" style="width:160px; height:16px;display:inline;margin-top:5px;"/></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; line-height:30px; height:30px;margin-top:10px;"><label style="width:80px;float:left;display:inline;">Your Name:</label><input  type="text" id="regUser"  style="width:160px; height:16px;display:inline;margin-top:5px;" /></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; line-height:15px; height:15px;font-size:10px;font-weight:100;"><label style="width:225px;float:right; text-align:left;">4 characters min.</label></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; line-height:30px; height:30px;margin-top:10px;"><label style="width:80px;float:left;display:inline;">Password:</label><input  type="password" id="regPassWord"  style="width:160px; height:16px;display:inline;margin-top:5px;" /></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; line-height:15px; height:15px;font-size:10px;font-weight:100;"><div style="width:225px;float:right; text-align:left;">6 characters min.</div></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; line-height:30px; height:30px;margin-top:10px;"><label style="width:80px;float:left;display:inline;">retype&nbsp;PW:</label>';
	this.regW+='		  <input  type="password" id="confirmPwd"  style="width:160px; height:16px;display:inline;margin-top:5px;" /></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; line-height:20px; margin-top:10px;font-weight:normal;width:90%;margin:0px auto;font-size:11px;"><input type="checkbox" name="term_agree" id="term_agree" value="1" checked/>&nbsp;&nbsp;I agree to Brothersoft&#39s <a href="http://www.brothersoft.com/info/terms/" target="_blank" style="color:#0066cc;">Term of Use</a>.</li>';
	this.regW+='		<li  style="list-style:none; height:0px; line-height:0px;vertical-align:middle; text-align:center; font-weight:100;color:red;overflow:hidden; text-align:left;padding-left:20px;display:none"><div id="reg_err_show" style="line-height:20px;width:90%;"></div></li>';
	this.regW+='		<li style="list-style:none; line-height:40px; height:40px; vertical-align:middle; text-align:center;"><span style="width: 195px; float: right; font-weight: 100; font-size: 10px;  height: 40px; line-height: 40px;text-align:left;padding-left:30px;"><a href="http://www.brothersoft.com/info/privacy/" target="_blank" style="color:#0066cc;">Privacy Policy</a></span><input class="send" id="reg_send_btn" type="image" style=" margin-top:10px; margin-left:10px;" onclick="goReg();" src="http://img.brothersoft.com/v1/ppd/submit.gif"/></li>';
	this.regW+='		<li style="list-style:none; margin-left:15px; line-height:20px; height:20px;margin-top:1px;margin-bottom:10px;">If you are an author, <a href="http://author.brothersoft.com/?act=Register.reg&figure=author" style="text-decoration:underline; font-size:14px;cursor:pointer;color:#0066cc;font-weight:600;">Click here</a> to register.</li>';
	this.regW+='		</ul>';
	this.regW+='		<div id="reg_ok_div" style="display:none;margin-left:20px;width:270px;display:none;padding-top:15px; margin-bottom:15px;font-family:Arial; clear:both; font-size:12px;font-weight:normal;"></div>';
	this.regW+='	</div>';
	this.regW+='</div>';
	
	this.loadDiv=function(){
		var msie=/msie/.test(navigator.userAgent.toLowerCase());
		
		newD=document.createElement('div');
		newD.style.display='none';
		newD.id='loginDiv';
		newD.style.width='328px';
		newD.innerHTML=this.logW;
		if(msie){ document.body.insertBefore(newD, document.body.firstChild);}
		else{document.body.appendChild(newD);}
		//hd[0].appendChild(newD);
		
		pD=document.createElement('div');		
		pD.style.display='none';
		pD.id='bgDiv';
		pD.style.backgroundColor='#000000';
		pD.innerHTML='<iframe frameborder="0" style="border:0px; width:100%; height:100%;FILTER: progid:DXImageTransform.Microsoft.Alpha(style=0,opacity=0);">';
		if(msie){ document.body.insertBefore(pD, document.body.firstChild);}
		else{document.body.appendChild(pD);}
		//hd[0].appendChild(pD);
		
		newD=document.createElement('div');
		newD.style.display='none';
		newD.id='regDiv';
		newD.style.width='328px';
		newD.innerHTML=this.regW;
		if(msie){ document.body.insertBefore(newD, document.body.firstChild);}
		else{document.body.appendChild(newD);}
		//hd[0].appendChild(newD);
		
		
		}
	}
	var reg_flag='';
	var req;
	var regaj;
	var reg_ok_str='';
	var login_goto_log=false;
	var hidde_Div_log=false;
	function showLogin(str,flag)
	{
		if(typeof(flag)=='string' && flag=='top') {
			login_goto_log=true;
			}
		if(typeof(str)=='string' && str!='') {
			reg_flag=str;
			req=new F_Ajax('/user/?act=Top.openwin&flag='+reg_flag,function(){});
			}
		if(str=='reviews_rating') hidde_Div_log=true;
		else if(str!='' && typeof(str)=='string') hidde_Div_log=false;
		
		//alert(hidde_Div_log);
		if(document.getElementById('loginDiv')){F_openNewDiv('bgDiv','loginDiv');}else{
			login=new logWindows();login.loadDiv();
			setTimeout(function(){F_openNewDiv('bgDiv','loginDiv');},200);
		}
		
		
		return false;
	}
	function hideLogin()
	{toCloseDiv('bgDiv','loginDiv');}
	function showReg(str,email)
	{
		if(document.getElementById('regDiv')){F_openNewDiv('bgDiv','regDiv');}else{
			login=new logWindows();
			login.loadDiv();
			setTimeout(function(){F_openNewDiv('bgDiv','regDiv');},200);
		}
		if(typeof(str)=='string' && str!='') {
			reg_flag=str;
			req=new F_Ajax('/user/?act=Top.openwin&flag='+reg_flag,function(){});
		}
		if(str=='reviews_rating') hidde_Div_log=true;		
		else if(str!='') hidde_Div_log=false;
		
		//alert(hidde_Div_log);
		if(typeof(email)=='string' && email!=''){
			document.getElementById('regUserName').value=email;
			document.getElementById('reg_to_login_but').style.display='none';
		}
		
		return false;
	}
	function hideReg()
	{
		if(document.getElementById('reg_ok_ul').style.display=='none'){toCloseDiv('bgDiv','regDiv');if(!hidde_Div_log){window.location.reload();}}
		else{toCloseDiv('bgDiv','regDiv');}
	}
	
	
	function goLogin(){
		userName=document.getElementById('loginUserName');
		user_name=userName.value.Trim();
		userPassWord=document.getElementById('loginPassWord');
		user_remeber=document.getElementById('remember_login').checked?1:0;		
		if(userName.value.Trim()=='' || userPassWord.value.Trim()==''){
			scroll_show('log_err_show','Please input you username/password.');
			return false;}
		sendStr='email='+escape(userName.value.Trim())+'&password='+escape(userPassWord.value.Trim())+'&remember='+user_remeber;
		document.getElementById('log_AJ').style.display='';
		req=new F_Ajax('/user/?act=Top.do_login&'+sendStr,loginBack);
	}
	function loginBack(){
		if(req){
		if(req.ajax.readyState==4){
			if(req.ajax.status==200){
				document.getElementById('log_AJ').style.display='none';
				var response=req.ajax.responseText.split("@!@");
				if(response[0]=='1' || response[0]=='2'){
					isLog=0;
					hideLogin();
					
					if(login_goto_log && response[0]=='2'){
						window.location.href="http://author.brothersoft.com";
					}
					else{		
						logined_user_id=response[1];
						logined_user_name=response[2];
						if(!hidde_Div_log)	window.location.reload();
						//alert(hidde_Div_log);
					}
				}
				else{scroll_show('log_err_show',req.ajax.responseText);isLog=1;}
			}
			req=null;
		}}
		
	}
	
	function goReg(){
		userName=document.getElementById('regUserName');
		user_name=userName.value;
		userPassWord=document.getElementById('regPassWord');
		confirmP=document.getElementById('confirmPwd');
		member_name=document.getElementById('regUser');
		if(!document.getElementById('term_agree').checked){
				scroll_show('reg_err_show','You need to agree the term of use to continue registering');
				return false;
			}
		reg_ok_str='';
		reg_ok_str+="A confirmation e-mail has been sent to the following address:<br><br>";
		reg_ok_str+="<span style=\"font-weight:bold\">";
		reg_ok_str+=user_name;
		reg_ok_str+="</span>";
		reg_ok_str+="<br><br>Please confirm your Brothersoft account.<br>";
		if(!hidde_Div_log) var reg_reload="hideReg();window.location.reload();";
		else var reg_reload="hideReg();";
		reg_ok_str+='<div style="width:100%;text-align:center;height:22px;line-height:22px;"><a href="javascript:void(0)" onclick="'+reg_reload+'"><img src="http://img.brothersoft.com/ppd/images/reg_ok.gif" border="0" /></a></div>';
		if(userName.value.Trim()=='' || userPassWord.value.Trim()=='' || confirmP.value.Trim()=='' || member_name.value.Trim()==''){
			scroll_show('reg_err_show','Please input your registration information.');
			return false;}
		sendStr='user_name='+escape(userName.value.Trim())+'&password='+escape(userPassWord.value.Trim())+'&password_2='+escape(confirmP.value.Trim())+'&user='+escape(member_name.value.Trim())+'&flag='+escape(reg_flag);
		document.getElementById('reg_AJ').style.display='inline';
		document.getElementById('reg_send_btn').src = "http://img.brothersoft.com/v1/ppd/unsubmit.gif"; 
		document.getElementById('reg_send_btn').onclick=function(){return false;};
		regaj=new F_Ajax('/user/?act=Top.rego&'+sendStr,regBack);
	}
	function regBack(){
		if(regaj){
		if(regaj.ajax.readyState==4){
			if(regaj.ajax.status==200){
				document.getElementById('reg_send_btn').src = "http://img.brothersoft.com/v1/ppd/submit.gif"; 
				document.getElementById('reg_send_btn').onclick=function(){goReg();};
				document.getElementById('reg_AJ').style.display='none';
				var response=regaj.ajax.responseText.split("@!@");
				if(response[0]=='1' || response[0]=='2'){
					if(response[0]==1){
						logined_user_id=response[1];
						logined_user_name=response[2];
					}
					scroll_show2('reg_ok_ul','reg_ok_div',reg_ok_str);
					
				}else
				{scroll_show('reg_err_show',regaj.ajax.responseText);}
				regaj=null;
			}
		}}
		
	}
	
	
	function scroll_show(str,html){
		obj=document.getElementById(str);
		if(obj.innerHTML==html) return;
		obj.innerHTML=html;
		obj.parentNode.style.display='';
		obj.parentNode.style.height='0px';
		height=obj.offsetHeight;
		obj.parentNode.style.display='';
		show=setInterval(function(){if(parseInt(obj.parentNode.style.height)<parseInt(height)){ obj.parentNode.style.height=(parseInt(obj.parentNode.style.height)+2)+'px';} else clearInterval(show);},2);
		
		}
		
		function scroll_show2(str1,str2,html){
		obj1=document.getElementById(str1);
		obj2=document.getElementById(str2);
		obj_body=document.getElementById('reg_body');
		
		obj_body.style.height=parseInt(obj_body.offsetHeight)+'px';
		h1=obj1.offsetHeight;
		
		obj1.style.display='none';
		obj2.innerHTML=html;
		obj2.style.display='';
		h2=obj2.offsetHeight;
		
		len=Math.abs(h1-h2);
		
		
		show=setInterval(function(){if(len>0){obj_body.style.height=h1>h2?(parseInt(obj_body.style.height)-2)+'px':(parseInt(obj_body.style.height)+2)+'px';len-=2;} else clearInterval(show);},1);
		
		}
		
	function checkLogin(i)
	{
		req=new F_Ajax('/user/?act=Top.checkLogin',function(){checkBack.call(this,i)});
	}
	
	function checkBack(i){
		if(req.ajax.readyState==4){
			if(req.ajax.status==200){
				if(req.ajax.responseText=='0'){
					if(i==0) {document.getElementById('barDiv').innerHTML='<a  title="login" style="cursor:pointer" onclick="showLogin()">Login</a> | <a onclick="showReg()" style="cursor:pointer" title="Sign Up">Sign Up</a>';isLog=1;}
					else {isLog=1;}
				}else
				{if(i==0) {document.getElementById('barDiv').innerHTML='Dear '+req.ajax.responseText+'&nbsp;&nbsp;[<a style="cursor:pointer" onclick="logout()">logout</a>]';isLog=0;}
				else isLog=0}
			}
		}
	}
	
	function onkey(e,obj,i)
	{
		
		var key; 
		var key = window.event ? e.keyCode : e.which;
		
		if(obj.style.display!='none'){
			if(key==13){
				if(i==1) goLogin();
				else {
					if(document.getElementById('reg_ok_ul').style.display=='none'){hideReg();window.location.reload();}
					else{goReg();}
					}
			}
		}
	}
		
		if(document.getElementById('head_loading_img')){
			var login=new logWindows();
			login.loadDiv();
			obj=document.getElementById('head_loading_img');
			h="<strong class=\"cWhite\"><a style=\"cursor:pointer;color:#FFFFFF;\" onclick=\"showLogin('pageTop','top')\" rel=\"nofollow\" class=\"cWhite\">Log in</a></strong>&nbsp;|&nbsp;<strong class=\"cWhite\"><a style=\"cursor:pointer;color:#FFFFFF;\" onclick=\"showReg('pageTop')\" rel=\"nofollow\" class=\"cWhite\">Sign Up</a></strong>";
			obj.style.display='none';
			obj.parentNode.innerHTML+=h;			
			obj=null;
		}