var CATEGORY = -1;
var RESOURCE_TYPE = -1;
var TOTAL_RESOURCE_COUNT = 0;
var CURRENT_PAGE = 1;
var FEATURE_NAME = 'all';
var CURRENT_LOCALE = 'en';
var SEARCH_TERM = '';

var user_agent = navigator.userAgent.toLowerCase();
var isVista = user_agent.indexOf("windows nt 6.0") >= 0;
var isWin7 = user_agent.indexOf("windows nt 6.1") >= 0;
var is32Bit = true;
var is64Bit = user_agent.indexOf("win64") >= 0 || user_agent.indexOf("wow64") >= 0;

var ie9UpgradeLink = 'http://microsoft.com/ie9';
var ie9_download_url = 'http://microsoft.com/ie9';
var is_en = (window.location.pathname.indexOf('/en') >= 0 || window.location.pathname.indexOf('/us') >= 0 || window.location.pathname == '/');
var is_cn = (window.location.pathname.indexOf('/cn') >= 0 || window.location.pathname.indexOf('/cn') >= 0 || window.location.pathname == '/');
var isIE9 = user_agent.indexOf('msie 9') >= 0;

var retrieved = {};
$(document).ready(function () {
    InitializeBubbles();
    CURRENT_LOCALE = $("html").attr('lang');
    // Disable console logging
    //if (typeof console == "undefined" || typeof console.log == "undefined") { var console = { log: function () { } }; }

    //setIE9DownloadLink(CURRENT_LOCALE);
});

function initHolidayOffers() {
    var holiday_pane = $('#holiday_pane');
    var is_en_pin_site_section = (window.location.pathname.indexOf('en/pinnedsites/default.aspx') >= 0 || window.location.pathname.indexOf('us/pinnedsites/default.aspx') >= 0);
    var is_cn_pin_site_section = window.location.pathname.indexOf('cn/pinnedsites/default.aspx') >= 0;
    var is_en_home_page = (window.location.pathname == '/en/' || window.location.pathname == '/en' || window.location.pathname == '/us/' || window.location.pathname == '/us' || window.location.pathname == '/');
    var is_cn_home_page = (window.location.pathname == '/cn/' || window.location.pathname == '/cn');

    if ((isWin7 && isIE9) && (is_en_pin_site_section || is_cn_pin_site_section || is_en_home_page || is_cn_home_page)) {
        $("#holiday_tag").fadeOut();

        holiday_pane.addClass('hasIE9');

        $('#scrolling_marquee').animate({ top: '+=326px' }, 'slow');
        holiday_pane.slideDown('slow', function (elm) {
           $("#holiday_tag_big").fadeTo('slow', 1);
        });
    }
}

function hideHolidayOffers() {
    $('#holiday_pane div:first').fadeTo('slow', 0, function () {
        $('#holiday_pane').slideUp('slow');
        $('#scrolling_marquee').animate({ top: '-=326px' }, 'slow');
        $("#holiday_tag").fadeIn("slow");
    })

}

function setIE9DownloadLink(CURRENT_LOCALE) {
    var rootLocation = $("html").attr('data-rootlocation');
    var slash = rootLocation.search(/^\//) > -1 ? '' : '/';
    ie9UpgradeLink = slash + rootLocation + 'getie9.aspx';
    //$("#message_download a.download").removeAttr('onclick');

    if (!isWin7 && !isVista) {
        $("#message_download a.download").attr('style', 'display: none;');
    };

    $("#message_download a.download").attr('href', ie9UpgradeLink);
};

function setIE9OneClickDownloadForDenmark(link) {
    var download_url;
    download_url = 'http://view.atdmt.com/action/ms6dke_IE9HentNuKnapDel2_1';
    $(link).attr('href', download_url);
};

function setIE9OneClickDownload(link, bingMsnDefault, locale) {
    var download_url;
    var linkPre, linkLocale;
    if (bingMsnDefault) {
        linkPre = 'http://g.msn.com/1me10IE9';
        switch (locale) {
            case 'en-us':
            case 'en-bg':
            case 'en-au':
            case 'en-ca':
            case 'fr-fr':
            case 'fra-cf':
            case 'de-de':
            case 'zh-cn':
                linkLocale = locale.replace('-', '').toUpperCase();
                break;
            case 'br-br':
                linkLocale = 'PTBR';
                break;
            case 'il-he':
                linkLocale = 'HEIL01';
                break;
            case 'es-la':
                linkLocale = 'ESLATAM01';
                break;
            case 'ch-ch':
                linkLocale = 'DEDE';
                break;
            case 'nl-bd':
                linkLocale = 'NLBE01';
                break;
            case 'fr-bf':
                linkLocale = 'FRBE01';
                break;
            case 'da-da':
                linkLocale = 'DADK01';
                break;
            case 'no-no':
                linkLocale = 'NBNO01';
                break;
            case 'jp-jp':
                linkLocale = 'JAJP';
                break;
            case 'kr-kr':
                linkLocale = 'KOKR01';
                break;
            case 'ct-ct':
                linkLocale = 'ZHTW01';
                break;
            case 'cn-cn':
                linkLocale = 'ZHCN';
                break;
            case 'ct-hk':
                linkLocale = 'ZHHK01';
                break;
            case 'ar-ar':
                linkLocale = 'ARXA01';
                break;
            case 'ct-tw':
                linkLocale = 'ZHTW01';
                break;
            case 'bg-bg':
            case 'hr-hr':
            case 'cs-cz':
            case 'et-ee':
            case 'hu-hu':
            case 'ro-ro':
            case 'sk-sk':
            case 'sl-si':
            case 'se-se':
            case 'uk-ua':
                linkLocale = 'ENGB';
                break;
            default:
                linkLocale = locale.replace('-', '').toUpperCase() + '01';
                break;
        };

        if (isVista && is64Bit) {
            download_url = linkPre + linkLocale + '/113';
        }
        else if (isWin7 && is64Bit) {
            download_url = linkPre + linkLocale + '/114';
        }
        else if (isVista && is32Bit) {
            download_url = linkPre + linkLocale + '/111';
        }
        else if (isWin7 && is32Bit) {
            download_url = linkPre + linkLocale + '/112';
        };
    }
    else {
        linkPre = 'http://download.microsoft.com/download/';
        var linkGuid = '8/6/D/86DB5DC9-5706-4A5B-BD46-FFBA6FA67D44';
        switch (locale) {
            case 'jp-jp':
                linkGuid = '0/E/D/0ED3F884-4FF3-4D8B-8FF4-ECB0DACB2F09';
                linkLocale = 'jpn';
                break;
            case 'en-us':
            case 'en-in':
            case 'en-gb':
            case 'en-au':
            case 'en-ca':
                linkLocale = 'enu';
                break;
            case 'ar-ar':
                linkLocale = 'ara';
                break;
            case 'bg-bg':
                linkLocale = 'bgr';
                break;
            case 'cn-cn':
                linkLocale = 'chs';
                break;
            case 'hk':
                linkLocale = 'zhh';
                break;
            case 'ct-tw':
                linkLocale = 'cht';
                break;
            case 'hr-hr':
                linkLocale = 'hrv';
                break;
            case 'cs-cz':
                linkLocale = 'csy';
                break;
            case 'da-da':
                linkLocale = 'dan';
                break;
            case 'nl-nl':
            case 'nl-bd':
                linkLocale = 'nld';
                break;
            case 'et-ee':
                linkLocale = 'eti';
                break;
            case 'fi-fi':
                linkLocale = 'fin';
                break;
            case 'fr-fr':
            case 'fr-bf':
            case 'fra-cf':
                linkLocale = 'fra';
                break;
            case 'de-de':
            case 'ch-ch':
                linkLocale = 'deu';
                break;
            case 'el-gr':
                linkLocale = 'ell';
                break;
            case 'il-he':
                linkLocale = 'heb';
                break;
            case 'hu-hu':
                linkLocale = 'hun';
                break;
            case 'it-it':
                linkLocale = 'ita';
                break;
            case 'kr-kr':
                linkLocale = 'kor';
                break;
            case 'no-no':
                linkLocale = 'nor';
                break;
            case 'pl-pl':
                linkLocale = 'plk';
                break;
            case 'br-br':
                linkLocale = 'ptb';
                break;
            case 'ro-ro':
                linkLocale = 'rom';
                break;
            case 'ru-ru':
                linkLocale = 'rus';
                break;
            case 'sk-sk':
                linkLocale = 'sky';
                break;
            case 'sl-sl':
                linkLocale = 'slv';
                break;
            case 'es-es':
            case 'es-la':
            case 'es-mx':
                linkLocale = 'esn';
                break;
            case 'se-se':
                linkLocale = 'sve';
                break;
            case 'tr-tr':
                linkLocale = 'trk';
                break;
            case 'uk-ua':
                linkLocale = 'ukr';
                break;
        }

        if (isVista && is64Bit) {
            download_url = linkPre + linkGuid + '/IE9-WindowsVista-x64-' + linkLocale + '.exe';
        }
        else if (isWin7 && is64Bit) {
            download_url = linkPre + linkGuid + '/IE9-Windows7-x64-' + linkLocale + '.exe';
        }
        else if (isVista && is32Bit) {
            download_url = linkPre + linkGuid + '/IE9-WindowsVista-x86-' + linkLocale + '.exe';
        }
        else if (isWin7 && is32Bit) {
            download_url = linkPre + linkGuid + '/IE9-Windows7-x86-' + linkLocale + '.exe';
        };
    };


    $(link).attr('href', download_url);
};

// Browser Detection ------------------------- //

var userAgent = navigator.userAgent;

var win = userAgent.search(/windows/i);
var win7 = userAgent.search(/nt 6.1/i);
var ie9 = userAgent.search(/msie 9/i);
var ie8 = userAgent.search(/msie 8/i);
var ie7 = userAgent.search(/msie 7/i);

// Secondary Header text string
function BrowserDetectionPinnedSites() {
    // User has Win7 and IE9

    if (win7 != "-1" && ie9 != "-1") {
        $('#new_since_beta').remove();
        //$('#message_feedback').show();
        //$('#logo').hide();
        //$('#logo img').hide();
        IE9AnimateCall();
        //add an "if not homepage": hide everything.
    }
    // User has Win7 but not IE9
    else if (win7 != "-1" && ie9 == "-1") {
        var html = '<a href="' + ie9UpgradeLink + '" accesskey="1">' + IE9GETIE9 + '</a>';
        $('#message_download').show();
        IE9AnimateCall();
        GetIE9AnimateAdmin();
    }
    // User has Windows but not Windows 7
    else if (win != "-1") {
        var html = '<a href="' + ie9UpgradeLink + '" accesskey="1">' + IE9GETWIN7 + '</a>';
        $('#message_download').show();
        IE9AnimateCall();
        GetIE9AnimateAdmin();
    }
    // User does not have Windows
    else {
        var html = '<a href="' + ie9UpgradeLink + '" accesskey="1">' + IE9GETBOTH + '</a>';
        $('#new_since_beta div').html(html);
        $('#message_download').show();
        GetIE9AnimateAdmin();
    }
    $('#new_since_beta div').html(html);
}

function BrowserDetectionPinnedSites_Homepage() {
    // Add for Holiday, delete after december
    if (win7 != "-1" && ie9 != "-1" && is_en) {
        $('#new_since_beta').remove();
        $('#message_feedback').show();
        IE9AnimateCall();
        //add an "if not homepage": hide everything.
    }
    //Holiday:  Delete above code after december
    // User has Win7 and IE9
    else if (win7 != "-1" && ie9 != "-1") {
        $('#ie9logo').show();
        $('#ie9logo img').show();
        $('#new_since_beta').remove();
        $('#message_feedback').show();
        IE9AnimateCall();
        //add an "if not homepage": hide everything.
    }
    // User has Win7 but not IE9
    else if (win7 != "-1" && ie9 == "-1") {
        var html = '<a href="' + ie9UpgradeLink + '" accesskey="1">' + IE9GETIE9 + '</a>';
        $('#message_download').show();
        IE9AnimateCall();
    }
    // User has Windows but not Windows 7
    else if (win != "-1") {
        var html = '<a href="' + ie9UpgradeLink + '" accesskey="1">' + IE9GETWIN7 + '</a>';
        $('#message_download').show();
        IE9AnimateCall();
    }
    // User does not have Windows
    else {
        var html = '<a href="' + ie9UpgradeLink + '" accesskey="1">' + IE9GETBOTH + '</a>';
        $('#new_since_beta div').html(html);
        $('#message_download').show();
    }
    $('#new_since_beta div').html(html);
}


function IE9AnimateCall() {
    setTimeout(function () {
        //$('.section_tpl #logo').remove();  //Nope, we show it in TPL now.
        if (window.location.pathname.toLowerCase().search("getie9.aspx") < 0) {
            $('#logo').fadeIn('slow');
        }
    }, 2000);
}

function GetIE9AnimateAdmin() {
    setTimeout(function () {
        $('#accountpage .page').animate({ height: $('#accountpage .page').height() + 50 }, 'slow');
    }, 2000);
}

// Installing Search Provider
function BrowserDetectionAddonInstallSearch(download_url, resie7) {
    if (ie8 != "-1" || ie9 != "-1") {
        window.external.AddSearchProvider(download_url);
    }
    else if (ie7 != "-1" && resie7 == 'True') {
        window.external.AddSearchProvider(download_url);
    } else {
        $('#addon_error').fadeIn('slow');
        $('#addon_error .close').click(function () {
            $('#addon_error').fadeOut('slow');
        });
    }
}

// Installing Accelerator
function BrowserDetectionAddonInstallAccelerator(download_url) {
    if (ie8 != "-1" || ie9 != "-1") {
        window.external.AddService(download_url);
    }
    else {
        $('#addon_error, #overlay').fadeIn('slow');
        $('#addon_error .close').click(function () {
            $('#addon_error, #overlay').fadeOut('slow');
        });
    }
}

// Installing Web Slice
function BrowserDetectionAddonInstallWebslice(isLink, name, download_url) {
    if (ie8 != "-1" || ie9 != "-1") {
        if (isLink) {
            window.open(download_url);
        }
        else {
            window.external.addToFavoritesBar(download_url, name, 'slice');
        }
    }
    else {
        $('#addon_error').fadeIn('slow');
        $('#addon_error .close').click(function () {
            $('#addon_error').fadeOut('slow');
        });
    }
}

// Installing Pinned Sites
function BrowserDetectionPinnedInstall() {
    $('#pinning_icon img').mousedown(function () {
        if (ie9 != "-1") {
            // User is running IE9 - lets install
        }
        else {
            $('#pinned_error').fadeIn('slow');
        }
    });
}

function BrowserDetectionTplGallery() {

    // User has IE9
    if (ie9 != "-1") {
        $('#new_since_beta').remove();
        //$('#message_feedback').show();
        IE9AnimateCall();
    }
    else {
        var html = '<a href="' + ie9UpgradeLink + '">' + IE9GETIE9TPL + '</a>';
        $('#message_download a.download').attr('href', ie9UpgradeLink);
        $('#message_download').show();
        IE9AnimateCall();
    }
    $('#new_since_beta div').html(html);
}
// Installing TPL
function BrowserDetectionTPLInstall(url, title, id) {
    Analytics.trackDownload(title, 'TPL', '', id);
    if (ie9 != "-1") {
        // User is running IE9 - lets install
        window.external.msAddTrackingProtectionList(url, title);
    }
    else {
        $('#addon_error h1').html('Tracking Protection Lists require <strong>Internet Explorer 9</strong>. <a href="http://microsoft.com/ie9">Download it now</a>');
        $('#addon_error').fadeIn('fast');
        $('#addon_error .close').click(function () {
            $('#addon_error').fadeOut('fast');
        });
    }
    return false;
}
// End Browser Detection -- //

function InitializeHomepage() {
    BrowserDetectionPinnedSites_Homepage();
    InitializeBubbles();
    setTimeout(function () {
        var bezier_params_left = {
            start: { x: -1000, y: 1000, angle: 10 },
            end: { x: 0, y: 542, angle: -40, length: 0.25 }
        }
        var bezier_params_right = {
            start: { x: 1000, y: 1000, angle: 10 },
            end: { x: 0, y: 542, angle: 40, length: 0.25 }
        }
        //$('.path.left').animate({ path: new $.path.bezier(bezier_params_left) }, 1000);
        //$('.path.right').animate({ path: new $.path.bezier(bezier_params_right) }, 1000);
        //$('.path.center').animate({ 'top': '542px' }, 1000, function () { InitializeTitles(); InitializeMarquee(); InitializeCallToAction(); });
        InitializeTitles();
        InitializeMarquee();
        InitializeCallToAction();
    }, 750);
    InitializeLearnMorePinned();
}

function InitializeTitles() {
    setTimeout(function () {
        $('#featured_sites, #view_pinned_gallery').fadeIn('slow');
    }, 250);
}

function InitializeMarquee() {
    setTimeout(function () {
        $('#home_marquee').animate(
			{ 'top': '+=530px' },
			{ duration: 1000, easing: 'easeInOutBack' }
		);
    }, 1000);

    $('.exhibit').hover(function () {
        $(this).addClass('active');
        $('.exhibit.active a img.thumbnail').show();
    }, function () {
        $(this).removeClass('active');
        $('.exhibit a img.thumbnail').hide();
    });
}

function InitializeCallToAction() {
    setTimeout(function () {
        $('#new_since_beta').animate(
			{ 'top': '+=30px' },
			{ duration: 500, easing: 'easeInOutQuad' }
		);
    }, 2000);
}

function InitializeLearnMorePinned() {
    // Set and check cookies
    var hasViewedLearnMorePinned = ($.cookie("viewedLearnMorePinned")) ? true : false;
    if (hasViewedLearnMorePinned) {
        $('#learn_more_pinned').fadeIn('fast');
        LearnMorePinnedModal();
        return false;
    } else {
        $.cookie("viewedLearnMorePinned", true, { expires: 365 });
    }

    setTimeout(function () {
        $('#featured_sites, #home_marquee, #view_pinned_gallery, .path').animate(
			{ 'top': '+=515px' },
			{ duration: 1000, easing: 'easeInOutBack' }
		);
        $('footer.global').animate(
			{ 'top': '+=120px' },
			{ duration: 1000 }
		);
        $('#wrapper_inner').animate(
			{ 'padding-bottom': '+=395px' },
			{ duration: 1000 }
		);
        setTimeout(function () {
            $('#how_to_pin').fadeIn('fast');
        }, 1000);
        return false;
    }, 5000);
    $('#how_to_pin .close').click(function () {
        $('#how_to_pin').fadeOut('fast');
        setTimeout(function () {
            $('#featured_sites, #home_marquee, #view_pinned_gallery, .path').animate(
				{ 'top': '-=515px' },
				{ duration: 1000, easing: 'easeInOutBack' }
			);
            $('footer.global').animate(
				{ 'top': '-=120px' },
				{ duration: 1000 }
			);
            $('#wrapper_inner').animate(
				{ 'padding-bottom': '-=395px' },
				{ duration: 1000 }
			);
            $('#learn_more_pinned').fadeIn('fast');
        }, 1000);
        return false;
    });

    LearnMorePinnedModal();
}

function InitializeLearnMoreAddons() {
    $('#learn_more_activate').click(function () {
        $('#browse_learn_more, #overlay').fadeIn('slow');
        return false;
    });

    $('#browse_learn_more .close').click(function () {
        $('#browse_learn_more, #overlay').fadeOut('slow');
        return false;
    });

    $('#browse_learn_more ul li').click(function () {
        if (!$(this).hasClass('active')) {
            $(this).addClass('animating');
            $('#browse_learn_more ul li.active p').slideUp('fast', function () {
                $('#browse_learn_more ul li.active').removeClass('active');
            });
            $('#browse_learn_more ul li.animating').find('p').slideDown('fast', function () {
                $('#browse_learn_more ul li.animating').removeClass('animating').addClass('active');
            });
        };
    });


}

function LearnMorePinnedModal() {

    $('#learn_more_pinned').unbind('click.learnmore');
    $('#learn_more_pinned').bind('click.learnmore', function () {
        $('#browse_learn_more, #overlay').fadeIn('slow');
        return false;
    });

    $('#browse_learn_more .close, #browse_learn_more .pseudoclose').unbind('click.learnmore');
    $('#browse_learn_more .close, #browse_learn_more .pseudoclose').bind('click.learnmore', function () {
        $('#browse_learn_more, #overlay').fadeOut('slow');
        return false;
    });

    $('#browse_learn_more .column ul li').unbind('click.learnmore');
    $('#browse_learn_more .column ul li').bind('click.learnmore', function () {
        var index = $('#browse_learn_more .column ul li').index(this);
        $('#browse_learn_more .column ul li.active, #learn_more_images img.active').removeClass('active');
        $('#browse_learn_more .column ul li:eq(' + index + '), #learn_more_images img:eq(' + index + ')').addClass('active');
    });

    $("a#download_ie9_modal_link").unbind('click.learnmore');
    $("a#download_ie9_modal_link").bind('click.learnmore', function (itm) {
        var offset = $("#content").offset();
        var content_left = offset.left + 225;
        var content_top = offset.top + 117;
        $("#download_ie9_modal").css('left', content_left.toString() + 'px');
        $("#download_ie9_modal").css('top', content_top.toString() + 'px');
        $('#download_ie9_modal, #overlay').fadeIn('slow');
        return false;
    });

    $("#download_ie9_modal a.close").unbind('click.learnmore');
    $("#download_ie9_modal a.close").bind('click.learnmore', function (itm) {
        $('#download_ie9_modal, #overlay').fadeOut('slow');
        return false;
    });
}

function AddonUploadModal() {
    $('#upload_form.addon form .typebuttons p.accelerator').click(function () {
        $('#upload_form.addon form .typebuttons p').removeClass('active');
        $('#upload_form.addon form .typebuttons p').addClass('unactive');
        $('#upload_form.addon form .typebuttons p.accelerator').addClass('active');
        $('#upload_form.addon form .typebuttons p.accelerator').removeClass('unactive');
        $('#upload_form.addon fieldset.type').removeClass('active');
        $('#upload_form.addon fieldset.type').addClass('unactive');
        $('#upload_form.addon fieldset.type.accelerator').addClass('active');
        $('#upload_form.addon fieldset.type.accelerator').removeClass('unactive');
        $('.typeselectbox').val("1");
    });

    $('#upload_form.addon form .typebuttons p.searchprovider').click(function () {
        $('#upload_form.addon form .typebuttons p').removeClass('active');
        $('#upload_form.addon form .typebuttons p').addClass('unactive');
        $('#upload_form.addon form .typebuttons p.searchprovider').addClass('active');
        $('#upload_form.addon form .typebuttons p.searchprovider').removeClass('unactive');
        $('#upload_form.addon fieldset.type').removeClass('active');
        $('#upload_form.addon fieldset.type').addClass('unactive');
        $('#upload_form.addon fieldset.type.searchprovider').addClass('active');
        $('#upload_form.addon fieldset.type.searchprovider').removeClass('unactive');
        $('.typeselectbox').val("2");
    });

    $('#upload_form.addon form .typebuttons p.toolbarorextension').click(function () {
        $('#upload_form.addon form .typebuttons p').removeClass('active');
        $('#upload_form.addon form .typebuttons p').addClass('unactive');
        $('#upload_form.addon form .typebuttons p.toolbarorextension').addClass('active');
        $('#upload_form.addon form .typebuttons p.toolbarorextension').removeClass('unactive');
        $('#upload_form.addon fieldset.type').removeClass('active');
        $('#upload_form.addon fieldset.type').addClass('unactive');
        $('#upload_form.addon fieldset.type.toolbarorextension').addClass('active');
        $('#upload_form.addon fieldset.type.toolbarorextension').removeClass('unactive');
        $('.typeselectbox').val("3");
    });

    $('#upload_form.addon form .typebuttons p.webslice').click(function () {
        $('#upload_form.addon form .typebuttons p').removeClass('active');
        $('#upload_form.addon form .typebuttons p').addClass('unactive');
        $('#upload_form.addon form .typebuttons p.webslice').addClass('active');
        $('#upload_form.addon form .typebuttons p.webslice').removeClass('unactive');
        $('#upload_form.addon fieldset.type').removeClass('active');
        $('#upload_form.addon fieldset.type').addClass('unactive');
        $('#upload_form.addon fieldset.type.webslice').addClass('active');
        $('#upload_form.addon fieldset.type.webslice').removeClass('unactive');
        $('.typeselectbox').val("0");
    });
}

function LearnMoreAddonsModal() {

    $('#learn_more_addons').unbind('click.learnmore');
    $('#learn_more_addons').bind('click.learnmore', function () {
        $('#browse_learn_more, #overlay').fadeIn('slow');
        return false;
    });

    $('#browse_learn_more .close, #browse_learn_more .pseudoclose').unbind('click.learnmore');
    $('#browse_learn_more .close, #browse_learn_more .pseudoclose').bind('click.learnmore', function () {
        $('#browse_learn_more, #overlay').fadeOut('slow');
        return false;
    });

    $('#browse_learn_more .column ul li').unbind('click.learnmore');
    $('#browse_learn_more .column ul li').bind('click.learnmore', function () {
        var index = $('#browse_learn_more .column ul li').index(this);
        $('#browse_learn_more .column ul li.active, #browse_learn_more .column ul p.active, #learn_more_images img.active').removeClass('active'); //not functional yet!
        $('#browse_learn_more .column ul li:eq(' + index + '), #browse_learn_more .column ul p:eq(' + index + '), #learn_more_images img:eq(' + index + ')').addClass('active');
    });
}

function InitializeBubbles() {
    $wrapper = $('#wrapper');
    if (
		$wrapper.hasClass('us') ||
		$wrapper.hasClass('ca') ||
		$wrapper.hasClass('gb') ||
		$wrapper.hasClass('in')
	) {
        $('#nav_home').find('a').removeAttr('title');
        $('#nav_pinning, #nav_addons, #nav_control').each(function () {
            $(this).hoverIntent({
                'over': function () {
                    $(this).find('a.menu-icon').css('opacity', '.5');
                    $(this).find('.bubble').stop().fadeIn(100);
                },
                'timeout': 250,
                'out': function () {
                    $(this).find('.bubble').stop().fadeOut(100);
                    $(this).find('a.menu-icon').css('opacity', '1');
                }
            });
        });
    } else {
        $('#nav_pinning, #nav_addons, #nav_control').each(function () {
            $(this).hoverIntent({
                'over': function () {
                    $(this).find('a.menu-icon').css('opacity', '.5');
                },
                'timeout': 250,
                'out': function () {
                    $(this).find('a.menu-icon').css('opacity', '1');
                }
            });
        });
    }
    $nav_home = $('#nav_home');
    $nav_home.hoverIntent({
        'over': function () { $nav_home.find('ul').stop().css('display', 'block'); },
        'timeout': 250,
        'out': function () { $nav_home.find('ul').stop().css('display', 'none') }
    });
}

function InitializeTPLs() {
    //InitializeBubbles();
    BrowserDetectionTplGallery();  //This function is now using the top-level Gallery.Master file.
    $('#tpl_gallery').load('get.aspx?startIndex=' + TPL_PAGINATION_START_INDEX + '&perPage=' + TPL_PAGINATION_PER_PAGE + '&sort=rating' + '&searchterm=' + SEARCH_TERM, function () {
        $('.tpl').each(function (index) {
            var self = this;
            setTimeout(function () {
                $(self).fadeTo('slow', 1);
            }, index * 200);
        });
    });

    LearnMoreTplModal();

    //GetMarqueeContent(0);
    //MarqueePaging();
    //MarqueeNav();
    MarqueeTPL_TypeNav();
}

function LearnMoreTplModal() {

    $('#learn_more_tpls').unbind('click.learnmore');
    $('#learn_more_tpls').bind('click.learnmore', function () {
        $('#browse_learn_more, #overlay').fadeIn('slow');
        return false;
    });

    $('#browse_learn_more .close, #browse_learn_more .pseudoclose').unbind('click.learnmore');
    $('#browse_learn_more .close, #browse_learn_more .pseudoclose').bind('click.learnmore', function () {
        $('#browse_learn_more, #overlay').fadeOut('slow');
        return false;
    });

    $('#browse_learn_more .column ul li#howtheywork').unbind('click.learnmore');
    $('#browse_learn_more .column ul li#howtheywork').bind('click.learnmore', function () {
        var unactive = $('#browse_learn_more .column ul li#howtheywork').hasClass('unactive');
        //alert(active.toString());
        if (unactive == true) {
            DeactivateManage();
            ActivateHowTheyWork();
        } else {
            DeactivateHowTheyWork();
        }
    });

    $('#browse_learn_more .column ul li#manage').unbind('click.learnmore');
    $('#browse_learn_more .column ul li#manage').bind('click.learnmore', function () {
        var unactive = $('#browse_learn_more .column ul li#manage').hasClass('unactive');
        //alert(active.toString());
        if (unactive == true) {
            DeactivateHowTheyWork();
            ActivateManage();
        } else {
            DeactivateManage();
        }
    });

    function ActivateHowTheyWork() {
        $('#browse_learn_more .column ul li#howtheywork').removeClass('unactive');
        $('#browse_learn_more .column ul li#howtheywork').addClass('active');
        $('#browse_learn_more .column ul li#howtheywork p').addClass('active');
        $('#learn_more_images img#howtheywork_img').addClass('active');
        $('#browse_learn_more .column p.toadd').removeClass('unactive');
        $('#browse_learn_more .column p.toadd').addClass('active');

    }
    function DeactivateHowTheyWork() {
        $('#browse_learn_more .column ul li#howtheywork').addClass('unactive');
        $('#browse_learn_more .column ul li#howtheywork').removeClass('active');
        $('#browse_learn_more .column ul li#howtheywork p').removeClass('active');
        $('#learn_more_images img#howtheywork_img').removeClass('active');
        $('#browse_learn_more .column p.toadd').removeClass('unactive');
        $('#browse_learn_more .column p.toadd').addClass('active');
    }
    function ActivateManage() {
        $('#browse_learn_more .column ul li#manage').removeClass('unactive');
        $('#browse_learn_more .column ul li#manage').addClass('active');
        $('#browse_learn_more .column ul li#manage p').addClass('active');
        $('#learn_more_images img#manage_img').addClass('active');
        $('#browse_learn_more .column p.toadd').removeClass('active');
        $('#browse_learn_more .column p.toadd').addClass('unactive');
    }
    function DeactivateManage() {
        $('#browse_learn_more .column ul li#manage').addClass('unactive');
        $('#browse_learn_more .column ul li#manage').removeClass('active');
        $('#browse_learn_more .column ul li#manage p').removeClass('active');
        $('#learn_more_images img#manage_img').removeClass('active');
        $('#browse_learn_more .column p.toadd').removeClass('unactive');
        $('#browse_learn_more .column p.toadd').addClass('active');
    }
}

function BindRatings() {
    $('.stardetail .starrating').addClass("star_over_sm");
    $('.stardetail .starrating').removeClass("star_over_sm");

    $('.stardetail .starrating').hover(function () {
        var hoveredStar = $(this).attr('rel');
        $.each($(this).parent("div").find(".starrating"), function (i, star) {
            if ($(star).attr('rel') <= hoveredStar) {
                $(star).addClass("star_over_sm");
            }
        });
    },
    function () {
        $(this).parent("div").find(".starrating").removeClass("star_over_sm");
    });

    $('.stardetail .starrating').click(function () {
        var self = $(this);
        var resourceId = self.closest('div.stardetail').attr('rel');
        var starval = self.attr('rel');
        var starsGroup = self.parent("div").find(".starrating");



        $.ajax({
            type: 'POST',
            url: '/handlers/RatingHandler.ashx',
            data: { ResourceId: resourceId, Stars: starval },
            error: function () {
                self.parent("div").find(".login").slideDown('fast');
            },
            success: function () {
                starsGroup.unbind('mouseover');
                starsGroup.unbind('mouseout');
                starsGroup.unbind('click');

                $.each(starsGroup, function (index, star) {
                    //                                        if ($(star).hasClass('star_on_sm')) {
                    //                                            $(star).removeClass('star_on_sm');
                    //                                            $(star).addClass('star_off_sm');
                    //                                        }
                    //                                        if ($(star).hasClass('orange_star_on_sm')) {
                    //                                            $(star).removeClass('orange_star_on_sm');
                    //                                            $(star).addClass('orange_star_off_sm');
                    //                                        }

                    $(star).removeClass('orange_star_on_sm').removeClass('orange_star_off_sm');
                    if ($(star).attr('rel') <= starval)
                        $(star).addClass('orange_star_on_sm');
                    else
                        $(star).addClass('orange_star_off_sm');

                });

                Analytics.trackEngagement('Rate Resource');

            }

        });

    });
}

function DisplayTPLPagination(totalCount) {
    $('#paging .pageTotal').text(totalCount);
    $('#paging .pageStart').text(TPL_PAGINATION_START_INDEX + 1);
    $('#paging').fadeIn('fast');

    if (TPL_PAGINATION_START_INDEX == 0) {
        $('#paging #nav_prev').addClass('disabled');
    } else {
        $('#paging #nav_prev').removeClass('disabled');
    }

    if ((TPL_PAGINATION_END_INDEX + 1) >= totalCount) {
        $('#paging .pageEnd').text(totalCount);
        $('#paging #nav_next').addClass('disabled');
    } else {
        $('#paging #nav_next').removeClass('disabled');
        $('#paging .pageEnd').text(TPL_PAGINATION_END_INDEX + 1);
    }

    $('#paging #nav_next').unbind('click');
    if ((TPL_PAGINATION_END_INDEX + 1) < totalCount) {
        $('#paging #nav_next').click(function () {
            $('#tpl_gallery').empty();
            TPL_PAGINATION_START_INDEX = TPL_PAGINATION_END_INDEX + 1;
            TPL_PAGINATION_END_INDEX = TPL_PAGINATION_START_INDEX + (TPL_PAGINATION_PER_PAGE - 1);
            $('#tpl_gallery').load('get.aspx?startIndex=' + TPL_PAGINATION_START_INDEX + '&perPage=' + TPL_PAGINATION_PER_PAGE + '&searchterm=' + SEARCH_TERM, function () {
                $('.tpl').each(function (index) {
                    var self = this;
                    setTimeout(function () {
                        $(self).fadeTo('slow', 1);
                    }, index * 200);
                });
            });
        });
    }

    $('#paging #nav_prev').unbind('click');
    if (TPL_PAGINATION_START_INDEX != 0) {
        $('#paging #nav_prev').click(function () {
            $('#tpl_gallery').empty();
            TPL_PAGINATION_START_INDEX = TPL_PAGINATION_START_INDEX - TPL_PAGINATION_PER_PAGE;
            TPL_PAGINATION_END_INDEX = TPL_PAGINATION_START_INDEX + (TPL_PAGINATION_PER_PAGE - 1);
            $('#tpl_gallery').load('get.aspx?startIndex=' + TPL_PAGINATION_START_INDEX + '&perPage=' + TPL_PAGINATION_PER_PAGE + '&searchterm=' + SEARCH_TERM, function () {
                $('.tpl').each(function (index) {
                    var self = this;
                    setTimeout(function () {
                        $(self).fadeTo('slow', 1);
                    }, index * 200);
                });
            });
        });
    }
}

function MarqueeTPL_TypeNav() {
    //a TPL-specific type navigation.
    var select = $('#nav_type select');
    select.unbind('change');
    select.change(function () {
        var selectedOption = $('#nav_type select :selected');
        var sortId = selectedOption.val();
        var sortName = selectedOption.data('type');

        $('#tpl_gallery').empty();
        TPL_PAGINATION_START_INDEX = 0;
        TPL_PAGINATION_END_INDEX = 4;
        $('#tpl_gallery').load('get.aspx?startIndex=' + TPL_PAGINATION_START_INDEX + '&perPage=' + TPL_PAGINATION_PER_PAGE + '&sort=' + sortName + '&searchterm=' + SEARCH_TERM, function () {
            $('.tpl').each(function (index) {
                var self = this;
                setTimeout(function () {
                    $(self).fadeTo('slow', 1);
                }, index * 200);
            });
        });
    });
}

function InitializeAddons() {
    InitializeBubbles();
    LearnMoreAddonsModal();
    if ($.urlParam('feature') != 0 && window.location.hash == '') {
        RESOURCE_TYPE = $('#nav_type select option[data-type=' + $.urlParam('feature') + ']').val();
    } else {
        ProcessUrlHash();
    }

    this.loadingAnim = setInterval(function () { UpdateSpinner("div.loading_spinner", 24, 288); }, 65);
    GetMarqueeContent(0);
    MarqueePaging();
    MarqueeNav();
    MarqueeTypeNav();
    InitializeLearnMoreAddons();
}

function GetMarqueeContent(index) {
    $('#scrolling_marquee').load('/' + CURRENT_LOCALE + '/' + MARQUEE_PAGE + '/get.aspx?index=' + index + '&category=' + CATEGORY + '&type=' + RESOURCE_TYPE + '&searchterm=' + SEARCH_TERM, function () {
        setTimeout(function () {
            UpdatePagingControls();
            $('div.loading_spinner').fadeOut('slow', function () {
                $('#scrolling_marquee .pane').each(function (index) {
                    var self = this;
                    setTimeout(function () {
                        $(self).fadeTo('slow', 1);
                    }, index * 100);
                });
            });
        },
		750);
        $('#scrolling_marquee .page').each(function (i) {
            $(this).attr('id', 'page-number-' + i);

        })
        $('#scrolling_marquee').prepend('<div class="page" id="spacer"></div>');
        if (TOTAL_RESOURCE_COUNT < 10) {
            var marquee_pos = ($('body').width() - 1000) / 2;
            $('#scrolling_marquee').css('left', marquee_pos + 'px');
        }
        $('#scrolling_marquee').attr('total', TOTAL_RESOURCE_COUNT);

    }, function () {
        $('#scrolling_marquee .page').each(function (i) {
        })


    })
}

function MarqueePaging() {

    $('#paging select .no_click').click(function () {
        return false;
    });

    $('#nav_prev').unbind('click');
    $('#nav_next').unbind('click');

    $('#nav_next').click(function () {
        if (!$(this).data('animating')) {
            $(this).data('animating', true);
            count = $("#scrolling_marquee .page").size();
            var marquee_width = count * 1000;
            var left = $('#scrolling_marquee').css('left');
            if (left == 'auto') {
                left = 0;
            } else {
                left = left.replace(/px/, "");
                left = parseInt(left);
            }

            var difference = marquee_width + left;
            if (difference > 1000) {
                var page = $('#nav_next').attr('data-page');
                $('.page.active').removeClass('active').addClass('inactive');
                page = parseInt(page);
                page = page + 1;
                CURRENT_PAGE = page;
                $('.page:eq(' + page + ')').removeClass('inactive').addClass('active');
                var r = (page + 1) % 4;

                if (
					r == 0 &&
					!retrieved[page] && //this page hasn't already been retrieved (if you go back then forward again)
					(($('#scrolling_marquee').attr('total') - 1) / CURRENT_PAGE > 10)) { //  it's not the last page
                    $(this).data('loading', true);
                    $(this).data('page-loading', page);

                    $('#buffer').load('/' + CURRENT_LOCALE + '/' + MARQUEE_PAGE + '/get.aspx?index=' + (((page + 1) * 10) - 1) + '&category=' + CATEGORY + '&type=' + RESOURCE_TYPE + '&searchterm=' + SEARCH_TERM, function () {
                        $('#buffer .page .pane').css('opacity', 1);
                        $('#scrolling_marquee').append($('#buffer').html());
                        $('#buffer').html('');
                        retrieved[page] = true;
                        $(this).data('loading', false);
                    });
                }
                if (!$(this).data('page-loading')) $(this).data('page-loading', 4);
                if (($('#scrolling_marquee').attr('total')) / 10 > (CURRENT_PAGE - 1)) {
                    $('#nav_next').attr('data-page', page);
                    $('#scrolling_marquee .page:eq(' + ((page == 1 || page == 2) ? 0 : page - 2) + ')').animate(
							{ 'margin-left': '-1010px' },

							{
							    duration: 1500,
							    easing: 'easeInOutBack',
							    complete: function () {
							        UpdatePagingControls();
							    }
							}

						);

                }



                setTimeout(function () {
                    $('#nav_next').removeData('animating');
                }, 1500);

            }
            else {
            }
        }
        return false;
    });

    $('#nav_prev').click(function () {
        count = $("#scrolling_marquee .page").size();
        var marquee_width = count * 1000;
        if (!$(this).data('animating')) {
            $(this).data('animating', true);
            var page = $('#nav_next').attr('data-page');
            $('.page.active').removeClass('active').addClass('inactive');
            page = parseInt(page);
            page = page - 1;
            if (page == 0) page = 1;
            CURRENT_PAGE = page;
            $('.page:eq(' + page + ')').removeClass('inactive').addClass('active');
            $('#nav_next').attr('data-page', page);
            $('#scrolling_marquee .page:eq(' + (parseInt(page) - 1) + ')').animate(
				{ 'margin-left': '0px' },
				{
				    duration: 1500,
				    easing: 'easeInOutBack',
				    complete: function () {
				        UpdatePagingControls();
				    }
				}
			);
            setTimeout(function () {
                $('#nav_prev').removeData('animating');
            }, 1500);


        }
        return false;
    });
}

function UpdatePagingControls() {
    if (CURRENT_PAGE < 1) CURRENT_PAGE = 1;



    if (CURRENT_PAGE == 1) {
        lower = 1;
        upper = 10;
        if (!totalled) {
            $('#paging #resource_count').html(TOTAL_RESOURCE_COUNT);
            var totalled = true;
        }
    } else {
        var lower = (parseInt(CURRENT_PAGE) - 1) * 10;
        var upper = lower + 10;
    }


    $('#paging #resource_lower').html(lower);
    $('#paging #resource_upper').html(upper);

    $('#paging').fadeIn('fast');
}

function MarqueeNav() {
    $('#nav_path a.navcat').unbind('click.marquee');
    $('#nav_path a.navcat').bind('click.marquee', function () {
        //ClearMarquee();

        $('#nav_next').attr('data-page', 1);
        CATEGORY = $(this).data('categoryId');
        SEARCH_TERM = '';
        window.location.hash = FEATURE_NAME + '/' + $(this).attr('title');
        ProcessUrlHash();
        var selected = $('#paging select :selected'); //this should not happen here, i'm lazy

        tag.autoscroll.search({ searchterm: SEARCH_TERM, category: CATEGORY, featuretype: selected.val() });
        //InitializeAddons();  //Why?
        return false;
    })
}

function MarqueeTypeNav() {
    var select = $('#nav_type select');
    select.unbind('change');
    select.change(function () {
        ClearMarquee();
        $('#nav_next').attr('data-page', 1);
        var selectedOption = $('#nav_type select :selected');
        var featureId = selectedOption.val();
        var featureName = selectedOption.data('type');
        window.location.hash = featureName;
        ProcessUrlHash();
        RESOURCE_TYPE = featureId;
        InitializeAddons();  //Why?
    });
}

function ClearMarquee() {
    CURRENT_PAGE = 1;
    $('div.loading_spinner').fadeIn('slow');
    $('#scrolling_marquee .page.inactive').animate(
			{ 'opacity': 0 },
			{
			    duration: 500,
			    easing: 'easeInOutBack'
			}
		);
    $('#scrolling_marquee > div > div').each(function (index) {
        var self = this;
        setTimeout(function () {
            $(self).animate(
					{ 'bottom': '-=1010px', 'opacity': 0 },
					{
					    duration: 300,
					    easing: 'easeInOutQuad'
					}
				);
        }, index * 100);
    });
}

function ProcessUrlHash() {
    var hash = window.location.hash.replace('#', '').split('/');

    if (hash.length > 0) {
        FEATURE_NAME = hash[0];
        if (FEATURE_NAME != '') {
            // Use the dropdown menu to get the resource type id
            var select_option = $("#paging select option[data-type=" + FEATURE_NAME + "]");
            RESOURCE_TYPE = select_option.val();
            select_option.attr("selected", "selected");
        }
        var category = hash[1];

        if (category != null) {
            CATEGORY = $('#nav_path li a[title=' + category + ']').data('categoryId');
        }
    }

    SetNavState();
}

function SetNavState() {
    $('#nav_path li a').removeClass('active');
    $.each($('#nav_path li a'), function () {
        if ($(this).data('categoryId') == CATEGORY) {
            $(this).addClass('active');
        }
    });
    //document.write("changing nav state")
}

/* -- Loading spinner -- */
var lastx = 0;
function UpdateSpinner(target, step, width) {
    $(target).css("background-position", lastx + "px 0px");
    lastx = (lastx - step) % width;
}

$.urlParam = function (name) {
    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (!results) { return 0; }
    return results[1] || null;
}

/* -- Social Networking shares -------------------- */

function GetSectionURL() {
    return window.location.href;
}

function SharePage(service) {
    service = $.trim(service);
    url = window.location.href;
    var og_title = $(".og_title");
    var og_image = $(".og_image");
    var og_url = $(".og_url");

    var shareurl = '';
    var title = "Explore a more beautiful web: Internet Explorer Add-ons, Pinned Sites and Tracking Protection Lists.";

    title = og_title.attr("content");
    url = og_url.attr("content");

    var desc = title + "%20" + url;

    if (service === "Email") {
        shareurl = "mailto:?subject=" + title + "&body=" + desc;
    }
    if (service === "Twitter") {
        title = title + " %23IE9";
        shareurl = "http://twitter.com/share?text=" + title + "&url=" + url;
    }
    if (service === "Facebook") {
        shareurl = "http://www.facebook.com/sharer/sharer.php?u=" + url + "&t=" + title;
    }
    if (service === "Live") {
        shareurl = "http://profile.live.com/badge/?url=" + url + "&title=" + title + "&description=" + desc;
    }
    if (service === "Renren") {
        shareurl = "http://share.renren.com/share/buttonshare.do?link=" + url;
    }
    if (service === "Sina") {
        shareurl = "http://v.t.sina.com.cn/share/share.php?url=" + url;
    }
    window.open(shareurl, "_newtab");
}

/* -- Pinned Site Thumbnails -------------------- */

function BindDetailThumbnails() {
    $('#slideshow .thumbs div').each(function (index, thumbnail) {
        $(thumbnail).click(function () {
            $('#slideshow .slides img').hide();
            $('#slideshow .slides img[data-name=' + $(thumbnail).data('name') + ']').show();
        });
    });
}

/* -- Pinned Site Detail Favicon -------------------- */
/* -- Set the icon to show after a timeout due to IE 9 not reading the meta info until a certain time */

function ShowDragToPin() {
    $('#pinning_icon, #drag_to_pin, #drag_to_pin_arrow').fadeIn('fast'); DetectSecondVisit(true);
}

/* -- Detect whether a referral came from self (pinning error) -------------------- */

function DetectSecondVisit(forceShowButton) {
    var last_href = $.cookie("lastPinnedSiteHref");

    // show the having trouble button if the page has been hit for a second time 
    // and the browser is MSIE 9
    if ((last_href == window.location.href && userAgent.search(/msie 9/i) != "-1") || forceShowButton) {
        // show the "how to pin" button
        $('#detail .trouble').show();
        $('#pinning_icon, #drag_to_pin, #drag_to_pin_arrow').show();

        // bind "how to pin" button
        $('#detail .trouble a').click(function () {
            $('#how_to_pin_detail').fadeIn('fast');
            $('#how_to_pin_detail .close').click(function () {
                $('#how_to_pin_detail').fadeOut('fast');
            });
        });
    }

    $.cookie("lastPinnedSiteHref", window.location.href);
}

function isLoggedin() {
    return ($.cookie("logged_in")) ? true : false;
}

//SEARCH STUFF
if (typeof search == 'undefined') { var search = {}; }

search.doSearch = function (searchBox) {

    var searchTerm = escape(searchBox.val());

    SEARCH_TERM = searchTerm;
    Analytics.trackSearch(searchTerm);

    ClearMarquee();
    if (document.URL.indexOf('/addons/default.aspx') > -1)
        GetMarqueeContent(0);
    else if (document.URL.indexOf('/trackingprotectionlists/default.aspx') > -1)
        InitializeTPLs();
    else if (document.URL.indexOf('/pinnedsites/default.aspx') > -1)
        GetMarqueeContent(0);
}

$(document).ready(function () {

    //cache selectors
    var searchForm = $('#searchForm');
    var searchBox = searchForm.find('#searchBox');
    var searchButton = searchForm.find('#searchButton');
    var searchTextDiv = searchForm.find('div');
    var searchTextSpan = searchForm.find('div').find('span');
    var searchInput = searchForm.find('input');

    search.submitted = false;

    searchBox.unbind('click.marquee');

    searchForm.submit(function (e) {
        if (searchBox.val() === '') {
            searchButton.removeClass('purple');
        } else {
            searchButton.toggleClass('purple');
        }
        e.preventDefault();

        var term = escape(searchBox.val());
        if (document.URL.indexOf('/pinnedsites/') > -1)
            tag.autoscroll.search({ searchterm: term, featuretype: 4 });
        else if (document.URL.indexOf('/addons/') > -1)
            tag.autoscroll.search({ searchterm: term, featuretype: -1 });
        else
            search.doSearch(searchBox);
    });

    searchButton.click(function (e) {
        e.preventDefault();
        searchTerm = escape(searchBox.val());
        if (searchTerm === '') {
            return;
        }

        if ($(this).hasClass('purple')) {
            searchButton.removeClass('purple');
            searchButton.addClass('blueButton');
            searchBox.val('');

            searchTextDiv.css('width', '160px');
            searchTextSpan.show();
            searchTextDiv.fadeIn();
            searchButton.removeClass('purpleButton');

            if (searchTerm === '') {
                return;
            }
        }

        searchForm.submit();
    });

    searchInput.keydown(function () {
        searchButton.removeClass('purple');
    });

    searchBox.focus(function () {
        searchButton.removeClass();
        searchButton.addClass('purpleButton');
    });

    searchBox.focusout(function () {
        if (searchBox.val() === '') {
            searchButton.removeClass();
            searchButton.addClass('blueButton');
        }
    });

    //THIS will be deleted once categories for search is enabled
    $('#nav_path .navcat').bind('click.search', function () {
        searchBox.val('');
        SEARCH_TERM = '';
        searchButton.removeClass();
        searchButton.addClass('blueButton');
    });

    searchTextDiv.click(function () {
        searchTextSpan.fadeOut('fast', function () {
            searchTextDiv.animate({ width: '1px' }, function () {
                searchTextDiv.hide();
                searchBox.focus();
            });
        });
        searchForm.unbind('mouseover');
        searchForm.unbind('mouseout');
    });

    searchForm.hover(function () {
        searchButton.toggleClass('purpleButton');
    });
});


function BindAddMarqueeStarratings() { //coolest function name ever


    //    $('.section_addons .pane').live('mouseenter', function () {
    //        if (!$(this).data('init')) {
    //            $(this).data('init', true);
    //            $(this).hoverIntent
    //            ({
    //                over: function () {

    //                    var self = this;

    //                    $(self).addClass('orangehover');

    //                    var resTypeEl = $(self).find('.resType');
    //                    var resType = resTypeEl.text();

    //                    //truncate resrouce type if it is too long
    //                    if (resType.length > 18) {
    //                        resTypeEl.attr('title', resType);

    //                        resType = resType.substring(0, 15) + '...';
    //                        resTypeEl.text(resType);
    //                    }
    //                    else
    //                        resTypeEl.removeAttr('title');

    //                    $(self).find('.starcontainer').show();
    //                },

    //                out: function () {

    //                    $(self).removeClass('orangehover');

    //                    var resTypeEl = $(this).find('.resType');
    //                    //if title exists then i want to restore h4 text
    //                    if (resTypeEl.attr('title').length != 0)
    //                        resTypeEl.text(resTypeEl.attr('title'));

    //                    $(this).find('.starcontainer').hide();
    //                },
    //                interval: 250

    //            });
    //            $(this).trigger('mouseenter');
    //        }
    //    });  

}

function LoadFacebookSocial(locale, type, resourceId) {
    //var fbscript = document.createElement('script');
    //fbscript.src = "http://connect.facebook.net/en_US/all.js#xfbml=1";
    //$("li#facebooksocial").append(fbscript);
    //alert(ie8);
    var fblikehtml = '<fb:like class="fb_edge_width_with_comment fb_iframe_widget" href="http://www.iegallery.com/' + locale + '/' + type + '/detail.aspx?id=' + resourceId + '" layout="button_count" show_faces="false" send="true">';
    if (ie8 != "-1") {
        $("li#facebooksocial").append('<a href="http://www.facebook.com" title="Facebook" onclick="SharePage(\'Facebook\', GetSectionURL());return false;"><img src="/assets/images/layout/fb_share.png"></img></a>');
        $("li#facebooksocial").addClass('ie8');
    } else {
        $("li#facebooksocial").append(fblikehtml);
    }

    /*<fb:like class="fb_edge_width_with_comment fb_iframe_widget" \
    href="http://www.iegallery.com/<%= Locale.GetCurrentLocale().CountryCode %>/pinnedsites/detail.aspx?id=<%= resource_id %>" layout="button_count" \
    show_faces="false" send="true">
    */
    // fb_edge_widget_with_comment fb_iframe_widget

    //The following code is impossible, since it tries to use cross-domain scripting..
    //var fbcss = document.createElement("link");
    //fbcss.href = "/assets/css/fb.css";
    //fbcss.type = "text/css";
    //fbcss.rel = "stylesheet";
    //$("html#facebook head").append(fbcss);
    //alert($("html#facebook head").InnerHtml.toString());
}

$.fn.extend({
    center: function (options) {
        var options = $.extend({
            horizontal: true,
            vertical: true,
            position: 'relative'
        }, options);

        return this.each(function () {
            $(this).css('position', options.position);
            if (options.vertical) {
                var top = ($(this).parent().height() / 2) - ($(this).outerHeight() / 2);
                $(this).css('top', parseInt(top));
            }

            if (options.horizontal) {
                var left = ($(this).parent().width() / 2) - ($(this).outerWidth() / 2);
                $(this).css('left', parseInt(left));
            }
        });
    }
});

var tag = window.TAG || {}; //new primary namespace?

TAG = function () { }; //stub
TAG.Account = function () { }; //stub

TAG.Account.Availability = function () {
    var setting = {
        link: null,
        indicatorYes: null,
        indicatorNo: null,
        input: null,
        userName: null,
        request: null,
        isAvailable: false,
        serviceURL: '/authenticate/availability.ashx?name='
    }
    var launch = function () {
        var self = this;

        //hydrate
        setting.link = $('#check-availability');
        setting.indicatorYes = $('#check-availability-inidcator');
        setting.indicatorNo = $('#check-availability-inidcator-not');
        setting.input = $('#Main_new_username');
        //setting.link.hide();

        //bind
        setting.link.click(function () {

            //setting.link.hide();

            setting.userName = setting.input.val();
            setting.input.attr('disabled', 'disabled');
            setting.request = setting.serviceURL + setting.userName;

            $.get(setting.request, function (response) {
                self.callback(response); ;
            });
        });
        setting.input.keydown(function () {
            setting.indicatorYes.hide();
            setting.indicatorNo.hide();
            setting.link.show();
        });
    }
    var callback = function (response) {
        setting.input.removeAttr('disabled');
        setting.isAvailable = response;

        if (setting.isAvailable == 'true') {
            setting.indicatorNo.hide();
            setting.indicatorYes.fadeIn();
        } else {
            setting.indicatorYes.hide();
            setting.indicatorNo.fadeIn();
            setting.input.focus();
        }
    }
    return {
        isAvailable: setting.isAvailable,
        launch: launch,
        callback: callback
    }
} ();
//usage
//TAG.Account.Availability.launch();

TAG.Account.Sync = function () {//modal stuff for signin2.aspx
    var setting = {
        button: null,
        userNameInput: null,
        userPassInput: null,
        userNameValue: null,
        userPassValue: null,
        newNameInput: null,
        newEmailInput: null,
        newNameValue: null,
        newEmailValue: null,
        termsInput: null,
        termsValue: null,
        liveidInput: null,
        liveidValue: null,
        request: null,
        mergeComplete: false,
        serviceURL: '/authenticate/merge.ashx',
        modal: null,
        sync: null,
        complete: null,
        close: null,
        continueOn: null,
        mask: null
    }

    var launch = function () {

        //hydrate selectors
        setting.modal = $('#account-modal');
        setting.sync = $('#content-sync');
        setting.complete = $('#content-complete');
        setting.close = $('#account-modal').find('.button-close');
        setting.continueOn = $('#account-modal').find('.button-continue');
        setting.mask = $('#modal-account-mask');


        //show mask
        setting.mask.show();

        //show syncing content
        setting.close.show();
        setting.modal.fadeIn();
        setting.sync.fadeIn();

    }
    var bind = function () {
        var self = this;

        //New User
        setting.button = $('#link_submit');
        setting.userNameInput = $("#existing_username");
        setting.userPassInput = $('#existing_password');
        setting.termsInput = $('#terms_check');
        setting.liveidInput = $("#referer");

        setting.button.click(function (event) {
            event.preventDefault();

            //get the username and userpass
            setting.userNameValue = setting.userNameInput.val();
            setting.userPassValue = setting.userPassInput.val();
            setting.liveidValue = setting.liveidInput.val();
            setting.termsValue = setting.termsInput.attr("checked");

            //kick off a request to the new service
            $.ajax({
                type: 'post',
                url: setting.serviceURL,
                data: {
                    newuser: 'false',
                    username: setting.userNameValue,
                    password: setting.userPassValue,
                    liveid: setting.liveidValue
                },
                success: function (data, textStatus, jqXHR) {
                    self.callback(data, ".link_username");
                }
            });

            //kick off the modal
            launch();

        });
    }

    //Create user
    var create = function () {
        var self = this;

        //New User
        setting.button = $('#create_submit');
        setting.newNameInput = $('#Main_new_username');
        setting.newEmailInput = $("#Main_new_email");
        setting.termsInput = $('#terms_check');
        setting.liveidInput = $("#referer");
        setting.errorMessage = $('.error');

        setting.button.click(function (event) {
            event.preventDefault();

            setting.newNameValue = setting.newNameInput.val();
            setting.newEmailValue = setting.newEmailInput.val();
            setting.liveidValue = setting.liveidInput.val();
            setting.termsValue = setting.termsInput.attr("checked");


            $.ajax({
                type: 'post',
                url: setting.serviceURL,
                data: {
                    newuser: 'true',
                    username: setting.newNameValue,
                    email: setting.newEmailValue,
                    liveid: setting.liveidValue,
                    terms: setting.termsValue
                },
                success: function (data, textStatus, jqXHR) {
                    self.callback(data, ".create_username");
                }
            });

            //kick off the modal
            launch();


        });
    }

    var callback = function (response, column) {
        //console.log('service response: ' + response);
        setting.mergeComplete = response;

        if (setting.mergeComplete == 'true') {
            //if returns true - update modal
            update();
        } else {
            //if returns anything but true - stick string in page and destroy modal
            $('#Form1 p.error').remove();
            $(column).find('ul').before('<p class=\"error\"></p>'); //cache this selector
            $(column + ' p.error').html(response);
            destroy();
        }

    }
    var update = function () {
        setting.sync.hide();
        setting.complete.fadeIn();
    }
    var destroy = function () {
        //console.log(setting.sync);
        setting.sync.hide();
        setting.complete.hide();
        setting.modal.hide();
        setting.mask.hide();
    }
    return {//expose methods
        launch: launch,
        update: update,
        destroy: destroy,
        bind: bind,
        create: create,
        callback: callback
    }
} ();
//usage
//TAG.Account.Sync.launch(); - to turn on modal with spinner while updating
//TAG.Account.Sync.update(); - to switch content after update complete