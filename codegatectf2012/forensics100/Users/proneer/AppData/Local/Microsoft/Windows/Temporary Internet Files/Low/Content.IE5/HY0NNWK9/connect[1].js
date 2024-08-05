var requireSessionTrue = 0;
var last_step = 0;
var site_url = 'software.informer.com';
var img_url = 'img.informer.com';
var api_key = '306010602255';

var platform_re = /(software|linux|mac|android)\.informer\.com/;
var matches = platform_re.exec(document.domain.toLowerCase());
var platform_domain = 'software';
    if (matches != null)
        platform_domain = matches[1];
    if(platform_domain =='mac')
        api_key='100726183336972';
    else if(platform_domain =='linux')
        api_key='241905805833117';
    else if(platform_domain =='android')
        api_key='287311561300969';
try
{
    document.observe('dom:loaded', function()
    {
        if(typeof(window['fb_auth']) == 'undefined')
            fb_auth=false;

        function fb_init()
        {
            FB.init({
                appId:api_key,
                cookie:true,
                status:true,
                xfbml:true
            });
//            FB.getLoginStatus(function(response) {
//
//                if(response.perms)
//                {
//
//                    var json  = JSON.parse(response.perms);
//                    var perms=json["extended"];
//                    //var perms=response.perms;
//                    update_perms(perms);
//
//        }
//            });
            //FB_RequireFeatures(["XFBML"], function() {
//            //FB.Facebook.init(api_key, "http://"+top.location.host+"/xd_receiver.htm");
            var container = $('fbcontainer');
            if (container) container.style.display = 'block';
            var container2 = $('fbcontainer2');
            if (container2) container2.style.display = 'block';

        }
        if (/msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent))
        {
            var div = document.createElement('div');
            div.innerHTML = '<div id="FB_HiddenContainer" style="position:absolute; top:-10000px; width:0px; height:0px;" ></div>';
            document.body.appendChild(div);
        }

        var divFb = document.createElement('div');
        divFb.setAttribute('id', "fb-root");
        document.body.appendChild(divFb);

        var script = document.createElement('script'), done = false;
        script.src = 'http://connect.facebook.net/en_US/all.js';
        script.onload = script.onreadystatechange = function() {
            if ( !done && ( !this.readyState || this.readyState == "loaded" || this.readyState == "complete") ) {done = true;fb_init();}

        };
        var dbody = $$('body')[0];
        dbody.appendChild(script);
        if(fb_auth)
         {
            //if user is logged in on SI, check if he has pair account on SI
            new Ajax.Request('/ajax/facebook.php?check',
            {
                method: 'get',
                onSuccess: function(data)
                {
                    var response = data.responseText;
                    if (response == 'checkauth')
                    {
    //                    var a = $$('td#logout2 a')[1];
    //                    var img = document.createElement('img');
    //                    img.src="http://"+img_url+"/images/fbicon.ico";
    //                    img.style.top = '4px';
    //                    img.style.position = 'relative';
    //                    img.style.marginRight = '3px';
    //                    var span = $$('td#logout2 p')[1];
    //                    span.insertBefore(img, a);
    //                    span = $$('td#logout2 p')[2];
    //                    span.innerHTML = '<a href="#" onclick="logout()">Log out</a>'
                    }
                }
            });
        }
    });


    var logbtn = $('fbloginbtn');
    var logbtncopy = $('fbloginbtncopy');//used when there is more than one fb login button on the page (like, the first is in header, the second is on register page). If the second button has the same id, it won't work. On click event will be set for only the first one.
    var logbtn2 = $('fbloginbtn2');

	var fblogin= function() {
            FB.login(function(response) {
                if (response.authResponse) {
                        new Ajax.Request('/ajax/facebook.php?check',
                        {
                            method: 'get',
                            onSuccess: function(data)
                            {
                                var response = data.responseText;
                                if (response == 'checkauth')
                                {
                                    if (window.location.hostname == 'android.informer.com')
                                        window.location = 'http://android.informer.com';
                                    else
                                        window.location.reload();
                                }
                                else
                                {
                                    lastMenu();
                                }

                }
                        })
                }

            }, {
                scope: 'email,offline_access,publish_stream'

            });
        }

	if (logbtn)
	    logbtn.onclick=fblogin;

	if (logbtncopy)
	    logbtncopy.onclick=fblogin;

      if (logbtn2)
        logbtn2.onclick = function() {
            FB.login(function(response) {
                if (response.session) {
                  if(response.perms)
                    {
                        var perms=response.perms;
                        update_perms(perms);
                    }
                    lastMenu();
                }

            },{
                perms: 'email,offline_access,publish_stream'

            });
        }
    }
catch (exc) {}

function update_perms(permissions)
{
    new Ajax.Request('/ajax/facebook.php?permissions='+permissions+'&action=upperms',
    {
        method:'get',

        onSuccess: function(data) {
            return;
        }
    });
}

function lastMenu()
{
    last_step = 1;
    //Show menu
    new Ajax.Request('/ajax/facebook_template.php',
    {
        method:'post',
        onSuccess: function(data) {

            var div = document.createElement('div');
            div.id = 'pm_html_code';
            document.body.appendChild(div);
            div.innerHTML = data.responseText;

            $('btn').onclick = function() {
                if(submitExist())
                    window.location.reload();
            }
            $('btnsignup').onclick = function() {
                if(submiRegnew())
                    window.location.reload();
            }

            FB.api('/me', function(user) {
                if(user != null) {
                    if(user.id)
                        $('facebook_ava').src = 'http://graph.facebook.com/' + user.id + '/picture';
                    else
                        $('facebook_ava').src = 'http://img.informer.com/images/ava48.gif';

                    $('facebook_name').innerHTML = user.name;

                    var atitles = $$('td.after_title');
                    atitles[0].innerHTML = user.email;
                    atitles[1].innerHTML = user.gender.capitalize();
                    $('femail').value = user.email;
                    $('fgender').value = user.gender.capitalize();
                }

            });
        }
    });
}

function submiRegnew()
{
    $('error_msg').innerHTML = '<img src="http://img.informer.com/images/loading1.gif">';
    var fbName = $('facebook_name').innerHTML;
    var fbEmail = $('femail').value;
    var fbGender = $('fgender').value;
    var useAvatar = $('avatar_use').checked ? 1 : 0;

    var action = 'regFbNew';
    var vars = 'action=' + action + '&name=' + fbName+'&email='+fbEmail+'&gender='+fbGender+'&useavatar='+useAvatar;

    new Ajax.Request('/ajax/facebook.php?' + vars,
    {
        method: 'get',
        onSuccess: function(transport)
        {
            if (transport.responseText == '')
            {
                $('pm_html_code').style.display = "none";
                return true;
            }
            return false;
        }
    });
}

function submitExist()
{
    $('error_msg').innerHTML = '<img src="http://img.informer.com/images/loading1.gif">';
    var fbSiEmail = $('siEmail').value;
    var fbSiPass = $('siPassword').value.md5();
    var action = 'confirmSiFb';
    var vars = 'action=' + action + '&email=' + fbSiEmail + '&pass=' + fbSiPass;

    new Ajax.Request('/ajax/facebook.php?' + vars,
    {
        method: 'get',
        onSuccess: function(transport)
        {
            var resp = transport.responseText.toString();
            if (resp == 'bademailorpass')
            {
                $('error_msg').innerHTML = 'Wrong email or password';
            }
            if (resp == 'banned')
            {
                $('error_msg').innerHTML = 'This account is banned';
            }
            if (resp == 'ok')
            {
                $('pm_html_code').style.display = "none";
                return true;
            }
            return false;
        }
    });
}

function logout()
{
    try
    {
        var href = window.location.toString();
        href.replace('#', '');
        FB.logout(function() {window.location ='http://' + site_url + '/logout.html';});
    }
    catch (exc)
    {
       window.location = href + 'logout.html';
    }
}

function closeFbLinkedMenu()
{
    document.body.removeChild($('pm_html_code'));

    var action = 'removeDisplayConfirmCookie';
    var vars = 'action='+action;
    new Ajax.Request('/ajax/facebookactions.php?' + vars,
    {
        method: 'get',
        onSuccess: function(transport)
        {
            return false;
        }
    });
}

function validateEmail(elementValue)
{
    var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    return emailPattern.test(elementValue);
}

(function(){

    var md5 =
    {
        hexcase : 0,  		/* hex output format. 0 - lowercase; 1 - uppercase        */
        b64pad  : "", 		/* base-64 pad character. "=" for strict RFC compliance   */
        chrsz   : 8, 		/* bits per input character. 8 - ASCII; 16 - Unicode      */

        /**
* These are the functions you'll usually want to call
* They take string arguments and return either hex or base-64 encoded strings
*/
        'hex_md5' : function(s)
        {
            return this.binl2hex(this.core_md5(this.str2binl(s), s.length * this.chrsz));
        },

        'b64_md5' : function(s)
        {
            return this.binl2b64(this.core_md5(this.str2binl(s), s.length * this.chrsz));
        },

        'str_md5' : function(s)
        {
            return this.binl2str(this.core_md5(this.str2binl(s), s.length * this.chrsz));
        },

        'hex_hmac_md5' : function(key, data)
        {
            return this.binl2hex(this.core_hmac_md5(key, data));
        },

        'b64_hmac_md5' : function(key, data)
        {
            return this.binl2b64(this.core_hmac_md5(key, data));
        },

        'str_hmac_md5' : function(key, data)
        {
            return this.binl2str(this.core_hmac_md5(key, data));
        },

        /**
* Calculate the MD5 of an array of little-endian words, and a bit length.
*
*/
        'core_md5' : function(x, len)
        {
            x[len >> 5] |= 0x80 << ((len) % 32);
            x[(((len + 64) >>> 9) << 4) + 14] = len;

            var a =  1732584193;
            var b = -271733879;
            var c = -1732584194;
            var d =  271733878;

            for(var i = 0; i < x.length; i += 16)
            {
                var olda = a;
                var oldb = b;
                var oldc = c;
                var oldd = d;

                a = this.md5_ff(a, b, c, d, x[i+ 0], 7 , -680876936);
                d = this.md5_ff(d, a, b, c, x[i+ 1], 12, -389564586);
                c = this.md5_ff(c, d, a, b, x[i+ 2], 17,  606105819);
                b = this.md5_ff(b, c, d, a, x[i+ 3], 22, -1044525330);
                a = this.md5_ff(a, b, c, d, x[i+ 4], 7 , -176418897);
                d = this.md5_ff(d, a, b, c, x[i+ 5], 12,  1200080426);
                c = this.md5_ff(c, d, a, b, x[i+ 6], 17, -1473231341);
                b = this.md5_ff(b, c, d, a, x[i+ 7], 22, -45705983);
                a = this.md5_ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
                d = this.md5_ff(d, a, b, c, x[i+ 9], 12, -1958414417);
                c = this.md5_ff(c, d, a, b, x[i+10], 17, -42063);
                b = this.md5_ff(b, c, d, a, x[i+11], 22, -1990404162);
                a = this.md5_ff(a, b, c, d, x[i+12], 7 ,  1804603682);
                d = this.md5_ff(d, a, b, c, x[i+13], 12, -40341101);
                c = this.md5_ff(c, d, a, b, x[i+14], 17, -1502002290);
                b = this.md5_ff(b, c, d, a, x[i+15], 22,  1236535329);

                a = this.md5_gg(a, b, c, d, x[i+ 1], 5 , -165796510);
                d = this.md5_gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
                c = this.md5_gg(c, d, a, b, x[i+11], 14,  643717713);
                b = this.md5_gg(b, c, d, a, x[i+ 0], 20, -373897302);
                a = this.md5_gg(a, b, c, d, x[i+ 5], 5 , -701558691);
                d = this.md5_gg(d, a, b, c, x[i+10], 9 ,  38016083);
                c = this.md5_gg(c, d, a, b, x[i+15], 14, -660478335);
                b = this.md5_gg(b, c, d, a, x[i+ 4], 20, -405537848);
                a = this.md5_gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
                d = this.md5_gg(d, a, b, c, x[i+14], 9 , -1019803690);
                c = this.md5_gg(c, d, a, b, x[i+ 3], 14, -187363961);
                b = this.md5_gg(b, c, d, a, x[i+ 8], 20,  1163531501);
                a = this.md5_gg(a, b, c, d, x[i+13], 5 , -1444681467);
                d = this.md5_gg(d, a, b, c, x[i+ 2], 9 , -51403784);
                c = this.md5_gg(c, d, a, b, x[i+ 7], 14,  1735328473);
                b = this.md5_gg(b, c, d, a, x[i+12], 20, -1926607734);

                a = this.md5_hh(a, b, c, d, x[i+ 5], 4 , -378558);
                d = this.md5_hh(d, a, b, c, x[i+ 8], 11, -2022574463);
                c = this.md5_hh(c, d, a, b, x[i+11], 16,  1839030562);
                b = this.md5_hh(b, c, d, a, x[i+14], 23, -35309556);
                a = this.md5_hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
                d = this.md5_hh(d, a, b, c, x[i+ 4], 11,  1272893353);
                c = this.md5_hh(c, d, a, b, x[i+ 7], 16, -155497632);
                b = this.md5_hh(b, c, d, a, x[i+10], 23, -1094730640);
                a = this.md5_hh(a, b, c, d, x[i+13], 4 ,  681279174);
                d = this.md5_hh(d, a, b, c, x[i+ 0], 11, -358537222);
                c = this.md5_hh(c, d, a, b, x[i+ 3], 16, -722521979);
                b = this.md5_hh(b, c, d, a, x[i+ 6], 23,  76029189);
                a = this.md5_hh(a, b, c, d, x[i+ 9], 4 , -640364487);
                d = this.md5_hh(d, a, b, c, x[i+12], 11, -421815835);
                c = this.md5_hh(c, d, a, b, x[i+15], 16,  530742520);
                b = this.md5_hh(b, c, d, a, x[i+ 2], 23, -995338651);

                a = this.md5_ii(a, b, c, d, x[i+ 0], 6 , -198630844);
                d = this.md5_ii(d, a, b, c, x[i+ 7], 10,  1126891415);
                c = this.md5_ii(c, d, a, b, x[i+14], 15, -1416354905);
                b = this.md5_ii(b, c, d, a, x[i+ 5], 21, -57434055);
                a = this.md5_ii(a, b, c, d, x[i+12], 6 ,  1700485571);
                d = this.md5_ii(d, a, b, c, x[i+ 3], 10, -1894986606);
                c = this.md5_ii(c, d, a, b, x[i+10], 15, -1051523);
                b = this.md5_ii(b, c, d, a, x[i+ 1], 21, -2054922799);
                a = this.md5_ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
                d = this.md5_ii(d, a, b, c, x[i+15], 10, -30611744);
                c = this.md5_ii(c, d, a, b, x[i+ 6], 15, -1560198380);
                b = this.md5_ii(b, c, d, a, x[i+13], 21,  1309151649);
                a = this.md5_ii(a, b, c, d, x[i+ 4], 6 , -145523070);
                d = this.md5_ii(d, a, b, c, x[i+11], 10, -1120210379);
                c = this.md5_ii(c, d, a, b, x[i+ 2], 15,  718787259);
                b = this.md5_ii(b, c, d, a, x[i+ 9], 21, -343485551);

                a = this.safe_add(a, olda);
                b = this.safe_add(b, oldb);
                c = this.safe_add(c, oldc);
                d = this.safe_add(d, oldd);
            }
            return Array(a, b, c, d);
        },

        /**
* These functions implement the four basic operations the algorithm uses.
*
*/
        'md5_cmn' : function(q, a, b, x, s, t)
        {
            return this.safe_add(this.bit_rol(this.safe_add(this.safe_add(a, q), this.safe_add(x, t)), s),b);
        },

        'md5_ff' : function(a, b, c, d, x, s, t)
        {
            return this.md5_cmn((b & c) | ((~b) & d), a, b, x, s, t);
        },

        'md5_gg' : function(a, b, c, d, x, s, t)
        {
            return this.md5_cmn((b & d) | (c & (~d)), a, b, x, s, t);
        },

        'md5_hh' : function(a, b, c, d, x, s, t)
        {
            return this.md5_cmn(b ^ c ^ d, a, b, x, s, t);
        },

        'md5_ii' : function(a, b, c, d, x, s, t)
        {
            return this.md5_cmn(c ^ (b | (~d)), a, b, x, s, t);
        },

        /**
* Calculate the HMAC-MD5, of a key and some data.
*
*/
        'core_hmac_md5' : function(key, data)
        {
            var bkey = this.str2binl(key);
            if(bkey.length > 16)
                bkey = this.core_md5(bkey, key.length * this.chrsz);

            var ipad = Array(16), opad = Array(16);
            for(var i = 0; i < 16; i++)
            {
                ipad[i] = bkey[i] ^ 0x36363636;
                opad[i] = bkey[i] ^ 0x5C5C5C5C;
            }

            var hash = this.core_md5(ipad.concat(this.str2binl(data)), 512 + data.length * this.chrsz);
            return this.core_md5(opad.concat(hash), 512 + 128);
        },


        /**
* Add integers, wrapping at 2^32. This uses 16-bit operations internally
* to work around bugs in some JS interpreters.
*
*/
        'safe_add' : function(x, y)
        {
            var lsw = (x & 0xFFFF) + (y & 0xFFFF);
            var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
            return (msw << 16) | (lsw & 0xFFFF);
        },

        /**
* Bitwise rotate a 32-bit number to the left.
*
*/
        'bit_rol' : function(num, cnt)
        {
            return (num << cnt) | (num >>> (32 - cnt));
        },

        /**
* Convert a string to an array of little-endian words.
* If this.chrsz is ASCII, characters >255 have their hi-byte silently ignored.
*
*/
        'str2binl' : function(str)
        {
            var bin = Array();
            var mask = (1 << this.chrsz) - 1;
            for(var i = 0; i < str.length * this.chrsz; i += this.chrsz)
                bin[i>>5] |= (str.charCodeAt(i / this.chrsz) & mask) << (i%32);
            return bin;
        },

        /**
* Convert an array of little-endian words to a string
*
*/
        'binl2str' : function(bin)
        {
            var str = "";
            var mask = (1 << this.chrsz) - 1;
            for(var i = 0; i < bin.length * 32; i += this.chrsz)
                str += String.fromCharCode((bin[i>>5] >>> (i % 32)) & mask);
            return str;
        },

        /**
* Convert an array of little-endian words to a hex string.
*
*/
        'binl2hex' : function(binarray)
        {
            var hex_tab = this.hexcase ? "0123456789ABCDEF" : "0123456789abcdef";
            var str = "";
            for(var i = 0; i < binarray.length * 4; i++)
            {
                str += hex_tab.charAt((binarray[i>>2] >> ((i%4)*8+4)) & 0xF) +
                hex_tab.charAt((binarray[i>>2] >> ((i%4)*8  )) & 0xF);
            }
            return str;
        },

        /**
* Convert an array of little-endian words to a base-64 string
*
*/
        'binl2b64' : function(binarray)
        {
            var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
            var str = "";
            for(var i = 0; i < binarray.length * 4; i += 3)
            {
                var triplet = (((binarray[i   >> 2] >> 8 * ( i   %4)) & 0xFF) << 16)
                | (((binarray[i+1 >> 2] >> 8 * ((i+1)%4)) & 0xFF) << 8 )
                |  ((binarray[i+2 >> 2] >> 8 * ((i+2)%4)) & 0xFF);
                for(var j = 0; j < 4; j++)
                {
                    if(i * 8 + j * 6 > binarray.length * 32)
                        str += this.b64pad;
                    else
                        str += tab.charAt((triplet >> 6*(3-j)) & 0x3F);
                }
            }
            return str;
        }
    };

    if(!String.prototype.md5)
        String.prototype.md5 = function()
        {
            return md5.hex_md5(this);
        }
})();
