/* podpress.js | podPress - JS scripts for the frontend and the Admin Site */
	function podPressShowVideoPreview (strPlayerDiv, strMediaFile, numWidth, numHeight, strPreviewImg) {
		var refPlayerDiv = document.getElementById('podPressPlayerSpace_'+strPlayerDiv);
		if(refPlayerDiv == undefined) {
			return false;
		} 
		refPlayerDiv.innerHTML = podPressGenerateVideoPreview (strPlayerDiv, strMediaFile, numWidth, numHeight, strPreviewImg);
	}

	function podPressGenerateVideoPreview (strPlayerDiv, strMediaFile, numWidth, numHeight, strPreviewImg, bPreviewOnly) {
		if (typeof numWidth == 'undefined') { numWidth = 320; }
		if (typeof numHeight == 'undefined') { numHeight = 240; }
		if (typeof strPreviewImg == 'undefined') { strPreviewImg = podPressDefaultPreviewImage; }
		if (typeof bPreviewOnly == 'undefined') { bPreviewOnly = false; }

		if (false == bPreviewOnly) {
			var strOnclick= ' onclick="javascript:podPressShowHidePlayer('+strPlayerDiv+', \''+strMediaFile+'\', '+numWidth+', '+numHeight+', \'force\'); return false;"';
		} else {
			var strOnclick= ''; // for a preview of this preview player at the player settings page on the admin pages
		}	

		var strResult = '';
		strResult += '<div class="podPress_videoplayer_wrapper" style="width: '+String(Number(numWidth)+14)+'px; height: '+String(Number(numHeight)+50)+'px; padding:0px; margin:0px; display:block;"'+strOnclick+'>';
		strResult += '	<div class="podPress_videoplayer_toprow" style="display:block; width:100%; padding:0px; margin:0px;">';
		strResult += '		<img src="'+podPressBackendURL+'images/vpreview_top_left.png" style="width:7px; height:27px; display:inline; float:left; border:0px; padding:0px; margin:0px;" alt=""/>';
		strResult += '		<span style="height:27px; border:0px; display:block; float:left; padding:0px; margin:0px; width: '+numWidth+'px; text-align:center; background:url(\''+podPressBackendURL+'images/vpreview_top_background.png\'); background-repeat: repeat-x;"><img src="'+podPressBackendURL+'images/vpreview_top_middle.png" style="width:119px; height:27px padding:0px; margin:0px;  float:none; border:0px;" alt="" /></span>';
		strResult += '		<img src="'+podPressBackendURL+'images/vpreview_top_right.png" style="width:7px; height:27px; display:inline; float:left; border:0px; padding:0px; margin:0px;" alt="" />';
		strResult += '	</div>';
		
		if (25 < Number(numHeight)) { // if the height value is smaller than 25 px then create a player preview without an cover or chapter image
		strResult += '	<div class="podPress_videoplayer_middlerow" style="clear:left; width:100%; padding:0px; margin:0px;">';
		strResult += '		<span style="width:7px; height:'+numHeight+'px; padding:0px; margin:0px; display:block; float:left; background:url(\''+podPressBackendURL+'images/vpreview_left_background.png\'); background-repeat:repeat-y;"></span>';
		strResult += '		<img class="podPress_previewImage" src="'+strPreviewImg+'" style="width:'+numWidth+'px; height:'+numHeight+'px; padding:0px; margin:0px; border:0px; float:left; display:inline;" alt="previewImg"  id="podPress_previewImageIMG_'+strPlayerDiv+'" />';
		strResult += '		<span style="width:7px; height:'+numHeight+'px; padding:0px; margin:0px; display:block; float:left; background:url(\''+podPressBackendURL+'images/vpreview_right_background.png\'); background-repeat:repeat-y;"></span>';
		strResult += '	</div>';
		}
		
		strResult += '	<div class="podPress_videoplayer_bottomrow" style="width:100%; padding:0px; margin:0px;">';
		strResult += '		<img src="'+podPressBackendURL+'images/vpreview_bottom_left.png" style="width:7px; height:23px; display:inline; float:left; border:0px; padding:0px; margin:0px;" alt="" />';
		strResult += '		<span style="display:block; float:left; padding:0px; margin:0px; text-align: left; background:url(\''+podPressBackendURL+'images/vpreview_bottom_background.png\'); background-repeat: repeat-x;"><img src="'+podPressBackendURL+'images/vpreview_bottom_middle_left.png" style="width:56px; height:23px; display:inline; border:0px; padding:0px; margin:0px;" alt="" /></span>';
		strResult += '		<span style="height:23px; display:block; float:left; padding:0px; margin:0px; width:'+String(Math.abs(Number(numWidth)-112))+'px; background:url(\''+podPressBackendURL+'images/vpreview_bottom_background.png\'); background-repeat: repeat-x;"></span>';
		strResult += '		<span style="display:block; float:left; padding:0px; margin:0px; text-align:right; background:url(\''+podPressBackendURL+'images/vpreview_bottom_background.png\'); background-repeat: repeat-x;"><img src="'+podPressBackendURL+'images/vpreview_bottom_middle_right.png" style="width:56px; height:23px; display:inline; border:0px; padding:0px; margin:0px;" alt="" /></span>';
		strResult += '		<img src="'+podPressBackendURL+'images/vpreview_bottom_right.png" style="width:7px; height:23px; display:inline; float:left; border:0px; padding:0px; margin:0px;" alt="" />';
		strResult += '	</div>';
		strResult += '</div>';
		return String(strResult);
	}

	function podPressGeneratePlayer(strPlayerDiv, strMediaFile, numWidth, numHeight, strAutoStart, strPreviewImg) {
		if (typeof numWidth == 'undefined' || numWidth == 0) { var numWidth = 320; }
		if (typeof numHeight == 'undefined') { var numHeight = 240; }
		if (typeof strAutoStart == 'undefined') { var strAutoStart = 'false'; }
		
		if(strAutoStart == 'nopreview') {
			return '';
		}
		var lenOfMedia = strMediaFile.length;
		if(strMediaFile.substring(lenOfMedia-8, lenOfMedia) == '.youtube') {
			var strExt = 'youtube';
			strMediaFile = strMediaFile.substring(0, lenOfMedia-8)
		} else if(strMediaFile.substring(lenOfMedia-8, lenOfMedia) == '.torrent') {
			var strExt = 'torrent';
		} else if(strMediaFile.substring(lenOfMedia-3, lenOfMedia-2) == '.') {
			var strExt = strMediaFile.substring(lenOfMedia-2, lenOfMedia);
		} else if(strMediaFile.substring(lenOfMedia-4, lenOfMedia-3) == '.') {
			var strExt = strMediaFile.substring(lenOfMedia-3, lenOfMedia);
		} else {
			var strExt = '';
		}
		strExt = strExt.toLowerCase();
		
		if(strExt != 'mp3'  && strExt != 'youtube' && strAutoStart == 'false') {
			if(strExt == 'youtube') {
				strMediaFile = strMediaFile+'.youtube';
			}
			return podPressGenerateVideoPreview(strPlayerDiv, strMediaFile, numWidth, numHeight, strPreviewImg);
		}

		var strResult = '';
		switch (strExt) {
			case 'm4v':
			case 'm4a':
			case 'avi':
			case 'mpeg':
			case 'mpg':
			case 'mp4':
			case 'qt':
			case 'mov':
				switch (strExt) {
					case 'm4v':
						var strMimeType = 'video/x-m4v';
						break;
					case 'm4a':
						var strMimeType = 'audio/x-m4a';
						break;
					case 'avi':
						var strMimeType = 'video/avi';
						break;
					case 'mpeg':
					case 'mpg':
						var strMimeType = 'video/mpeg';
						break;
					case 'mp4':
						var strMimeType = 'audio/mpeg';
						break;
					case 'qt':
					case 'mov':
						var strMimeType = 'video/quicktime';
						break;
				}
				numHeight = String(Number(numHeight)+ 18); // add up the height of the player controls
				strResult = '<object class="podpress_player_object" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" width="'+numWidth+'px" height="'+numHeight+'px" codebase="http://www.apple.com/qtactivex/qtplugin.cab">';
				strResult += '	<param name="src" value="'+strMediaFile+'" />';
				strResult += '	<param name="href" value="'+strMediaFile+'" />';
				strResult += '	<param name="scale" value="aspect" />';
				strResult += '	<param name="controller" value="true" />';
				strResult += '	<param name="autoplay" value="'+strAutoStart+'" />';
				strResult += '	<param name="bgcolor" value="000000" />';
				strResult += '	<param name="pluginspage" value="http://www.apple.com/quicktime/download/" />';
				strResult += '	<embed src="'+strMediaFile+'" style="width:'+numWidth+'px; height:'+numHeight+'px; background-color:#000;" scale="aspect" cache="true" autoplay="'+strAutoStart+'" controller="true" src="'+strMediaFile+'" type="'+strMimeType+'" pluginspage="http://www.apple.com/quicktime/download/"></embed>';
				strResult += '</object><br/>';
				break;
			case 'wma':
			case 'wmv':
			case 'asf':
				numHeight = String(Number(numHeight)+ 44); // add up the height of the player controls
				strResult = '<object class="podpress_player_object" id="winplayer_'+strPlayerDiv+'" classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'+numWidth+'px" height="'+numHeight+'px" standby="Media is loading..." type="application/x-oleobject">';
				strResult += '	<param name="url" value="'+strMediaFile+'" />';
				strResult += '	<param name="AutoStart" value="'+strAutoStart+'" />';
				strResult += '	<param name="AutoSize" value="true" />';
				strResult += '	<param name="AllowChangeDisplaySize" value="true" />';
				strResult += '	<param name="standby" value="Media is loading..." />';
				strResult += '	<param name="AnimationAtStart" value="true" />';
				strResult += '	<param name="scale" value="aspect" />';
				strResult += '	<param name="ShowControls" value="true" />';
				strResult += '	<param name="ShowCaptioning" value="false" />';
				strResult += '	<param name="ShowDisplay" value="false" />';
				strResult += '	<param name="ShowStatusBar" value="false" />';
				strResult += '	<embed type="application/x-mplayer2" src="'+strMediaFile+'" style="width:'+numWidth+'px; height:'+numHeight+'px; background-color:#000;" scale="aspect" AutoStart="'+strAutoStart+'" ShowDisplay="0" ShowStatusBar="0" AutoSize="1" AnimationAtStart="1" AllowChangeDisplaySize="1" ShowControls="1"></embed>';
				strResult += '</object><br/>';
				break;
			case 'swf':
				if(strAutoStart == 'true') {
					strAutoStart = '';
				} else {
					strAutoStart = ' play="false"';
				}
				strResult = '<object class="podpress_player_object" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0"'+strAutoStart+' width="'+numWidth+'" height="'+numHeight+'" menu="true">';
				strResult += '	<param name="movie" value="'+strMediaFile+'" />';
				strResult += '	<param name="quality" value="high" />';
				strResult += '	<param name="menu" value="true" />';
				strResult += '	<param name="scale" value="noorder" />';
				strResult += '	<embed src="'+strMediaFile+'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"'+strAutoStart+' width="'+numWidth+'" height="'+numHeight+'" menu="true"></embed>';
				strResult += '</object><br />';
				break;
			case 'flv':
				if(strAutoStart == 'true') {
					strAutoStart = '';
				} else {
					strAutoStart = '&autoStart=false';
				}
				strResult = '<object class="podpress_player_object" type="application/x-shockwave-flash" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" data="'+podPressBackendURL+'players/flvplayer.swf?file='+strMediaFile+strAutoStart+'" width="'+numWidth+'" height="'+numHeight+'" >';
				strResult += '<param name="movie" value="'+podPressBackendURL+'players/flvplayer.swf?file='+strMediaFile+strAutoStart+'" />';
				strResult += '<param name="wmode" value="transparent" />';
				strResult += '<param name="quality" value="high" />';
				strResult += '<param name="menu" value="true" />';
				strResult += '<embed src="'+podPressBackendURL+'players/flvplayer.swf?file='+strMediaFile+strAutoStart+'" type="application/x-shockwave-flash" width="'+numWidth+'" height="'+numHeight+'" wmode="transparent"></embed>';
				strResult += '</object>';
				break;
			case '.rm':
				strResult = '<object class="podpress_player_object" id="realplayer_'+strPlayerDiv+'" classid="clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa" width="'+numWidth+'" height="'+numHeight+'">';
				strResult += '	<param name="src" value="'+strMediaFile+'" />';
				strResult += '	<param name="autostart" value="'+strAutoStart+'" />';
				strResult += '	<param name="controls" value="imagewindow,controlpanel" />';
				strResult += '	<embed src="'+strMediaFile+'" width="'+numWidth+'" height="'+numHeight+'" autostart="'+strAutoStart+'" controls="imagewindow,controlpanel" type="audio/x-pn-realaudio-plugin"></embed>';
				strResult += '</object>';
				break;
			case 'ogv':
			case 'ogg':
				var strAdditionalParam = '';
				if ( 'ogg' == strExt  ) {
					var strVideo ='false';
					var strAudio ='true';
					var strTag = 'audio';
					if ( 0 == Number(numHeight) ) {
						numHeight = '24';
					}
				} else {
					var strVideo ='true';
					var strAudio ='true';
					var strTag = 'video';
					strAdditionalParam = '<param name="keepAspect" value="true" />';
					if ( 0 == Number(numHeight) ) {
						numHeight = '240';
					}
				}
				if ( strAutoStart == 'true' ) {
					var strAutoPlay = ' autoplay="autoplay"';
				} else {
					var strAutoPlay = '';
				}
				// Gecko since 1.9.1 and Presto since 2.5 support OGG Audio and Video in the HTML 5 <audio> and <video> element.
				if ( podPressHTML5 == true && (-1 != navigator.userAgent.search(/rv:([0-9]\.*[0-9]*\.*[0-9]*\.*[0-9]*)\) Gecko/gi) && true == podPress_is_v1_gtoreq_v2(RegExp.$1, '1.9.1')) || (-1 != navigator.userAgent.search(/Presto\/([0-9]\.*[0-9]*\.*[0-9]*\.*[0-9]*)/gi) && true == podPress_is_v1_gtoreq_v2(RegExp.$1, '2.5')) ) {
					var maskedurl = decodeURI(strMediaFile);
					var realurl = podPress_get_OrigURL(strPlayerDiv);
					strResult = '<' + strTag + ' id="podpresshtml5_'+strPlayerDiv+'" controls="controls" preload="metadata"' + strAutoPlay + ' onplaying="podPress_html5_count(\'' + maskedurl + '\', this.id)">';
					strResult += '<source src="' + realurl + '" type="' + strTag + '/ogg" />';
					var use_html5 = true;
				} else {
					strResult += '<object class="podpress_player_object" classid="clsid:CAFEEFAC-0015-0000-0000-ABCDEFFEDCBA" type="application/x-java-applet;jpi-version=1.5.0" width="'+numWidth+'" height="'+numHeight+'">';
						strResult += '<param name="code" value="com.fluendo.player.Cortado.class" />';
						if ( true == podPress_cortado_signed ) {
							strResult += '<param name="archive" value="'+podPressBackendURL+'players/cortado/cortado-signed-0.6.0.jar" />';
						} else {
							strResult += '<param name="archive" value="'+podPressBackendURL+'players/cortado/cortado-ovt-stripped-0.6.0.jar" />';
						}
						strResult += '<param name="url" value="'+strMediaFile+'" />';
						strResult += '<param name="statusHeight" value="24" />';
						strResult += '<param name="seekable" value="auto" />';
						strResult += '<param name="local" value="false" />';
						strResult += '<param name="autoPlay" value="'+String(strAutoStart)+'" />';
						strResult += '<param name="video" value="'+strVideo+'" />';
						strResult += '<param name="audio" value="'+strAudio+'" />';
						strResult += '<param name="bufferSize" value="200" />';
						strResult += strAdditionalParam;
						strResult += '<param name="userId" value="user" />';
						strResult +='<param name="password" value="test" />';
						strResult +='<param name="debug" value="0" />';
						strResult += '<comment>';
							strResult += '<embed code="com.fluendo.player.Cortado.class" type="application/x-java-applet;jpi-version=1.5.0" archive="'+podPressBackendURL+'players/cortado/cortado-ovt-stripped-0.6.0.jar" width="'+numWidth+'" height="'+numHeight+'" url="'+strMediaFile+'" statusHeight="24" seekable="auto" local="false" autoPlay="'+String(strAutoStart)+'" video="false" audio="true" bufferSize="200" userId="user" password="test" debug="0">';
								strResult += '<noembed>';
								strResult += 'No Java Support.';
								strResult += '</noembed>';
							strResult += '</embed>';
						strResult += '</comment>';
					strResult += '</object>';
					var use_html5 = false;
				}
				if ( true == use_html5 ) {
					strResult += '</' + strTag + '><br />';
				}
				break;
			case 'youtube':
				if(strAutoStart == 'true') {
					strAutoStart = '1';
				} else {
					strAutoStart = '0';
				}
				// classid is for the Adobe Flash Player and is necessary in IE6 which inserts this object tag via innerHTML only with this classid
				strResult = '<object class="podpress_player_object" classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'+String(numWidth)+'" height="'+String(numHeight)+'">';
				strResult += '	<param name="movie" value="'+'http://www.youtube.com/v/'+strMediaFile+'&rel=1&fs=1&autoplay='+strAutoStart+'" />';
				strResult += '	<param name="allowFullScreen" value="true"></param>';
				strResult += '	<param name="allowScriptAccess" value="always"></param>';
				strResult += '	<embed src="'+'http://www.youtube.com/v/'+strMediaFile+'&rel=1&fs=1&autoplay='+strAutoStart+'" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="'+String(numWidth)+'" height="'+String(numHeight)+'"></embed>';
				strResult += '</object>';
			break;
			case 'mp3':
			default:
				// since 8.8.6 this is only for the Podango player
				if(strAutoStart == 'true') {
					var localCopyPlayerOptions = podPressMP3PlayerOptions+'autostart=yes&amp;'; 
				} else {
					var localCopyPlayerOptions = podPressMP3PlayerOptions+'autostart=no&amp;'; 
				}
				strResult = '';
				if(podPressMP3PlayerWrapper) {
					strResult += '<div style="width:342px; height:40px; padding:0px; margin:0px; background-image:url('+podPressBackendURL+'images/listen_wrapper.gif); display:block;"><span style="width:45px;height:40px;display:block;float:left;">&nbsp;</span>';
					strResult += '<div style="width:290px; height:24px; margin:0px; padding:8px 0px 8px 0px; display:block; float:left; background-color:transparent;">';
				}
				strResult += '<object class="podpress_player_object" type="application/x-shockwave-flash" classid="CLSID:D27CDB6E-AE6D-11cf-96B8-444553540000" data="'+podPressBackendURL+'players/'+podPressPlayerFile+'" id="audioplayer'+strPlayerDiv+'" width="290" height="24" style="display:block;">';
				strResult += '<param name="movie" value="'+podPressBackendURL+'players/'+podPressPlayerFile+'" />';
				strResult += '<param name="FlashVars" value="playerID=audioplayer'+strPlayerDiv+localCopyPlayerOptions+'soundFile='+unescape(strMediaFile)+'" />';
				strResult += '<param name="bgcolor" value="FFFFFF" />';
				strResult += '<param name="menu" value="false" />';
				strResult += '<param name="wmode" value="transparent" />';
				strResult += '<param name="quality" value="high" />';
				strResult += '<embed src="' + podPressBackendURL + 'players/' + podPressPlayerFile+ '" type="application/x-shockwave-flash" flashvars="playerID=audioplayer'+strPlayerDiv+localCopyPlayerOptions+'soundFile='+unescape(strMediaFile)+'" width="290" height="24" wmode="transparent"></embed>';
				strResult += '</object>';
				if (podPressMP3PlayerWrapper) {
					strResult += '</div></div>';
				}
			break;
		}
		return strResult;
	}
	
	function podPress_getfileext(strMediaFile) {
		if (typeof strMediaFile == 'undefined') { return false; }
		var pos = strMediaFile.lastIndexOf('\.');
		pos = pos+1;
		var strExt = strMediaFile.substring(pos);
		strExt = strExt.toLowerCase();
		return strExt;
	}
	
	function podPressShowHidePlayer(strPlayerDiv, strMediaFile, numWidth, numHeight, strAutoStart, strPreviewImg, strTitle, strArtist) {
		var refPlayerDiv = document.getElementById('podPressPlayerSpace_'+strPlayerDiv);
		var refPlayerDivLink = document.getElementById('podPressPlayerSpace_'+strPlayerDiv+'_PlayLink');
		
		if(refPlayerDiv == undefined) {
			return false;
		}

		if (strAutoStart == 'force') {
			strAutoStart = 'true';
			var bForceShow = true;
		} else {
			var bForceShow = false;
		}
		var strExt = podPress_getfileext(strMediaFile);

		if(bForceShow == true) {
			refPlayerDivLink.innerHTML=podPressText_HidePlayer;
			refPlayerDivLink.parentNode.onclick = function(){ podPressShowHidePlayer(strPlayerDiv, strMediaFile, numWidth, numHeight, 'true', strPreviewImg, strTitle, strArtist); return false; };
			if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && true == podPressMP3PlayerWrapper ) {
				document.getElementById('podpress_lwc_' + strPlayerDiv).style.backgroundImage = 'url('+podPressBackendURL+'images/listen_wrapper.gif)';
				document.getElementById('podpress_lwc_' + strPlayerDiv).style.display='block';
			}
			refPlayerDiv.parentNode.style.display='block';
			refPlayerDiv.style.display='block';
		} else {
			if(refPlayerDivLink.innerHTML == podPressText_PlayNow) {
				refPlayerDivLink.innerHTML=podPressText_HidePlayer;
				if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && true == podPressMP3PlayerWrapper ) {
					document.getElementById('podpress_lwc_' + strPlayerDiv).style.backgroundImage = 'url('+podPressBackendURL+'images/listen_wrapper.gif)';
					document.getElementById('podpress_lwc_' + strPlayerDiv).style.display='block';
				}
				refPlayerDiv.parentNode.style.display='block';
				refPlayerDiv.style.display='block';
			} else {
				refPlayerDivLink.innerHTML=podPressText_PlayNow;
				if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && true == podPressMP3PlayerWrapper ) {
					document.getElementById('podpress_lwc_' + strPlayerDiv).style.backgroundImage = '';
					document.getElementById('podpress_lwc_' + strPlayerDiv).style.display='none';
				}
				refPlayerDiv.style.display='none';
				refPlayerDiv.parentNode.style.display='none';
				if(document.getElementById('winplayer') != undefined) {
					if(document.getElementById('winplayer').controls) {
						document.getElementById('winplayer').controls.stop();
					}
				} else {
					refPlayerDiv.innerHTML='';
				}
				bForceShow = true;
				refPlayerDivLink.parentNode.onclick = function(){ podPressShowHidePlayer(strPlayerDiv, strMediaFile, numWidth, numHeight, 'force', strPreviewImg, strTitle, strArtist); return false; };
				return true;
			}
		}
		
		if(strAutoStart == 'nopreview') {
			refPlayerDivLink.innerHTML=podPressText_PlayNow;
			if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && true == podPressMP3PlayerWrapper ) {
				document.getElementById('podpress_lwc_' + strPlayerDiv).style.backgroundImage = '';
			}
			refPlayerDiv.style.display='none';
		}
		
		// WebKit supports since 525.x and Internet Explorer since 9.0 MP3 in the HTML 5 <audio> element.
		if ( strExt == 'mp3' && podPressHTML5 == true && ( (-1 != navigator.userAgent.search(/Webkit\/([0-9]+\.[0-9]+)/gi) && true == podPress_is_v1_gtoreq_v2(RegExp.$1, '525')) ||  true == podPress_is_modern_ie() ) ) {
			if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && true == podPressMP3PlayerWrapper ) {
				var pobj = document.getElementById('podpress_lwc_' + strPlayerDiv);
				pobj.innerHTML = '<div id="podPressPlayerSpace_' + strPlayerDiv + '"><!-- podPress --></div>';
				pobj.removeAttribute('class');
				pobj.style.backgroundImage = '';
			}
			if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && false == podPressMP3PlayerWrapper ) {
				document.getElementById('podPressPlayerSpace_' + strPlayerDiv).parentNode.setAttribute('class', 'podpress_playerspace');
			}
			if ( podPressHTML5_showplayersdirectly == true || -1 != navigator.userAgent.search(/iPhone|iPod|iPad/gi) ) {
				podPressenprintHTML5audio(strPlayerDiv, strMediaFile);
			} else {
				document.getElementById('podPressPlayerSpace_' + strPlayerDiv).innerHTML = '<a id="podpress_html5_play_'+ strPlayerDiv+'" href="javascript:void(null);" onclick="podPressenprintHTML5audio(\''+strPlayerDiv+'\', \'' + strMediaFile + '\', true);" class="podpress_play_button" title="' + podpressL10.playbutton + '" style="background-image:url('+podPressBackendURL+'images/play_button_dyn_v4_32.png);"></a>';
			}
		} else {
			if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' ) {
				if (strAutoStart == 'true') {
					var valAutostart = 'yes'; 
				} else {
					var valAutostart = 'no'; 
				}
				if ( podPressOverwriteTitleandArtist == true && typeof strTitle != 'undefined' && typeof strArtist != 'undefined' && strTitle != 'undefined' && strArtist != 'undefined' && strTitle != '' && strArtist !='' ) {
					podpressAudioPlayer.embed("podPressPlayerSpace_" + strPlayerDiv, { soundFile: podPressencodeSource(strMediaFile), origSource: decodeURI(strMediaFile), encode: 'yes', width: 290, height: 24, autostart: valAutostart, titles: strTitle, artists: strArtist});
				} else {
					podpressAudioPlayer.embed("podPressPlayerSpace_" + strPlayerDiv, { soundFile: podPressencodeSource(strMediaFile), origSource: decodeURI(strMediaFile), encode: 'yes', width: 290, height: 24, autostart: valAutostart });
				}
			} else {
				refPlayerDiv.innerHTML=podPressGeneratePlayer(strPlayerDiv, strMediaFile, numWidth, numHeight, strAutoStart, strPreviewImg);
			}
		}
	}

	/**podPressenprintHTML5audio - inserts the HTML5 audio element
	* @param strPlayerDiv String - ID number of the target element
	* @param strMediaFile String - URL of the media file
	*/
	function podPressenprintHTML5audio(strPlayerDiv, strMediaFile, playnow) {
		if (typeof playnow != 'boolean') { var playnow = false; }
		var maskedurl = decodeURI(strMediaFile);
		var realurl = podPress_get_OrigURL(strPlayerDiv);
		// add a background-color to the audio element in order to overwrite an effect which occurrs in Chrome/Chromium >= v10 caused by the ocasionally inherited value "background-color: transparent:"
		if ( -1 != navigator.userAgent.search(/(Chrome|Chromium)\/([0-9]+\.[0-9]+)/gi) && true == podPress_is_v1_gtoreq_v2(RegExp.$2, '10') )  {
			var chrome_css_helper = ' style="background-color:#262626"';
		} else {
			var chrome_css_helper = '';
		}
		if ( playnow == true ) {
			document.getElementById('podPressPlayerSpace_' + strPlayerDiv).innerHTML = '<audio id="podpresshtml5_'+strPlayerDiv+'" controls="controls" preload="auto" autoplay="autoplay"' + chrome_css_helper + ' onplaying="podPress_html5_count(\'' + maskedurl + '\', this.id)"><source src="' + realurl + '" type="audio/mpeg" /></audio>';
		} else {
			document.getElementById('podPressPlayerSpace_' + strPlayerDiv).innerHTML = '<audio id="podpresshtml5_'+strPlayerDiv+'" controls="controls" preload="none"' + chrome_css_helper + ' onplaying="podPress_html5_count(\'' + maskedurl + '\', this.id)"><source src="' + realurl + '" type="audio/mpeg" /></audio>';
		}
	}
	
	
	/** podPressencodeSource - Encodes the given string. This function is the JS equivalent of the function with the same name of the WP Audio Player plugin (http://wpaudioplayer.com/standalone)
	* @param str String - the string to encode
	* @return String - the encoded string
	*/
	function podPressencodeSource(str) {
		var str = unescape(str);
		var str = encodeURIComponent(str);
		var str = unescape(str);
		var ntexto = '';
		var codekey = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-';
		for ( var i = 0; i < String(str).length; i++ ) {
			var tmpstr = "0000" + Number(str.charCodeAt(i)).toString(2);
			ntexto += tmpstr.substr(tmpstr.length-8, 8);
		}
		ntexto += "00000".substr( 0, 6-(ntexto).length % 6);
		str = "";
		for ( var i = 0; i < (ntexto).length-1; i += 6 ) {
			str += codekey.substr( parseInt(ntexto.substr( i, 6), 2), 1 );
		}
		return str;
	}
	
	/** podPress_compare_v1_v2 - compares to version strings or numbers
	* @param v1 String or Number
	* @param v2 String or Number
	* @return mixed - returns "lt" in case v1 is lower than v2, "gt" in case v1 is greater than v2, "eq" in case v1 is equal to v2 or false in case v1 and v2 are a string (which is not empty) or a number
	*/
	function podPress_compare_v1_v2(v1, v2) {
		if ( ((typeof v1 == 'string' && false == podPress_is_emptystr(v1)) || typeof v1 == 'number') && ((typeof v2 == 'string' && false == podPress_is_emptystr(v1)) || typeof v2 == 'number') ) {
			var v1_parts = String(v1).split('.');
			var v2_parts = String(v2).split('.');
			var l1 = v1_parts.length;
			var l2 = v2_parts.length;
			var minl = Math.min(l1, l2);
			for (var i = 0; i < minl; i++ ) {
				var l1int = parseInt(v1_parts[i]);
				var l2int = parseInt(v2_parts[i]);
				if ( l1int < l2int ) {
					return 'lt';
				} else if ( l1int > l2int ) {
					return 'gt';
				}
			}
			return 'eq';
		} else {
			return false;
		}
	}
	
	/** podPress_is_v1_gtoreq_v2 - checks whether v1 is greater than or equal to v2 or not
	* @param v1 string or number
	* @param v2 string or number
	* @return bool - if v1 <= v2 then it is true else it is false
	*/
	function podPress_is_v1_gtoreq_v2(v1, v2) {
		var vc = podPress_compare_v1_v2(v1, v2);
		if ('gt' == vc || 'eq' == vc) {
			return true;
		} else {
			return false;
		}
	}
	
	/** podPress_is_emptystr - checks whether a given value is an empty string or not
	* @param val string 
	* @return bool - if v1 <= v2 then it is true else it is false
	*/	
	function podPress_is_emptystr( val ) {
		var str = String(val);
		var str_trim = str.replace(/\s+$/, '');
		str_trim = str_trim.replace(/^\s+/, '');
		if (str_trim.length > 0) {
			return false;
		} else {
			return true;
		}
	}	
	
	function podPressPopupPlayer(strPlayerDiv, strMediaFile, numWidth, numHeight, windowName, postID, strTitle, strArtist) {
		var refPlayerDiv = document.getElementById('podPressPlayerSpace_'+String(strPlayerDiv));
		var refPlayerDivLink = document.getElementById('podPressPlayerSpace_'+strPlayerDiv+'_PlayLink');
		var strExt = podPress_getfileext(strMediaFile);
		var strPreviewImg ='';
		if (refPlayerDiv != undefined) {
			refPlayerDivLink.innerHTML=podPressText_PlayNow;
			if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && true == podPressMP3PlayerWrapper ) {
				document.getElementById('podpress_lwc_' + strPlayerDiv).style.backgroundImage = '';
			}
			refPlayerDiv.style.display='none';
			if(document.getElementById('winplayer') != undefined) {
				if(document.getElementById('winplayer').controls) {
					document.getElementById('winplayer').controls.stop();
				}
			} else {
				if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' ) {
					var podPress_container = refPlayerDiv.parentNode;
					var newdiv = document.createElement('div');
					newdiv.setAttribute('id', 'podPressPlayerSpace_'+String(strPlayerDiv));
					podPress_container.replaceChild(newdiv, refPlayerDiv);
				} else {
					refPlayerDiv.innerText = '';
				}
			}
			//refPlayerDivLink.parentNode.onclick = function(){ podPressShowHidePlayer(strPlayerDiv, strMediaFile, numWidth, numHeight, 'force', strPreviewImg, strTitle, strArtist); return false; };
		}
		
		if (typeof windowName == 'undefined') { windowName = 'podPress';  var backto_name = podpressL10.theblog; } else { var backto_name = windowName; }
		if (typeof postID != 'undefined' && Number(postID) > 0) { var backlink = podPressBlogURL + '?p=' + postID; } else { var backlink = podPressBlogURL; }
		if (typeof numWidth == 'undefined') { numWidth = 320; }
		if (typeof numHeight == 'undefined') { numHeight = 240; }
		
		var strResult = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\n';
		strResult += '<HTML xmlns="http://www.w3.org/1999/xhtml">\n';
		strResult += '<HEAD>\n';
		strResult += '<TITLE>'+windowName+' - Popup Player</TITLE>\n';
		strResult += '<link rel="stylesheet" id="podpress_frontend_styles-css"  href="'+podPressBackendURL+'podpress.css" type="text/css" media="all" />\n';
		// WebKit supports since 525.x and Internet Explorer since 9.0 MP3 in the HTML 5 <audio> element.
		if ( strExt == 'mp3' && podPressHTML5 == true && podPress_is_modern_ie() == true ) {
			var is_modern_ie = true;
			numWidth = numWidth + 200;
			numHeight = numHeight + 50;
		} else {
			var is_modern_ie = false;
		}
		if ( strExt == 'mp3' && podPressHTML5 == true && ((-1 != navigator.userAgent.search(/Webkit\/([0-9]+\.[0-9]+)/gi) && true == podPress_is_v1_gtoreq_v2(RegExp.$1, '525')) || true == is_modern_ie ) ) {
			var use_html5 = true;
			strResult += '<script type="text/javascript" src="'+podPressBackendURL+'js/podpress_popupplayer.js"></script>\n';
		} else {
			var use_html5 = false;
		}
		if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' && false == use_html5) {
			strResult += '<script type="text/javascript" src="'+podPressBackendURL+'players/1pixelout/1pixelout_audio-player.js"></script>\n';
			strResult += '<script type="text/javascript">\n//<![CDATA[\n';
			strResult += '	podpressAudioPlayer.setup("'+podPressBackendURL+'players/1pixelout/'+podPressPlayerFile+'", {bg:"' + podPressPopupPlayerOpt.bg+ '", text:"' + podPressPopupPlayerOpt.text+ '", leftbg:"' + podPressPopupPlayerOpt.leftbg+ '", lefticon:"' + podPressPopupPlayerOpt.lefticon+ '", voltrack:"' + podPressPopupPlayerOpt.voltrack+ '", volslider:"' + podPressPopupPlayerOpt.volslider+ '", rightbg:"' + podPressPopupPlayerOpt.rightbg+ '", rightbghover:"' + podPressPopupPlayerOpt.rightbghover+ '", righticon:"' + podPressPopupPlayerOpt.righticon+ '", righticonhover:"' + podPressPopupPlayerOpt.righticonhover+ '", loader:"' + podPressPopupPlayerOpt.loader+ '", track:"' + podPressPopupPlayerOpt.track+ '", border:"' + podPressPopupPlayerOpt.border+ '", tracker:"' + podPressPopupPlayerOpt.tracker+ '", skip:"' + podPressPopupPlayerOpt.skip+ '", slider:"' + podPressPopupPlayerOpt.slider+ '", initialvolume:"' + podPressPopupPlayerOpt.initialvolume+ '", buffer:"' + podPressPopupPlayerOpt.buffer+ '", checkpolicy:"' + podPressPopupPlayerOpt.checkpolicy+ '", pagebg:"FFFFFF", transparentpagebg:"yes"} );\n';
			strResult += '//]]>\n</script>\n';
		} 
		strResult += '<script type="text/javascript">\n//<![CDATA[\n';
		strResult += '	function closepopupwindow() { window.close(); }\n';
		strResult += '//]]>\n</script>\n';
		strResult += '</HEAD>\n';
		strResult += '<BODY>\n';
		strResult += '<div id="podpress_popupplayer_container">\n';
		if ( true == use_html5 ) {
			var maskedurl = decodeURI(strMediaFile);
			var realurl = podPress_get_OrigURL(strPlayerDiv);
			strResult += '<div id="podPressPlayerSpace_popup"><audio id="podpresshtml5_popup" controls="controls" preload="metadata" autoplay="autoplay" onplaying="podPress_html5_count(\'' + maskedurl + '\', this.id)"><source src="' + realurl + '" type="audio/mpeg" /></audio></div>\n';
		} else {
			if ( strExt == 'mp3' && podPressPlayerFile == '1pixelout_player.swf' ) {
				if ( true == podPressMP3PlayerWrapper ) {
					strResult += '<div class="podpress_listenwrapper_container" style="background-image:url('+podPressBackendURL+'images/listen_wrapper.gif);"><div class="podpress_mp3_borderleft"></div><div class="podpress_1pixelout_container"><div id="podPressPlayerSpace_popup"></div></div></div>\n';
				} else {
					strResult += '<div id="podPressPlayerSpace_popup"></div>\n';
				}
				strResult += '<script type="text/javascript">\n//<![CDATA[\n';
				if ( podPressOverwriteTitleandArtist == true && typeof strTitle != 'undefined' && typeof strArtist != 'undefined' && strTitle != 'undefined' && strArtist != 'undefined' && strTitle != '' && strArtist !='' ) {
					strResult += '	podpressAudioPlayer.embed("podPressPlayerSpace_popup", { soundFile: "' + podPressencodeSource(strMediaFile) + '", origSource: "' + decodeURI(strMediaFile) + '", encode: "yes", width: 290, height: 24, autostart: "yes", titles: "'+escape(strTitle)+'", artists: "'+escape(strArtist)+'" });\n';
				} else {
					strResult += '	podpressAudioPlayer.embed("podPressPlayerSpace_popup", { soundFile: "' + podPressencodeSource(strMediaFile) + '", origSource: "' + decodeURI(strMediaFile) + '", encode: "yes", width: 290, height: 24, autostart: "yes" }); \n';
				}
				strResult += '//]]>\n</script>\n';
			} else {
				strResult +=  podPressGeneratePlayer('popup', strMediaFile, numWidth, numHeight, 'true');
			}
		}
		strResult += '</div>\n';
		strResult += '<div id="podpress_backtoclose_container"><span id="podpress_popup_backto"><span>' + podpressL10.openblogagain + '</span><br /><a href="' + backlink + '" target="_blank">' +  backto_name + '</a></span><span id="podpress_popup_close"><a href="#close" onclick="closepopupwindow();">' + podpressL10.close + '</a></span></div>\n';
		strResult += '</BODY>\n';
		strResult += '</HTML>';

		if (strExt == 'mp3' && podPressMP3PlayerWrapper) {
			var windowWidth = Number(numWidth) + 65;
		} else {
			var windowWidth = Number(numWidth) + 20;
		}
		if (strExt == 'mp3') {
			var windowHeight = Number(numHeight) + 50;
		} else {
			var windowHeight = Number(numHeight) + 70;
		}
		podpresswindow = window.open('about:blank', 'podPressPlayer', 'width='+String(windowWidth)+',height='+String(windowHeight)+',left=50,top=50,toolbar=no,scrollbars=no,location=no,statusbar=no,menubar=no,resizable=yes');
		podpressdocument = podpresswindow.document;
		podpressdocument.open();
		podpressdocument.write(strResult);
		podpressdocument.close();
		podpresswindow.focus();
		if (navigator.appName == 'Microsoft Internet Explorer') {
			podpresswindow.location.reload();
		}
	}
	
	/** podPress_is_modern_ie - checks the current browser is an Internet Explorer 9.0 or newer
	* @return bool
	*/	
	function podPress_is_modern_ie() {
		if ( -1 != navigator.userAgent.search(/Trident\/([0-9]+\.[0-9]+)/gi) && true == podPress_is_v1_gtoreq_v2(RegExp.$1, '5') && -1 != navigator.userAgent.search(/MSIE\s([0-9]+\.[0-9]+)/gi)  && true == podPress_is_v1_gtoreq_v2(RegExp.$1, '9') ) {
			return true;
		} else {
			return false;
		}
	}
	
	/** podPress_get_OrigURL -  get the OrigURL
	* @param mixed $url - the masked url
	* @return string - the real URL
	*/	
	function podPress_get_OrigURL(strPlayerDiv) {	
		var realurl = document.getElementById('podPressPlayerSpace_' + strPlayerDiv + '_OrigURL').value;
		if ( typeof podPressPT == 'boolean' && podPressPT == true ) {
			realurl = realurl.replace(/^(https?:\/\/|http:\/\/)/, '');
			realurl = 'http://www.podtrac.com/pts/redirect.mp3?' + realurl;
		} else if ( typeof podPressBK == 'string' && podPressBK != '' ) {
			realurl = realurl.replace(/^http:\/\//, '');
			realurl = 'http://media.blubrry.com/' + podPressBK + '/'+ realurl;
		}
		return realurl;
	}
	
	/** podPress_html5_count - counts how many times a media file was played (HTML5)
	* @param mixed $url - the masked url
	* @param mixed $id - the ID of the player element
	*/	
	function podPress_html5_count(url, id) {
		if ( typeof podPressHTML5sec == 'string' && podPressHTML5sec != '' ) { // if statistics are enabled then podPressHTML5sec exists and is an not-empty string 
			var startTime = document.getElementById( id ).currentTime;
			if (startTime < 0.1) { // count only if the player has been started at the position < 0.1 seconds
				jQuery.ajax({
					async: true,
					url: podPressBackendURL + 'podpress_backend.php',
					type: 'POST',
					dataType: 'text',
					data: 'action=getrealurl&url='+encodeURIComponent(url)+'&_ajax_nonce=' + podPressHTML5sec
				});
			}
		}
	}

	function podPressGetBaseName(file) {
		var Parts = file.split('\\');
		if( Parts.length < 2 ) {
			Parts = file.split('/');
		}
		return Parts[ Parts.length -1 ];
	}