
 var webserver_http = "http://global.ahnlab.com";
 var webserver_https = "https://global.ahnlab.com";

// 000000. 메인 
m000000 = "/en/site/main/main.do";

//010000. Products & Services
m010000 = "/en/site/product/productMain.do";
m010100 = "/en/site/product/productSubList.do?prod_type=CD&prod_class=C";
m010101 = "/en/site/product/productSubList.do?prod_type=CD&prod_class=C";
m010102 = "/en/site/product/productSubList.do?prod_type=CM&prod_class=C";
m010200 = "/en/site/product/productSubList.do?prod_type=ND&prod_class=N";
m010201 = "/en/site/product/productSubList.do?prod_type=ND&prod_class=N";
m010202 = "/en/site/product/productSubList.do?prod_type=NF&prod_class=N";
m010203 = "/en/site/product/productSubList.do?prod_type=NM&prod_class=N";
m010204 = "/en/site/product/productSubList.do?prod_type=NA&prod_class=N";
m010205 = "/en/site/product/productSubList.do?prod_type=NE&prod_class=N";
m010300 = "/en/site/product/productSubList.do?prod_type=P0&prod_class=P";
m010301 = "/en/site/product/productSubList.do?prod_type=P0&prod_class=P";
m010302 = "/en/site/product/productSubList.do?prod_type=P1&prod_class=P";
m010303 = "/en/site/product/productSubList.do?prod_type=P2&prod_class=P";
m010304 = "/en/site/product/productSubList.do?prod_type=P3&prod_class=P";
m010305 = "/en/site/product/productSubList.do?prod_type=P4&prod_class=P";
m010501	= "/en/site/product/windows7.do";

// 020000. Downloads
m020000 = "/en/site/download/downloadMain.do";
m020100 = "/en/site/download/engine/freeTrial.do";
m020200 = "/en/site/download/engine/engineList.do";
m020300 = "/en/site/download/removal/removalList.do";
m020400 = "/en/site/download/downloads/manual.do";
m020500 = "/en/site/download/downloads/brochure.do";

// 030000. Threats & Solutions
m030000 = "/en/site/threat/threatMain.do";
m030100 = "/en/site/threat/html/aboutAsec.do";
m030200 = "/en/site/threat/asec/asecReportList.do";
m030300 = "/en/site/threat/viruscenter/virusList.do";
m030400 = "/en/site/threat/html/secuRiskLevel.do";
m030500 = "";

// 040000. Support
m040000 = "/en/site/support/supportMain.do";
m040100 = "/en/site/support/html/techSupport.do";
//m040200 = "http://esd.element5.com/ccc/index.html?publisherid=200013181";
m040200 = "/en/site/support/html/customerService.do";
m040300 = "/en/site/support/virusreport/virusReport.do";
m040400 = "/en/site/support/virusfaq/virusFaqList.do";
m040500 = "/en/site/support/productfaq/productFaqList.do";
m040600 = "/en/site/support/prodregit/prodregitArticle.do";
m040700 = "/en/site/support/assistance/idpwAssistanceA.do";

// 050000. Store
m050000 = "/en/site/store/storeMain.do";
m050100 = "/en/site/store/storeHomeProductList.do";
m050200 = "/en/site/store/storeBusinessProductList.do";
m050300 = "/en/site/store/renewal/renewalList.do";

// 060000. About AhnLab
m060000 = "http://www.ahnlab.com/company/site/eng/main/comIntroMainEng.do";

// 070000. Login
m070000 = "/en/site/login/loginForm.do";
m070001 = "/en/site/login/logout.do";

// 080000. My Page
m080000 = "/en/site/mypage/mypageMain.do";
m080100 = "/en/site/mypage/customer/customerEditForm.do";
m080200 = "/en/site/mypage/customer/changePwdForm.do";
m080300 = "/en/site/mypage/product/myProductList.do";
m080400 = "/en/site/mypage/product/addMyProductForm.do";
m080500 = "/en/site/mypage/qna/qnaList.do";
m080600 = "/en/site/mypage/myReportHistory/myReportHistoryList.do";

// 090000. Notice
m090100 = "/en/site/notice/noticeList.do?pressType=004";
m090200 = "/en/site/notice/noticeList.do?pressType=001";

// 100000. Etc
m100100 = "/en/site/etc/termsOfUse.do";
m100200 = "/en/site/etc/privacyPolicy.do";
m100300 = "/en/site/etc/contactUs.do";
m100400 = "/en/site/etc/sitemap.do";
m100500 = "/en/site/etc/afterPurchase.do";
m100600 = "http://www.ahnlab.com/company/site/eng/about/partner_channel.jsp";
m100700 = "/en/site/etc/privacyPolicy1.do";
m100701 = "/en/site/etc/privacyPolicy2.do";


// 110000. Search
m110000 = "";

// 120000. Mail
m120100 = "mailto:e-support@ahnlab.com";

// GNB - PullDown
function gnbPlNavi(param) {

	for(var i = 1 ; i < 7 ; i++)
		$("#gnbPl_"+i+"").attr("style", "display:none");

	if(param != 0)
		$("#"+param).attr("style", "display:block");
}

//공통 메뉴 링크
function mLink(pCode,target) {
	
	var pUrl=eval("m"+pCode);
	
	
	if(pUrl.indexOf("/en/site/login/loginForm") > -1 || pUrl.indexOf("/en/site/mypage/") > -1 || pUrl.indexOf("/en/site/support/prodregit/") > -1){
		// https
		pUrl = webserver_https + pUrl;
	}else if(pUrl.indexOf("element5.com") > -1){
		//  그대로
	}else if(pUrl.indexOf("www.ahnlab.com") > -1){
		// 그대로
	}else if(pUrl.indexOf("www.ahnlab.com") > -1){
		// 그대로
	}else{
		// 나머지는 http
		pUrl = webserver_http + pUrl;
	}
	
	
	if (eval(pCode) == "") {
		document.location.href = "http://global.ahnlab.com";
	}
	else {
		if(target == null) target = '_self'; // 타겟값 없을때 _self처리
		window.open(pUrl,target);
	//	document.location.href = pUrl;
	}
}

// 로그아웃
function loginOut(){
	document.location.href = "/en/site/login/userLogout.do";
}
