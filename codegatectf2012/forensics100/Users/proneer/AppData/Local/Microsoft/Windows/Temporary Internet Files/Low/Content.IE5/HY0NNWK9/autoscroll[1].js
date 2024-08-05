if (typeof tag == 'undefined') { var tag = {}; }

tag.autoscroll = function () {

    var _marquee, _resTemplate;
    var _fetchedlast = false;
    var _currentPage = 0;
    var _resourcesTotal;
    var _pageSize = 200;
    var _scrollTimer = false;
    var _speed = 2, maxSpeed = 240;
    var _featuretype = -1;
    var _fps = 24;
    var _searchTerm = '';
    var _category = -1;
    var _pager;
    var _loadingTimer;
    var _isLoading = false;
    var _isScrolling = false;
    var _x, _y;
    var _rightMargin;
    var _leftMargin;

    var calculateSpeed = function (origin, pos) {

        var distanceFromOrigin = Math.abs(origin - pos);

        var newspeed = distanceFromOrigin / 3;

        _speed = (newspeed > maxSpeed) ? maxSpeed : newspeed;
        //console.log
        //screenLogger.log('speed: ' + _speed);
    }

    var bindAutoScroll = function () {
        $(_marquee).mousemove(function (e) {

            var screenWidth = $(window).width();
            _leftMargin = Math.floor(screenWidth * .15);
            _rightMargin = Math.floor(screenWidth * .85);

            //alert("mousemove function got hit :D");

            _x = parseInt(e.pageX); //this is probaby an int but whatever
            _y = parseInt(e.pageY);
            //if mouse is on the right side => scroll
            if (_x > _rightMargin) {

                calculateSpeed(_rightMargin, _x);
                if (!_scrollTimer)
                    _scrollTimer = setInterval(function () { scrollMarquee('right', _speed); }, 1000 / _fps);

            }
            else if (_x < _leftMargin) {
                //disable hover state during scroll
                calculateSpeed(_leftMargin, _x);
                if (!_scrollTimer)
                    _scrollTimer = setInterval(function () { scrollMarquee('left', _speed); }, 1000 / _fps);
            }
            else {

                if (_scrollTimer) {
                    //console.log('mouseleave: ' + _x + ' left: ' + _leftMargin + ' right: ' + _rightMargin);
                    window.clearInterval(_scrollTimer);

                    _scrollTimer = false;
                    _isScrolling = false;
                }
            }


        });
        //do nothing if not hover over marquee
        $(_marquee).mouseleave(function () {


            if (_x <= _rightMargin || _x >= _leftMargin) {
                //console.log('mouseleave: ' + _x + ' left: ' + _leftMargin + ' right: ' + _rightMargin);
                window.clearInterval(_scrollTimer);
                _scrollTimer = false;
                _isScrolling = false;
            }
        });
    }

    var scrollMarquee = function scrollMarquee(direction, speed) {
        _isScrolling = true;
        var sPos = $(_marquee).scrollLeft();

        if (direction == 'left')
            $(_marquee).scrollLeft(sPos - speed);
        else //direction === right
            $(_marquee).scrollLeft(sPos + speed);

    }

    var bindScrollbarPaging = function () {

        $(_marquee).bind('scroll', function () {

            var marqueeWidth = $(this).width();
            var mScrollWidth = $(this).attr('scrollWidth');

            if ($(this).scrollLeft() > .8 * (marqueeWidth - mScrollWidth)) {

                //caculate total pages
                var totalPages = Math.ceil(_resourcesTotal / _pageSize);
                //totalPages = (_resourcesTotal % _pageSize > 0) ? totalPages + 1 : totalPages;

                //if there is another page add it to the marquee
                if (!_isLoading && totalPages > 1 && _currentPage < totalPages) {
                    _currentPage += 1;
                    getResources({ index: _currentPage * _pageSize });
                }
            }
        });

    }

    var search = function (args) {

        _searchTerm = args.searchterm;
        _currentPage = 0;
        $(_marquee + ' > div').empty();
        _featuretype = args.featuretype;
        _category = args.category;
        getResources({ index: 0 });

    }

    var showSpinner = function () {
        $('.loading_spinner').show();
        var spinTimer = setInterval(function () { UpdateSpinner("div.loading_spinner", 24, 288); }, 65);
    }
    var hideSpinner = function () {
        window.clearInterval(_loadingTimer);
        $('.loading_spinner').hide();
    }

    var getResources = function (args) {
        //?index=0&category=-1&type=-1&searchterm=
        if (_currentPage === 0)
            showSpinner();
        _isLoading = true;
        $.getJSON("../handlers/getResources.ashx", { index: args.index, category: _category, searchterm: _searchTerm, type: _featuretype, pagesize: _pageSize }, function (json) {

            //put two resouces/panes in each div
            hideSpinner();

            _resourcesTotal = json.Total; //this will be used elsewhere

            if (json.Total > 0) {
                var pane,
                col = $('<div data-page="' + args.index + '"></div>'),
                html = '';

                $.each(json.Resources, function (i, val) {

                    //holiday holiday
                    if (json.Resources[i].Holiday === true) {
                        if (win7 != "-1" && ie9 != "-1") {
                            //json.Resources[i].Holiday = "holiday";
                            var tagURL = "/assets/images/holiday/TileOverlayTag-Browse.png";
                            if (is_cn) {
                                tagURL = "/assets/images/holiday/TileOverlayTag-Browse-CN.png";
                            } 
                            json.Resources[i].HolidayOverlay = '<img id="" src="' + tagURL + '" ' + 'class="browseTag">';
                        }
                    }

                    pane = $(_resTemplate).tmpl(json.Resources[i]);

                    col.append(pane);

                    if ((i + 1) % 2 === 0 || i === json.Count - 1) {
                        html += col.clone().wrap('<div></div>').parent().html(); //grab the outerHTML
                        col = $('<div data-page="' + args.index + '"></div>');
                    }

                });
                $(_marquee + ' > div').append(html);
                $(_pager).show();
            }
            else {
                $(_marquee + ' > div').append('<h2 style="text-align:center;"><br>' + NO_RESULT + '<br><br><br></h2>');
                $(_pager).hide();
            }
            _isLoading = false;
        });
    }

    var bindFeatureTypeSelector = function () {
        $(_pager + ' select').change(function () {

            _currentPage = 0;

            selected = $(this).find(':selected');

            search({ featuretype: selected.val(), category: _category });

        });
    }


    //live isn't fast enough
    var hoverpane = function (id) {

        //console.log('hover scrolling: ' + tag.autoscroll.isScrolling);

        if (!_isScrolling) { //don't show hover state if scrolling

            var self = $('#scrolling_marquee #' + id);


            self.addClass('orangehover');

            var resTypeEl = self.find('.resType');
            var resType = resTypeEl.text();

            //truncate resrouce type if it is too long
            if (resType.length > 18) {
                resTypeEl.attr('title', resType);

                resType = resType.substring(0, 15) + '...';
                resTypeEl.text(resType);
            }
            else
                resTypeEl.removeAttr('title');

            self.find('.starcontainer').show();

        }

    }

    var hoverpaneout = function (el) {

        var self = $(el);

        $(self).removeClass('orangehover');

        var resTypeEl = $(self).find('.resType');
        //if title exists then i want to restore h4 text
        if (resTypeEl.attr('title').length != 0)
            resTypeEl.text(resTypeEl.attr('title'));

        $(self).find('.starcontainer').hide();


    }

    var init = function (args) {


        _marquee = args.marquee;
        _resTemplate = args.template;
        _featuretype = args.featuretype;
        _pager = args.pager;

        //process hash
        if ($.urlParam('feature') != 0 && window.location.hash == '') {
            _featuretype = $('#nav_type select option[data-type=' + $.urlParam('feature') + ']').val();
        } else {
            ProcessUrlHash();
            if (CATEGORY != '') { _category = CATEGORY; } //grab this out of global scope -interactions.js
        }

        //populate initial resources
        getResources({ index: 0 });

        //this will load more resources when we scroll over far enough
        bindScrollbarPaging();

        //scroll when mouse over stuff
        bindAutoScroll();

        MarqueeNav(); //this resides in interactions.js. It bands the nav links above the marquee

        bindFeatureTypeSelector();

    }

    //reveal public properties and methods
    return {
        init: init,
        search: search,
        isScrolling: _isScrolling,
        hoverpane: hoverpane,
        hoverpaneout: hoverpaneout
    };
} ();

