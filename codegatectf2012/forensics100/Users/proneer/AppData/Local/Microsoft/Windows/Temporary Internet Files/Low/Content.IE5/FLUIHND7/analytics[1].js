// dependencies: jQuery, s_code

var Analytics = {

    webSliceClickThru: function (params) {

        var langLoc = params.langLoc ? params.langLoc : 'en-us';

        s.linkLeaveQueryString = true;
        s.linkTrackVars = 'events,eVar10,prop10';
        s.linkTrackEvents = 'event8';
        s.prop10 = langLoc;
        s.eVar10 = langLoc;

        s.events = 'event8';

        s.linkType = 'e';
        s.linkName = 'Web Slice Click-Throughs';

        this.trackEvent('Web Slice Click-Throughs');

    },

    //Tracking for the Gallery (IE 8 Users)
    trackIEDownload: function () {
        s.linkLeaveQueryString = true;
        s.linkTrackVars = 'events,eVar9,eVar10,prop10';
        s.linkTrackEvents = 'event18';
        s.eVar9 = s.pageName;
        s.events = 'event18';

        s.linkType = 'e';
        s.linkName = 'Download IE9';

        this.trackEvent('IEgallery dl now');

    },

    //Tracking for Once click donwloads
    trackIEOneClickDownload: function (msnbingchecked) {
        s.linkLeaveQueryString = true;
        s.linkTrackVars = 'events,eVar9,eVar10,prop10';
        s.linkTrackEvents = 'event17';
        s.eVar9 = s.pageName;
        s.events = 'event17';

        s.linkType = 'e';
        s.linkName = 'Download IE9';

        this.trackEvent('One-click dl now');

        if (msnbingchecked == true) {
            Analytics.trackMSNBing();
        }

    },

    //Tracking for Webslice downloads
    trackIEDownloadSlice: function () {
        s.linkLeaveQueryString = true;
        s.linkTrackVars = 'events,eVar9,eVar10,prop10';
        s.linkTrackEvents = 'event16';
        s.eVar9 = s.pageName;
        s.events = 'event16';

        s.linkType = 'e';
        s.linkName = 'Download IE9';

        this.trackEvent('Web Slice DL now');

    },

    //Tracking if the msn/bing has been added.
    trackMSNBing: function () {
        s.linkLeaveQueryString = true;
        s.linkTrackVars = 'events,eVar9,eVar10,prop10';
        s.linkTrackEvents = 'event19';
        s.eVar9 = s.pageName;
        s.events = 'event19';

        s.linkType = 'e';
        s.linkName = 'Download IE9';

        this.trackEvent('msn-bing selected');

    },


    // Method to track downloads from the gallery
    trackDownload: function (resName, resType, partnerName, resID) {
        var path = window.location.pathname;
        var dlLocation = "";
        s.events = "";

        if (path.indexOf("trackingprotectionlists") > -1) {
            dlLocation = "TPL Page";
            s.linkTrackVars = 'prop17,prop10,eVar17,prop7,eVar9,eVar10,eVar11,eVar13,events';
            s.prop17 = resName; // install name
            s.eVar17 = s.prop17;
            s.linkTrackEvents = 'event20';
            s.events = s.apl(s.events, "event20", ",", 2);

        }
        else if (path.indexOf("pinnedsites/") > -1 || path.indexOf("partners/") > -1 || path.indexOf("/staples") > -1 || path.indexOf("_sony") > -1 || path.indexOf("/geeksquad") > -1) {
            dlLocation = "Pinned Sites Page";
            s.linkTrackVars = 'prop16,prop10,eVar16,prop7,eVar9,eVar10,eVar11,eVar13,events';
            s.prop16 = resName; // install name
            s.eVar16 = s.prop16;
            s.linkTrackEvents = 'event14';
            s.events = s.apl(s.events, "event14", ",", 2);
        }
        else {
            dlLocation = "Addons Page";
            s.linkTrackVars = 'prop6,prop7,prop10,eVar6,eVar7,eVar9,eVar10,eVar11,eVar13,prop12,events';
            s.prop6 = resName; // install name
            s.eVar6 = s.prop6;
            s.eVar7 = resType;
            s.prop12 = resType;
            s.linkTrackEvents = 'event6';
            s.events = s.apl(s.events, "event6", ",", 2);
        }

        s.prop7 = s.pageName;
        s.eVar9 = s.pageName;
        s.eVar13 = dlLocation;
        s.eVar11 = partnerName;
        

        this.handleAtlasCode();
        this.trackEvent('Gallery Download');
    },

    trackEngagement: function (eventName) {
        s.events = "";
        s.linkTrackVars = 'events,eVar2';
        s.linkTrackEvents = 'event2';
        s.events = 'event2';
        s.eVar2 = eventName.toLowerCase();
        this.trackEvent(eventName);
    },

    trackFailedInstall: function (eventName, resName) {
        s.events = "";
        s.linkTrackVars = 'events,eVar6,eVar9,eVar10';
        s.linkTrackEvents = 'event9';
        s.prop6 = resName;
        s.eVar6 = s.prop6;
        s.eVar9 = s.pageName;
        s.eVar10 = '';
        s.events = 'event9';
        this.trackEvent(eventName);
    },

    trackSearch: function (searchTerm) {
        s.events = "";
        s.linkTrackVars = 'events,eVar5';
        s.linkTrackEvents = 'event5';
        s.events = 'event5';
        s.evar5 = 'search: ' + searchTerm;
        this.trackEvent('Search');
    },

    // Calls s_code to track an event
    // Sometimes invoked by this.trackDownload
    trackEvent: function (eventText) {
        s.tl(true, 'o', eventText);
    },

    handleAtlasCode: function () {
        atlas_code = $('.atlas_code').val();
        if (atlas_code != "") {
            var st = $("<script type='text/javascript' langage='JavaScript'></script>");
            st.attr("src", "http://view.atdmt.com/jaction/" + atlas_code);
            $('body').append(st);
        }
    }
};