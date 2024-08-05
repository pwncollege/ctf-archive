if(postwidgetnamespace)
{
   if (console && console.log)
   {
     console.log("The widget was initialized already. New script instance will not be loaded.");
   }
}
else
{

var postwidgetnamespace = {};

postwidgetnamespace.init = function(jsInit,options,divId)
{
	
var ra1ClassPrefix = "pw_";
var ra1ScriptNamePostfix = "post-widget.js";
//pixel log server
var pixelLogServer = "http://p.po.st/p"; 
//URL shortener
var urlShortenerServer= 'http://po.st/';
//Share widget server host
var poStHost = 'http://w.po.st';

var poStClassicServer = poStHost+'/share';
//poSt core server.
var poStCoreServer = poStClassicServer+'/entry';

//poSt CDN server.
var postCNDServer = 'http://i.po.st/share';

var successHtmlServer = "http://www.po.st/aftersharedisplay?pi=";
var excludedIds = ["25"];

var sharingServices = new Object();

var copyPasteLoading = false;


var pwjQ = null;

if (jsInit)
{
	loadCss();	    
	loadJQuery(function()
	{
		var publisherKey = getPublisherKey();
		loadMetadata(publisherKey,function()
		{
		   options["publisherKey"]= publisherKey;
	       PostWidget(options,divId);
		});

	},options);
}
else
{
	if (isOldIE())
	{
		if(document.attachEvent)
		{  
			// IE is different...    
			document.attachEvent("onreadystatechange", function()    
			{        
				if(document.readyState === "complete") 
				{           
					document.detachEvent("onreadystatechange", arguments.callee);
				  	loadCss();	    
				  	loadJQuery(function()
				  	{
					    Init();
				  	});					
					      
				}   
			});
		}
	}
	else
	{	
	  	loadCss();	    
	  	loadJQuery(function()
	  	{
		    Init();
	  	});
	}
}

function isOldIE()
{
	if (isIE7()) return true;
	if (navigator.appVersion.indexOf('MSIE 8.')>0) return true;
	return false;
}

function isIE7()
{
	return navigator.appVersion.indexOf('MSIE 7.')>0;
}


function logError(msg)
{
	if (console && console.log)
	{ 
		console.log(msg);
	}
}

function Init()
{
	var publisherKey = getPublisherKey();
	loadMetadata(publisherKey,function()
	{
		checkHtmlInit(publisherKey);
		pwjQ(document).ready(function()
		{
			var publisherKey = getPublisherKey();
			checkHtmlInit(publisherKey);
		});

	});
}




var googlePlusLoading = false;
function loadGooglePlus()
{
	
   if (!googlePlusLoading)
   {
      var scriptName = "https://apis.google.com/js/plusone.js";
      loadScript(scriptName);
      googlePlusLoading = true;
   }
}



function getRandom()
{
	 var d = new Date();
	 return d.getTime();	
}

var metadata = [{"name":"facebook","type":"sharerUrl","value":"http://www.facebook.com/share.php?src=bm&u=[URL]&t=[TITLE]&v=3","displayName":"Facebook","isFunction":false,"id":"1"},{"name":"twitter","type":"sharerUrl","value":"http://twitter.com/share?url=[URL]&text=[TITLE]","displayName":"Twitter","isFunction":false,"id":"2"},{"name":"email","type":"sharerUrl","value":"emailButtonClick","displayName":"Email","isFunction":true,"id":"3"},{"name":"print","type":"sharerUrl","value":"printButtonClick","displayName":"Print","isFunction":true,"id":"4"},{"name":"stumble_upon","type":"sharerUrl","value":"http://www.stumbleupon.com/submit?url=[URL]&title=[TITLE]","displayName":"StumbleUpon","isFunction":false,"id":"5"},{"name":"favorites","type":"sharerUrl","value":"favoritesButtonClick","displayName":"Favorites","isFunction":true,"id":"6"},{"name":"linkedin","type":"sharerUrl","value":"http://www.linkedin.com/shareArticle?mini=true&url=[URL]&title=[TITLE]","displayName":"LinkedIn","isFunction":false,"id":"7"},{"name":"google_bookmarks","type":"sharerUrl","value":"http://www.google.com/bookmarks/mark?op=edit&bkmk=[URL]&title=[TITLE]","displayName":"Google Bookmarks","isFunction":false,"id":"8"},{"name":"microsoft_messenger","type":"sharerUrl","value":"http://profile.live.com/badge?url=[URL]&title=[TITLE]","displayName":"MS Messenger","isFunction":false,"id":"9"},{"name":"myspace","type":"sharerUrl","value":"http://www.myspace.com/Modules/PostTo/Pages/?u=[URL]&t=[TITLE]","displayName":"MySpace","isFunction":false,"id":"10"},{"name":"delicious","type":"sharerUrl","value":"http://delicious.com/save?url=[URL]&title=[TITLE]","displayName":"Delicious","isFunction":false,"id":"11"},{"name":"digg","type":"sharerUrl","value":"http://digg.com/submit?phase=2&url=[URL]&title=[TITLE]","displayName":"Digg","isFunction":false,"id":"12"},{"name":"orkut","type":"sharerUrl","value":"http://promote.orkut.com/preview?nt=orkut.com&tt=[TITLE]&du=[URL]","displayName":"Orkut","isFunction":false,"id":"13"},{"name":"gmail","type":"sharerUrl","value":"http://mail.google.com/mail/?view=cm&fs=1&to&su=[TITLE]&body=[URL]&ui=2&tf=1","displayName":"GMail","isFunction":false,"id":"14"},{"name":"blogger","type":"sharerUrl","value":"http://www.blogger.com/blog_this.pyra?t&u=[URL]&n=[TITLE]&pli=1","displayName":"Blogger","isFunction":false,"id":"15"},{"name":"reddit","type":"sharerUrl","value":"http://reddit.com/submit?resubmit=true&url=[URL]&title=[TITLE]","displayName":"Reddit","isFunction":false,"id":"16"},{"name":"yahoo_mail","type":"sharerUrl","value":"http://compose.mail.yahoo.com/Subject=[TITLE]&body=[URL]","displayName":"Yahoo Mail","isFunction":false,"id":"17"},{"name":"tumblr","type":"sharerUrl","value":"http://www.tumblr.com/share?v=3&u=[URL]&t=[TITLE]&s=","displayName":"Tumblr","isFunction":false,"id":"18"},{"name":"hotmail","type":"sharerUrl","value":"http://www.hotmail.msn.com/secure/start?action=compose&to=&subject=[TITLE]&body=[URL]","displayName":"Hotmail","isFunction":false,"id":"19"},{"name":"aol_mail","type":"sharerUrl","value":"http://webmail.aol.com/25045/aol/en-us/Mail/compose-message.aspx?to=&subject=[TITLE]&body=[URL]","displayName":"AOL Mail","isFunction":false,"id":"20"},{"name":"live_journal","type":"sharerUrl","value":"http://www.livejournal.com/update.bml?subject=[TITLE]&event=[URL]","displayName":"LiveJournal","isFunction":false,"id":"21"},{"name":"posterous","type":"sharerUrl","value":"http://posterous.com/share?linkto=[URL]","displayName":"Posterous","isFunction":false,"id":"22"},{"name":"aol_lifestream","type":"sharerUrl","value":"http://lifestream.aol.com/share/?url=[URL]&title=[TITLE]&description=","displayName":"AOL LifeStream","isFunction":false,"id":"23"},{"name":"wordpress","type":"sharerUrl","value":"wordpressButtonClick","displayName":"WordPress","isFunction":true,"id":"24"},{"name":"google_buzz","type":"sharerUrl","value":"http://www.google.com/buzz/post?url=[URL]&title=[TITLE]","displayName":"Google Buzz","isFunction":false,"id":"25"},{"name":"vkontakte","type":"sharerUrl","value":"http://vkontakte.ru/share.php?url=[URL]&title=[TITLE]","displayName":"VKontakte","isFunction":false,"id":"26"},{"name":"baidu","type":"sharerUrl","value":"http://cang.baidu.com/do/add?it=[TITLE]&iu=[URL]&fr=ien&dc=","displayName":"Baidu","isFunction":false,"id":"27"},{"name":"mail.ru","type":"sharerUrl","value":"http://connect.mail.ru/share?url=[URL]&title=[TITLE]","displayName":"Mail.ru","isFunction":false,"id":"28"},{"name":"hyves","type":"sharerUrl","value":"http://www.hyves.net/profilemanage/add/tips/?name=[TITLE]&text=[URL]&type=12","displayName":"Hyves","isFunction":false,"id":"29"},{"name":"redirUrl","type":"redirUrl","isFunction":false,"id":""}];
function loadMetadata(publisherKey,callback)
{
	var data = metadata;
	pwjQ.each(data, function(key, val)
    {
		if (val.type == 'sharerUrl' && pwjQ.inArray(val.id, excludedIds) == -1)
			{
				sharingServices[val.name] = val;
			}
    });
	callback();	
}


function loadJQuery(callback)
{
	  if (options && options["jQuery"])
	   {
			 pwjQ = options["jQuery"];
			 pwjQ(document).ready(function()
			 {
                callback();
			 });
		    
	   }
	  else
	  {

	      var scriptName  = postCNDServer+'/script/jquery-1.7.min.js';	  
		  var onload = function() 
		  {	
       		   //IE 9 workaround for incorrect script loading sequence.
       		   if (jQuery().jquery.indexOf("1.7") == -1)
       		   {
       		      loadJQuery(callback);
       		      return;
       		   }	
 		   pwjQ = jQuery.noConflict(true);                          

		   callback();
		  };
		  loadScript(scriptName,onload);
	  }
}

function loadScript(scriptName,onload)
{
	  var body = document.getElementsByTagName('body')[0];
	  if (body == null)
	  {
		  logError("<body> tag is not found - trying to use head instead.");
		  body = document.getElementsByTagName('head')[0];
		  if (body == null)
		  {
			  logError("<head> tag is not found - widget could not be initialized.");		    
		  }
	  }
	  var script = document.createElement('script');
	  script.type = 'text/javascript';
	  script.src = scriptName;
          script.async = true;
	  
	  if (onload!=null)
	  {
		  if ("onreadystatechange" in script) 
		  {
			  script.onreadystatechange = function(e)
			  {
				  if (this.readyState == 'loaded' || this.readyState == 'complete')
				  {
//                                    alert("[state] Src:"+script.src);
				    onload(e);
				  }
			  };
		  }
		  else
		  {
//                     alert("[onload] Src:"+script.src);
		     script.onload = onload;
		  }
	  }
	  body.appendChild(script);
}
  
function serverRequest(requestString, onComplete) {
	
	var request = pwjQ.ajax({
		type: 'GET',
		contentType: "application/json;charset=UTF-8",
		dataType: "jsonp",
		url: requestString+'&random='+getRandom(),
		success:function(data){onComplete(data);}

	});
}	


function getElementsByClass (classList, node) 
{			
	var node = node || document,
	list = node.getElementsByTagName('*'), 
	length = list.length,  
	classArray = classList.split(/\s+/), 
	classes = classArray.length, 
	result = [], i,j;
	for(i = 0; i < length; i++) {
		for(j = 0; j < classes; j++)  {
			if (list[i].className && list[i].className.search )
			{	
				if(list[i].className.search('\\b' + classArray[j] + '\\b') != -1) {
					result.push(list[i]);
					break;
				}
			}
		}
	}

	return result;
};
	
function startsWithPrefix(element,prefix)
{
	if (!element || !element.className)
	{
		return false;
	}
	
	if (prefix == null)
	{
		prefix = ra1ClassPrefix;
	}
	return (element.className.indexOf(prefix)>=0);
}
// HTML initializer.
function checkHtmlInit(publisherKey)
{
	var widgetDivs = getElementsByClass("pw_widget",document);
	if (widgetDivs.length == 0)
	{
		return;
	}	
    for (var i = 0;i<widgetDivs.length;i++)
    {
    	var nextDiv = widgetDivs[i];
    	if (nextDiv['pwDivInitialized'])
		{
		   continue;
		}
    	nextDiv['pwDivInitialized'] = true;
    	var divId = "ra1-pw-widget-"+(i+1);    	
    	
    	nextDiv.setAttribute("id",divId);
    	if (startsWithPrefix(nextDiv,ra1ClassPrefix+"widget"))
    	{
        	var divClasses = nextDiv.className.split(" ");
        	
        	
        	var options = setupOptions(options,nextDiv);
        	options.publisherKey= publisherKey;
        	
        	for (var j = 0;j<divClasses.length;j++)
        	{
        		var nextClass = divClasses[j];
        		if (nextClass.indexOf(ra1ClassPrefix) == 0)
        		{
                    var propertyString = nextClass.substring(ra1ClassPrefix.length);
                    
                    var chunks = propertyString.split("_");
                    var propertyName = chunks[0];
                    var propertyValue = null;
                    if (chunks.length>1)
                    {
                    	propertyValue = chunks[1];
                    }
                    eval("options."+propertyName+"="+propertyValue);
        		}
        	}
        	/*
        	var scripts = nextDiv.getElementsByTagName("script");
        	if (scripts.length == 1)
            {
        		var scriptContent = scripts[0].text;
        		try {
        			eval(scriptContent);
            		if (pw_url)
        			{
        			   options["url"]=pw_url;
        			   pw_url == null;
        			}
            		if (pw_title)
        			{
        			   options["title"]=pw_title;
        			   pw_title == null;
        			}
        		}catch (e)
        		{
        			logError("Error evaluating init script:"+scriptContent);
        		}
            }*/
        	nextDiv.innerHTML = "";
        	PostWidget(options,divId);
    	}
    	
    }	
}

function getPublisherKey()
{
	var availableScripts = document.getElementsByTagName('script');
	for (var i = 0;i<availableScripts.length;i++)
	{
		var scriptSrc = availableScripts[i].src;
		if (scriptSrc.indexOf(ra1ScriptNamePostfix)>0)
		{
			var chunks = scriptSrc.split("#publisherKey=");
			if (chunks.length == 2)
			{
				var result = chunks[1];
				return result;
			}
		}
	}
}


function setupOptions(options,parentDiv)
{
	var options = new Object();
	var buttons = [];
	var buttonNodes = parentDiv.getElementsByTagName('a');
	for (var i = 0;i < buttonNodes.length;i++)
	{
		var nextNode = buttonNodes[i];
		var className = nextNode.className;
		if (startsWithPrefix(nextNode,ra1ClassPrefix))
		{
			var actionName = className.substring(ra1ClassPrefix.length);
			if (actionName == 'url')
			{
				options.url = nextNode.innerHTML;
			}
			else if (actionName == 'title')
			{
				options.title = nextNode.innerHTML;
			}
			else
			{			
			  buttons.push(actionName);
			}
		}
	    nextNode.style.display='none'; 
	}	
	options.buttons = buttons;
	return options;
}


function loadCss()
{
  	  var head = document.getElementsByTagName('head')[0];
	  var script = document.createElement('link');
	  script.rel = 'stylesheet';
	  script.href = postCNDServer+'/style/poSt-classic.css';
	  script.media = 'screen';
	  script.type = 'text/css';
	  head.appendChild(script);
}


//END HTML INIT
function PostWidget(initOptions,dId)
{
var isHorizontal = true;
// poSt click-back URL wrapping template.
var poStClickBackUrlTemplate = poStCoreServer+'/redir?publisherKey=[PUBLISHER_KEY]&url=[URL]&title=[TITLE]&sharer=[SHARER]';//&vGUID=[vGUID]&cGUID=[cGUID]'; 


var KEYCODE_ESC = 27; 


var fullShareUrl = '';
var shareUrl = '';
var shareTitle = '';
var queryString = '';

var publisherKey;
var sizeAspect = 16;
var moreSizeAspect = 16;

//GUIDS
var vGUID = null;
var cGUID = null;


var initialButtons;

var poSt_UUID_COOKIE = 'post_uuid';
var poSt_OPTOUT_COOKIE = 'post_optout';
var uuidCookie;
var isOptout;

var copyPasteActive = false;
var counterActive = false;

ShareWidgetInit(initOptions,dId);
/*
 * Temporary guid generator 
 */
function S4() 
{
	return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
}


function guid() 
{
   return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}

/*
 * poSt logging functions.
 * 
 */

function sanitizeString(str)
{
	try
	{
		str = str.replace('\n', '', 'g');
		str = str.replace('\t', '', 'g');	
		str = str.replace('\r', '', 'g');
	} catch(e){}
	return str;
}
function createCommonLogUrlPart(eventType)
{
	var result = pixelLogServer//+'#'+getRandom()
	+'?t='+eventType
	+'&pub='+encodeURIComponent(publisherKey)
	+'&pu='+encodeURIComponent(shareUrl)
	+'&random='+getRandom();
	if (!isOptout)
	{
		result = result+'&pt='+encodeURIComponent(sanitizeString(shareTitle))
		+'&pq='+encodeURIComponent(queryString)
		+'&vGUID='+vGUID;
	}
	return result;
}



//View event.
function createViewLogUrl()
{
	var eventType = 'view';
	vGUID = guid();
	return createCommonLogUrlPart(eventType);
}

function createClickLogUrl(sharer)
{
	var eventType = 'click';	
	cGUID = guid();
	var result = createCommonLogUrlPart(eventType);
	result = result+'&clicked='+sharer.name;
	if (!isOptout)
	{
	  result=result+'&cGUID='+cGUID;
	}
	return result;
}

function createShareLogUrl(sharer)
{
	var eventType = 'share';
	var sGUID = guid();
	var result = createCommonLogUrlPart(eventType);
	sharerName = sharer.name;
	result = result+'&sm=classic&sc1='+sharerName;
	
	if (!isOptout)
	{
	   result = result+'&su1=&cGUID='+cGUID+'&sGUID='+sGUID;
	}
	return result;
}
function createCopyLinkLogUrl()
{
	var eventType = 'copylink';
	var result = createCommonLogUrlPart(eventType);
	if (!isOptout)
	{
	   var lGUID = guid();
	   result = result+'&lGUID='+lGUID+'&cGUID='+cGUID;
	}
	return result;
}

function createMoreLogUrl()
{
	
	var eventType = 'more';
	var result = createCommonLogUrlPart(eventType);
	var moreLabel = 'Wishlist';
	result = result + '&label='+moreLabel;
	if (!isOptout)
	{
	  var mGUID = guid();	
	  result = result+'&mGUID='+mGUID+'&cGUID='+cGUID;
	}
	return result;
}

function emitLogPixel(logUrl)
{		
	var logImage = pwjQ('<img class="ra1-pw-logImage"></img>');
	logImage.attr('src',logUrl);
}




//var divId;
function readCookies()
{
	uuidCookie = getCookie(poSt_UUID_COOKIE);
	isOptout = ("1" == getCookie(poSt_OPTOUT_COOKIE));
}

function isSharer(obj)
{
	return obj.id && obj.name && obj.displayName;
}


function ShareWidgetInit(initOptions,dId) {		
	if (initOptions['publisherKey'])
	{	  
		var divId = '#classicWidget';
		if (dId != null)
		{
		  divId = '#'+dId;
		}
		
		publisherKey = initOptions['publisherKey'];
		
		//setup option parameters
		//counter 
		counterActive = initOptions['counter'];
		
		//initial buttons
		initialButtons = initOptions['buttons'];
		if (initialButtons == null)
		{
			initialButtons = ["facebook","twitter","email"];
		}
		
		//copy-paste
		copyPasteActive = initOptions['copypaste'];
		
		sizeAspect = initOptions['size'];
		if (sizeAspect == null)
		{
			sizeAspect = 16;
		}
		
		//title
	    shareTitle = initOptions['title']; 
		if (shareTitle == null) 
		{ 
			shareTitle = document.title;		
		}

		//url
		shareUrl = initOptions['url'];		
		if (shareUrl == null)
		{
			 shareUrl = document.location.href;
		}
		
		fullShareUrl = shareUrl;

		if (shareUrl!=null)
		{
			var queryStringIndex = shareUrl.indexOf('?');
			if (queryStringIndex > -1)
			{
				var startQueryIndex = queryStringIndex+1;
				if (startQueryIndex< shareUrl.length)
				{
				  queryString = shareUrl.substring(startQueryIndex);
				}
				shareUrl = shareUrl.substring(0, queryStringIndex);				
			}
		}
   	    initialize(divId);		
		return;
	}
		 	
	logError('PoSt Error: No publisher key detected.');
}


function addPopupSharerButton(buttonDiv,sharer,index)
{
	
    var buttonCell = pwjQ('<div class="ra1-pw-moreButtonDiv"></div>').css('height',moreSizeAspect+'px');    
	var shareButton = createSpriteButton(sharer.name,moreSizeAspect,sharer.displayName,sharer.id);
	
    var buttonSpan = pwjQ('<div class="ra1-pw-sharerNameText">'+sharer.displayName+'</div>');
    //buttonSpan.css('top',(2-moreSizeAspect/2)+'px');

    buttonCell.append(shareButton);
    
    buttonCell.append(buttonSpan);    
        	    
    addClickListener(buttonCell,sharer);

    buttonDiv.append(buttonCell);
    return buttonCell;
}

//Sharing logic happen here.
function addClickListener(buttonCell,sharer)
{
    var handler = null;
	  if (sharer.isFunction)
	  {
		  handler = function()
		  {
			   var functionPrefix= sharer.value;
			   var fName  = functionPrefix+"(sharer)";
		       eval(fName);
		  };
		  
	  }
	  else
	  {
	     handler = function(sharer)
	     {
	        shareClickHandler(sharer,buttonCell);
	     };
	  }
    buttonCell.click(function()
    {
        if (addMoreMoreOpened)
    	{
         	moreMoreSuccess = true;
         	if (sharer.name!='email')
         	{
              loadMoreSuccessHtml(addMoreMorePopup,'s');
         	  switchView("#moreShareDiv","#moreSuccesDiv",addMoreMorePopup);
         	}
    	}

  	 	 updateCounterValue();
	  	 emitLogPixel(createClickLogUrl(sharer));
	  	 if (sharer.name!='email')
	  	 {
	  	   emitLogPixel(createShareLogUrl(sharer));
	  	 }
         handler(sharer);
        if (addMoreOpened)
      	{
         	if (sharer.name!='email')
         	{
              loadMoreSuccessHtml(addMorePopup,'s');		
          	  switchView("#moreShareDiv","#moreSuccesDiv",addMorePopup);
         	}
      	}
	  });	
}
function createStandaloneButton(buttonDiv,background)
{
	var standaloneIcon  = createSpriteButton('poSt',sizeAspect,'Share...',33);
	standaloneIcon.addClass('ra1-pw-standalone');
		
	var standaloneButton = pwjQ('<a href="javascript:void(0)" class="ra1-pw-sharealone-brd"></a>')
	.css("height",sizeAspect+"px").css("width",(sizeAspect+50)+"px");
	if (background)
	{
		standaloneButton.addClass("ra1-pw-sharealone-brd-bg");
	}
	
	standaloneButton.append(standaloneIcon);
	var textSpan = pwjQ('<span class="ra1-pw-sharealone-txt">Share</span>');
	textSpan.addClass('ra1-pw-sharealone-txt-'+sizeAspect);
	standaloneButton.append(textSpan);
	standaloneButton.mouseenter(function(e)
	{
	//	emitLogPixel(createMoreLogUrl()); - should we log this ?
        if (!addMoreOpened)
        {
		   showMoreMorePopup(e,buttonDiv);
        }
	});
	setCloseListener(standaloneButton);	
	buttonDiv.append(standaloneButton);
	buttonDiv.width(buttonDiv.width()+standaloneButton.width()-(sizeAspect+4)*2);
}

function setUpButtons(buttonDiv)
{
 	  var shareButtonPresent = false;
	  for (var sharerIdIndex in initialButtons)
	  {
		  
		    var sharerId = initialButtons[sharerIdIndex];
		    if ("googleplus" == sharerId)
	    	{
		  	  setupGooglePlus(buttonDiv);
		  	  continue;
	    	}
		    if ("share" == sharerId)
	    	{
		   	  createStandaloneButton(buttonDiv,true);
		   	  shareButtonPresent = true;
		  	  continue;
	    	}
		    if ("share_transparent" == sharerId)
	    	{
		   	  createStandaloneButton(buttonDiv,false);
		   	  shareButtonPresent = true;
		  	  continue;
	    	}
		    var sharer = sharingServices[sharerId];
		    if (sharer == null)
	    	{
	    	  continue;
	    	}
		    
		    var shareButton = createShareButton(sharer,sizeAspect);
		    buttonDiv.append(shareButton);
	  }
      	  
	  if (!shareButtonPresent)
      {
		  var moreButton = createMoreMoreButton(buttonDiv);
		  buttonDiv.append(moreButton);		 		  
      }

	  
	  if (counterActive)
      {
		  var counterDiv = pwjQ("<div></div>");
		  
		  var counterArrow = pwjQ('<i class="ra1-pw-counterContainer-arrow"></i>');
		  counterArrow.addClass("ra1-pw-counterContainer-arrow-"+sizeAspect);
		  counterDiv.append(counterArrow);
		  
		  counter = createCounter();
		  counterDiv.append(counter);
		  buttonDiv.append(counterDiv);
		  updateCounterValue(true);
      }
}
var counter; 


function createCounter()
{
	var result = null;
    result = pwjQ('<a class="ra1-pw-counterContainer"></a>');
    result.addClass("ra1-pw-counterContainer-"+sizeAspect);
    result.css('height',(sizeAspect-2)+'px');
    return result;
}


function updateCounterValue(noIncrement)
{
	if (!counterActive) return;
	serverRequest(poStCoreServer+'/counterValue?publisherKey='+publisherKey+'&sharer=all&url='+encodeURIComponent(fullShareUrl)+'&noincrement='+noIncrement+"&random="+getRandom(), function(data){
		setCounterValue(data[0].value+'');	
	});
}

var moreSuccessHtml = null;
function loadMoreSuccessHtml(popup,size)
{
   if (moreSuccessHtml)
   {
	      popup.find('#sharingSuccessDiv').html(moreSuccessHtml);
	      return;
   }
   serverRequest(successHtmlServer+encodeURIComponent(publisherKey)+"&si="+size,function(result)
   {
	  moreSuccessHtml = result; 
      popup.find('#sharingSuccessDiv').html(result);
   });
}


var digitLetter = ['','K','M','B','T','Q'];
function setCounterValue(value)
{
	var digit = Math.floor((value.length-1) / 3);
	var shortenedValue = value.substring(0,value.length-digit*3+1);
	var floatValue = (value/Math.pow(1000,digit));
	if (value>1000)
	{
		floatValue = floatValue.toFixed(1);
	}
    var displayValue = floatValue+digitLetter[digit];    
	counter.html(displayValue);
    
}

function createShareButton(sharer,size)
{
	var shareButton = createMiscButton(sharer.name,size,sharer.displayName,sharer.id);
	addClickListener(shareButton,sharer);	
	return shareButton;
	
}

function createMiscButton(buttonName,size,tooltip,index)
{
	return createSpriteButton(buttonName,size,tooltip,index);
}

function createSpriteButton(buttonName,size,tooltip,index)
{
	var miscButton = pwjQ('<div class="ra1-pw-buttonSprite-'+size+'" ></div>');
	var spriteGap = 0;
	if (size == 16)
	{
		spriteGap = 2*size*index;	
	}
	if (size == 24)
	{
		spriteGap = (size+8)*index;	
	}
	if (size == 32)
	{
		spriteGap = size*index;	
	}
	miscButton.css('background-position','0px -'+spriteGap+'px');
	if (tooltip)
	{
		miscButton.attr('alt',tooltip);
		miscButton.attr('title',tooltip);
	}
	return miscButton;	
}

function createMoreMoreButton(buttonDiv)
{	
	var moreButton = createMiscButton('poSt',sizeAspect,'',33);
	moreButton.mouseenter(function(e)
	{
	//	emitLogPixel(createMoreLogUrl()); - should we log this ?
        if (!addMoreOpened)
        {
		   showMoreMorePopup(e,buttonDiv);
        }
	});
	setCloseListener(moreButton);	
	return moreButton;
}
function createMoreButton(buttonDiv)
{
    var buttonCell = pwjQ('<div class="ra1-pw-moreButtonDiv"></div>').css('height',moreSizeAspect+'px');    
	var shareButton = createSpriteButton('poSt',16,'More...',0);
	shareButton.addClass('moreIconButton');
	
    var buttonSpan = pwjQ('<div class="ra1-pw-sharerNameText">More</div>');
    buttonCell.append(shareButton);
    buttonCell.append(buttonSpan);    
    buttonCell.mouseenter(function()
	{
    	buttonCell.addClass('ra1-pw-selectedButton');
	});
    buttonCell.mouseleave(function()
	{
    	buttonCell.removeClass('ra1-pw-selectedButton');
	});    
    buttonCell.click(function(e)
		{
    	   //emitLogPixel(createMoreLogUrl());
    	   hideAddMoreMore();
    	   showMorePopup(e,buttonDiv);
		}
    );
    buttonDiv.append(buttonCell);    
    
    return buttonCell;
}



function emailButtonClick(sharer)
{
	showEmailWindow();	
}
function printButtonClick(sharer)
{
	if (!addMoreOpened && !addMoreMoreOpened)
    {
		window.print();
    }
	hideAddMore(true);
	hideAddMoreMore(true);
}


function favoritesButtonClick(sharer)
{
	if (pwjQ.browser.webkit)
	{
		alert("Please press Ctrl+D to bookmark this page.");
		return;
	}

	
	if (window.sidebar)
	{
		window.sidebar.addPanel(shareTitle, fullShareUrl, "");
	}
	else
	if (window.external)
	{
		window.external.AddFavorite(fullShareUrl, shareTitle);
	}
    else if(window.opera && window.print) 
    { // Opera Hotlist   
		alert("Opera - Please press Ctrl+D to bookmark this page.");
    	/*
        var elem = document.createElement('a'); 
        elem.setAttribute('href',fullShareUrl); 
        elem.setAttribute('title',shareTitle); 
        elem.setAttribute('rel','sidebar'); 
        elem.click();
        */
        
    }
}
function wordpressButtonClick(sharer)
{
   var blogName = prompt("Please enter your WordPress Blog URL:","http://");

   if (blogName!=null)
   {
	   var clickBackUrl = getClickBackUrl(sharer.name);
	   
	   shortenUrl(clickBackUrl,function(shortenedUrl)
	   {		   
		   var shareUrl = blogName+"/wp-admin/press-this.php?u="+encodeURIComponent(shortenedUrl)+"&t="+encodeURIComponent(shareTitle);		   
		    var wnd = window.open(shareUrl);
		    if (wnd == null)
		    {
		    	alert("Please disable pop-up blocker to share at WordPress.");
		    	return;
		    }
	   });
   
   }
}


function getClickBackUrl(sharerName)
{
	var poStClickBackUrl = poStClickBackUrlTemplate;	
	poStClickBackUrl = poStClickBackUrl.replace('[PUBLISHER_KEY]',publisherKey);
	poStClickBackUrl = poStClickBackUrl.replace('[URL]',encodeURIComponent(fullShareUrl));
	poStClickBackUrl = poStClickBackUrl.replace('[TITLE]',encodeURIComponent(shareTitle));
	poStClickBackUrl = poStClickBackUrl.replace('[SHARER]',sharerName);
	//poStClickBackUrl = poStClickBackUrl.replace('[vGUID]',vGUID);
	//poStClickBackUrl = poStClickBackUrl.replace('[cGUID]',cGUID);
	return poStClickBackUrl;
}

//'redir?url=[URL]&title=[TITLE]&sharer=[SHARER]&vGUID=[vGUID]&cGUID=[cGUID]'; 
function shareClickHandler(sharer,shareButton)
{
	var sharerName = sharer.name;
	var sharerUrl = sharer.value;
	var url = postCNDServer+"/share.html?publisherKey="+publisherKey
	+"&url="+encodeURIComponent(fullShareUrl)
	+"&title="+encodeURIComponent(shareTitle)
	+"&sharer="+encodeURIComponent(sharerName)
	+"&displayName="+encodeURIComponent(sharer.displayName)
	+"&sharerUrl="+encodeURIComponent(sharerUrl)
	+"&cGUID="+cGUID
	+"&vGUID="+vGUID;
	var wnd = window.open(url);
	if (wnd == null)
	{
	    	alert("Please disable pop-up blocker to share.");
	    	return;
	}
}


function shortenUrl(url,callback)
{
	var request = pwjQ.ajax({
		type: 'GET',
		dataType: "jsonp",
		url: urlShortenerServer+'s?url='+encodeURIComponent(url),
		success:function(data)
		{
			var result = urlShortenerServer+data.urlKey;
			callback(result); 
		}

		
	});
}


var shareContainer;
var mailDelimeter = "&";

function showEmailWindow()
{
	hideAddMore();
	hideAddMoreMore();

	if (shareContainer == null)
	{
		shareContainer = document.createElement ('div');
		shareContainer.id="ra1-pw-share-container";
		document.body.appendChild(shareContainer);
				
		pwjQ('#ra1-pw-share-container').append("<iframe id='share-widget-iframe' name='share-widget-iframe'/>");
		
		if (pwjQ('#ra1-pw-closeDiv').length<=0)
		{
			var closeDiv = pwjQ('<div id="ra1-pw-closeDiv"></div>');
			pwjQ('#ra1-pw-share-container').append(closeDiv);		
			closeDiv.click(function()
		    {
				deactivateShareBox(); 
			});
		}
		
	}
	pwjQ('#ra1-pw-share-container').css('width',pwjQ(window).width()+"px").css('height',pwjQ(window).height()+"px");
	
	
	var shareBoxIframe = pwjQ('<iframe  />', {
		scrolling: 'no',
		width:'0px',
		height:'0px',
		
		align:'middle',
		src: poStClassicServer+'/classic-email.html#'+encodeURIComponent(publisherKey)+mailDelimeter+encodeURIComponent(fullShareUrl)+mailDelimeter+encodeURIComponent(shareTitle)+mailDelimeter+vGUID+mailDelimeter+cGUID+mailDelimeter+encodeURIComponent(queryString),
	    id:   'share-widget-iframe',
		name: 'share-widget-iframe',
		frameBorder:'0'				    
	});	
	
	shareBoxIframe.load(function()
	{ 
	   shareBoxIframe.css('height', '425px');		
	   shareBoxIframe.css('width', '495px');
	   centerStuff();		
	 }); 

	pwjQ(window).resize(function()
	{
		centerStuff();
	});
	pwjQ(window).scroll(function()
	{
		centerStuff();
	});
	
   centerStuff();	

	pwjQ('#ra1-pw-share-container iframe').replaceWith(shareBoxIframe);
	pwjQ('#ra1-pw-share-container').fadeIn(50, function()
	{
			pwjQ('body').bind('click', deactivateShareBox);
	});
	
}

function centerStuff()
{
	pwjQ('#ra1-pw-share-container').css('width',pwjQ(document).width()).css('height',pwjQ(document).height());
	var iframe = pwjQ('#ra1-pw-share-container iframe');
	center(iframe); 	
	var closeDiv = pwjQ("#ra1-pw-closeDiv");
	var offset = iframe.offset();
			
	closeDiv.css('top',offset.top+"px");
	closeDiv.css('left',(offset.left+440)+"px");
}
function deactivateShareBox()
{	
	//Close Share Widget
	pwjQ('#ra1-pw-share-container').fadeOut();
	pwjQ('body').unbind('click');	
	pwjQ(window).unbind('resize');
}

var addMorePopup = null;
var addMoreOpened = false;

var addMoreMorePopup = null;
var addMoreMoreOpened = false;

function center(obj) { 
    obj.css("_position","absolute"); 
    obj.css("position","fixed"); 
    obj.css("top", ((pwjQ(window).height() - obj.outerHeight()) / 2) + "px"); 
    obj.css("left", ((pwjQ(window).width() - obj.outerWidth()) / 2) +  "px"); 
} 

function showMorePopup(e,buttonDiv)
{
	if (addMoreOpened)
	{
		return;
	}
	
	if (addMorePopup == null)
	{
		createMorePopup(buttonDiv);
		pwjQ(document).keyup(function(e) 
		{ 
		  if (e.keyCode == KEYCODE_ESC) 
		   { 
				hideAddMore();
		   }  
	    });		
	}
	center(addMorePopup);
	switchView("#moreSuccesDiv","#moreShareDiv",addMorePopup);	
	addMorePopup.fadeIn(50, function()
	{
		pwjQ(document).bind('click',hideAddMoreHandler);
	});

	addMoreOpened = true;
}

function showMoreMorePopup(e,buttonDiv)
{
	if (addMoreMoreOpened)
	{
		return;
	}
	
	if (addMoreMorePopup == null)
	{
		createMoreMorePopup(buttonDiv,10);
		pwjQ(document).keyup(function(e) 
		{ 
		  if (e.keyCode == KEYCODE_ESC) 
		   { 
				hideAddMore();
		   }  
	    });		
	}
	var popupTargetTop = 0; 
	var popupTargetLeft =0;
	var screenX = e.pageX;
	var screenY = e.pageY;
	
	var windowHeight = pwjQ(window).height();
	var windowWidth = pwjQ(window).width();
	
	var popupHeight = addMoreMorePopup.height(); 
	var popupWidth = addMoreMorePopup.width();

	popupTargetTop =  (e.currentTarget.offsetTop + sizeAspect+2);
	popupTargetLeft = e.currentTarget.offsetLeft;
	var clipUp = screenY - pwjQ(window).scrollTop() + popupHeight + 20 > windowHeight && windowHeight>300; 
	if (clipUp)
	{
		popupTargetTop = -(popupHeight+10);
	}
	if (screenX - pwjQ(window).scrollLeft() + popupWidth + 20 > windowWidth)
	{
		popupTargetLeft = buttonDiv.width()-popupWidth;
		popupTargetLeft-=counterActive ?72:10;
		if (clipUp)
		{
		   popupTargetLeft-=10;
		}
	}
	
	popupTargetTop = buttonDiv.offset().top+popupTargetTop;
	popupTargetLeft = buttonDiv.offset().left+popupTargetLeft;
	addMoreMorePopup.css('top',popupTargetTop+'px');
	addMoreMorePopup.css('left',popupTargetLeft+'px');

	switchView("#moreSuccesDiv","#moreShareDiv",addMoreMorePopup);
	moreMoreSuccess = false;

	addMoreMorePopup.fadeIn(50);	
	addMoreMoreOpened = true;
}

function hidePopup(popup,print)
{
  popup.fadeOut(50,function()
  {
	  if (print)
	  {
		  window.print();
	  }
  });	
}

function hideAddMoreHandler(e)
{
	if (!e || !e.target)
	{
    	hideAddMore(false);
    	return;
	}
		
	var targetClass = pwjQ(e.target).closest('.ra1-pw-addMorePopup');
	if (targetClass.length == 0)
	{
    	hideAddMore(false);
    	return;
	}
}

function hideAddMore(print)
{  
   if(addMoreOpened)
   { 
	  hidePopup(addMorePopup,print);
	  addMoreOpened = false;
	  pwjQ(document).unbind('click',hideAddMoreHandler);
	  addMorePopup.find('#sharingSuccessDiv').html('');
   }
}

function hideAddMoreMore(print)
{  
   if(addMoreMoreOpened)
   {
	  hidePopup(addMoreMorePopup,print);
	  addMoreMorePopup.find('#sharingSuccessDiv').html('');
	  addMoreMoreOpened = false;	  
   }
}

function switchView(viewOne,viewTwo,parent)
{
	pwjQ(viewOne,parent).hide();
	pwjQ(viewTwo,parent).show();
}



function createMorePopup(buttonDiv)
{	
	  addMorePopup = pwjQ('<div class="ra1-pw-addMorePopup" ></div>');
	  if (isIE7())
	  {  
	     addMorePopup.css("width","300px");
	  }
	  //success
	  var successDiv = pwjQ('<div id="moreSuccesDiv" class="ra1-pw-addMorePopup-i" style="display:none"></div>');
	  addMorePopup.append(successDiv);
	  successDiv.append('<div class="ra1-pw-popup-title" >Sharing successfull!<span id="successClose" class="ra1-pw-popup-close" ></span></div>');	  
	  pwjQ("#successClose",addMorePopup).click(function()
	  {
		hideAddMore();  
	  });
	  
	  successDiv.append("<div class='ra1-pw-addMorePopup-text' >You've successfully shared using Po.st <span id='ra1-pw-shareAgainButton' class='ra1-pw-small-btn'><span><span>Share again!</span></span></span><br><div id='sharingSuccessDiv' class='ra1-pw-success-more'>Loading content...</div></div>");
	  successDiv.append('<div class="ra1-pw-popup-footer"><a href="http://www.po.st" rel="nofollow" target="_blank" class="ra1-pw-powered">Powered by Po.st</a><a href="http://www.po.st/privacy.html" rel="nofollow" class="ra1-pw-privacy" target="_blank">Privacy policy</a></div>');
	  
	  pwjQ('#ra1-pw-shareAgainButton',successDiv).click(function()
	  {
		  switchView("#moreSuccesDiv","#moreShareDiv",addMorePopup);  
	  });
	  
	  //buttons
	  var addMorePopupIn = pwjQ('<div id="moreShareDiv" class="ra1-pw-addMorePopup-i" ></div>');
	  addMorePopup.append(addMorePopupIn);
	  
	  
	  var headerDiv = pwjQ('<div class="ra1-pw-popup-title" >Share with friends</div>');
	  
	  var closeButton = pwjQ('<span class="ra1-pw-popup-close" ></span>');
	  closeButton.click(function()
	  {
	     hideAddMore();
	  });
	  headerDiv.append(closeButton);
	  addMorePopupIn.append(headerDiv);
	  	  
	  var buttonsDiv =pwjQ('<div class="ra1-pw-moreButtonsDiv"></id>');	  
	  addMorePopupIn.append(buttonsDiv);

	  
	  var firstColumn = pwjQ('<div class="ra1-pw-morePopupColumn" />');
	  buttonsDiv.append(firstColumn);
	  var secondColumn = pwjQ('<div class="ra1-pw-morePopupColumn" />');
	  buttonsDiv.append(secondColumn);	  
	  var index = 0;
	  for (var sharerId in sharingServices)
	  {		  
		  var sharer = sharingServices[sharerId];
		  if (!isSharer(sharer))
		  {
			  continue;
		  }
		  var currentColumn =(index % 2 == 0)? firstColumn:secondColumn;
		  addPopupSharerButton(currentColumn,sharer,index);
		  index = index + 1;
	  }	  
	  addMorePopupIn.append('<div class="ra1-pw-popup-footer"><a href="http://www.po.st" rel="nofollow" target="_blank" class="ra1-pw-powered">Powered by Po.st</a><a href="http://www.po.st/privacy.html" rel="nofollow" class="ra1-pw-privacy" target="_blank">Privacy policy</a></div>');
	  pwjQ('body').append(addMorePopup);
}

var timer;
var moreMoreSuccess = false;


function setCloseListener(element)
{
	if (moreMoreSuccess) return;
	element.mouseleave(function()
	{
		if (timer != null)
		{
			clearTimeout(timer);
		}
        timer = setTimeout(function()
		{
        	if (!moreMoreSuccess)
    		{
	          hideAddMoreMore();
    		}
		},1000);

	});
	
	element.mouseenter(function()
	{
		if (timer!=null)
		{
			clearTimeout(timer);
		}
	});
}

function createMoreMorePopup(buttonDiv,buttonsCount)
{	
	  addMoreMorePopup = pwjQ('<div class="ra1-pw-addMorePopup"></div>');
	  if (isIE7())
	  {  
	     addMoreMorePopup.css("width","300px");
	  }

	  //success
	  var successDiv = pwjQ('<div id="moreSuccesDiv" class="ra1-pw-addMorePopup-i" style="display:none"></div>');
	  addMoreMorePopup.append(successDiv);
	  successDiv.append('<div class="ra1-pw-popup-title" >Sharing successfull!<span id="successClose" class="ra1-pw-popup-close" ></span></div>');	  
	  pwjQ("#successClose",addMoreMorePopup).click(function()
	  {
		hideAddMoreMore();  
		moreMoreSuccess = false;
	  });
	  
	  successDiv.append("<div class='ra1-pw-addMorePopup-text'>You've successfully shared using Po.st <span id='ra1-pw-shareAgainButton' class='ra1-pw-small-btn'><span><span>Share again!</span></span></span><div id='sharingSuccessDiv' class='ra1-pw-success-moremore'>Loading content...</div></div>");
	  successDiv.append('<div class="ra1-pw-popup-footer"><a href="http://www.po.st" rel="nofollow" target="_blank" class="ra1-pw-powered">Powered by Po.st</a><a href="http://www.po.st/privacy.html" rel="nofollow" class="ra1-pw-privacy" target="_blank">Privacy policy</a></div>');

	  pwjQ('#ra1-pw-shareAgainButton',successDiv).click(function()
	  {
		  switchView("#moreSuccesDiv","#moreShareDiv",addMoreMorePopup);  
	  });
  
	  
	  //buttons
	  var addMoreMorePopupIn = pwjQ('<div id="moreShareDiv" class="ra1-pw-addMorePopup-i" ></div>');
	  addMoreMorePopup.append(addMoreMorePopupIn);
	  var headerDiv = pwjQ('<div class="ra1-pw-popup-title" >Share with friends</div>');
	  
	  var closeButton = pwjQ('<span class="ra1-pw-popup-close" ></span>');
	  closeButton.click(function()
		  {
		     hideAddMoreMore();
		  }
	  );
	  headerDiv.append(closeButton);
	  
	  addMoreMorePopupIn.append(headerDiv);

	  var buttonsDiv =pwjQ('<div class="ra1-pw-moreButtonsDiv" style="height:158px"></id>');	  
	  addMoreMorePopupIn.append(buttonsDiv);
	  
	  var firstColumn = pwjQ('<div class="ra1-pw-morePopupColumn"/>');
	  buttonsDiv.append(firstColumn);
	  var secondColumn = pwjQ('<div class="ra1-pw-morePopupColumn"/>');
	  buttonsDiv.append(secondColumn);	  
	  var index = 0;
	  for (var sharerId in sharingServices)
	  {
		  var sharer = sharingServices[sharerId];
		  if (!isSharer(sharer))
		  {
			  continue;
		  }
		  		  
		  if (index>buttonsCount)
		  {
			  break;
		  }

		  var currentColumn =(index % 2 == 0)? firstColumn:secondColumn;
		  addPopupSharerButton(currentColumn,sharer,index);
		  index = index + 1;
	  }	  

	  var moreButton = createMoreButton(buttonDiv);
	  secondColumn.append(moreButton);

	  
	addMoreMorePopupIn.append('<div class="ra1-pw-popup-footer"><a href="http://www.po.st" target="_blank" rel="nofollow" class="ra1-pw-powered">Powered by Po.st</a><a href="http://www.po.st/privacy.html" rel="nofollow" target="_blank" class="ra1-pw-privacy">Privacy policy</a></div>');
	  
	setCloseListener(addMoreMorePopup);
	pwjQ('body').append(addMoreMorePopup);
}

function setupGooglePlus(buttonDiv)
{
	var googlePlusDiv = null; 
	//google plus button
    var googlePlus = document.createElement("g:plusone");
    googlePlus.setAttribute("count", false);
	if (sizeAspect == 32)
	{
	   googlePlusDiv = pwjQ('<span class="ra1-pw-googlePlus-32"></span>');
	}
	else
	if (sizeAspect == 24)
	{
	       googlePlusDiv = pwjQ('<span class="ra1-pw-googlePlus-24"></span>');
	}
	else
	if (sizeAspect == 16)
	{
		googlePlusDiv = pwjQ('<span class="ra1-pw-googlePlus-16"></span>');
		googlePlus.setAttribute("size", "small");
	}
	
    var plusDiv = googlePlusDiv.get(0);
    plusDiv.appendChild(googlePlus);
	buttonDiv.append(googlePlusDiv);
	pwjQ(document).ready(function()
	{
	  loadGooglePlus();
	});
}


function initialize(divId) {	    
		var buttonDiv = pwjQ(divId).addClass('ra1-pw-classicWidget');
		buttonDiv.hide();

		
		var gap = counterActive ?72:20;
		var sizeGap = sizeAspect!=32?-5:0;
		if (counterActive)
		{
			sizeGap =0;
		}
		buttonDiv.css('width',(sizeAspect+4)*(initialButtons.length+1)+gap+sizeGap);
		buttonDiv.css('height',(sizeAspect+8)+'px');
		
	   readCookies();
	   emitLogPixel(createViewLogUrl());
	   setUpButtons(buttonDiv);
	   if (copyPasteActive && !copyPasteLoading)
	   {
	     initCopyPaste();
	     copyPasteLoading = true;
	   }
      buttonDiv.show();
}

var googlePlusLoaded = false;
var metadataLoaded = false;

//copy-paste
function initCopyPaste()
{
	
  if (!window.radium)
  {
	  var scriptName = postCNDServer+'/script/poSt-copypaste.js';
	  var onload = function()
	  {
		 
	    cGUID = guid();
	    var clickBackUrl = getClickBackUrl('copypaste');
	    	
	    var radvars = {"min":20,"url":clickBackUrl,"msg":"Read more - ","allowLink":false,"callback": function()
	    {
	    	emitLogPixel(createCopyLinkLogUrl());
	  	 	updateCounterValue();	    	
	    }};
	    radvars.jQuery = pwjQ;
        window.radium.init(radvars);
	  };
	  loadScript(scriptName,onload);
  }
  
}

function getCookie(c_name)
{
	var i,x,y,ARRcookies=document.cookie.split(";");
	for (i=0;i<ARRcookies.length;i++)
	{
	  x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
	  y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
	  x=x.replace(/^\s+|\s+$/g,"");
	  if (x==c_name)
	    {
	    return unescape(y);
	    }
	  }
}


}
};
   postwidgetnamespace.init();
}

