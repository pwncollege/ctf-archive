jQuery(document).ready(function ($) {
    $(".lcsp-tabs-menu a").click(function (event) {
        event.preventDefault();
        $(this).parent().addClass("current");
        $(this).parent().siblings().removeClass("current");
        var tab = $(this).attr("href");
        $(".lcsp-tab-content").not(tab).css("display", "none");
        $(tab).fadeIn();
    });

    //color picker
    jQuery('.cpa-color-picker').wpColorPicker();


    $('.cmd-switch > div').on('click', function (e) {
        e.preventDefault();
        $(this).addClass('active');
        $(this).siblings().each(function () {
            $(this).removeClass('active');

        });
    });

    $(".cmd-switch-carousel").on('click', function (e) {
        e.preventDefault();
        $(".wcpscu_grid_layout").removeAttr('checked');
        $(".wcpscu_carousel_layout").attr('checked', true);
        $("#tab2").css('display', 'block');
        $("#tab3").css('display', 'none');
        $("#wcpscu_total_pdt").html("Total Products to Display");
    });

    $(".cmd-switch-grid").on('click', function (e) {
        e.preventDefault();
        $(".wcpscu_carousel_layout").removeAttr('checked');
        $(".wcpscu_grid_layout").attr('checked', true);
        $("#tab2").css('display', 'none');
        $("#tab3").css('display', 'block');
        $("#wcpscu_total_pdt").html("Products Per Page");
    });

    /* Replace all SVG images with inline SVG */
    document.querySelectorAll('img.svg_compile').forEach((el) => {
        const imgID = el.getAttribute('id');
        const imgClass = el.getAttribute('class');
        const imgURL = el.getAttribute('src');

        fetch(imgURL)
            .then(data => data.text())
            .then(response => {
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(response, 'text/html');
                let svg = xmlDoc.querySelector('svg');

                if (typeof imgID !== 'undefined') {
                    svg.setAttribute('id', imgID);
                }

                if (typeof imgClass !== 'undefined') {
                    svg.setAttribute('class', imgClass + ' replaced-svg');
                }

                svg.removeAttribute('xmlns:a');

                el.parentNode.replaceChild(svg, el);
            })
    });



    $('.theme_1, .theme_2, .theme_3').hide();

    var $theme = $('.wcpscu_theme'); // get theme jQuery object

    var currentTheme = $theme.val(); // get current theme

    $('.' + currentTheme).show(); // show current theme
    $theme.on('change', function () {
        var $this = $(this);

        ('theme_1' == $this.val()) ? $('.theme_1').show(): $('.theme_1').hide();
        ('theme_2' == $this.val()) ? $('.theme_2').show(): $('.theme_2').hide();
        ('theme_3' == $this.val()) ? $('.theme_3').show(): $('.theme_3').hide();

    });

    $('.wcpscu_radio_layout').hide();

    // autoplay dependable
    if ($("input[name='wcpscu[A_play]']:checked").val() === 'yes') {
        $('.wpcu_auto_play_depend').show();
    } else {
        $('.wpcu_auto_play_depend').hide();
    }

    $('.wcpcu_auto_play').on('click', function () {
        if ($(this).val() === 'yes') {
            $('.wpcu_auto_play_depend').show();
        } else {
            $('.wpcu_auto_play_depend').hide();
        }
    });

    //navigation dependable
    if ($("input[name='wcpscu[nav_show]']:checked").val() === 'yes') {
        $('.wpcu_navigation_depend').show();
    } else {
        $('.wpcu_navigation_depend').hide();
    }

    $('.wcpcu_navigation').on('click', function () {
        if ($(this).val() === 'yes') {
            $('.wpcu_navigation_depend').show();
        } else {
            $('.wpcu_navigation_depend').hide();
        }
    });

    //caraousel pagination dependable
    if ($("input[name='wcpscu[carousel_pagination]']:checked").val() === 'yes') {
        $('.wpcu_carousel_pagination_depend').show();
    } else {
        $('.wpcu_carousel_pagination_depend').hide();
    }

    $('.wcpcu_carousel_pagination').on('click', function () {
        if ($(this).val() === 'yes') {
            $('.wpcu_carousel_pagination_depend').show();
        } else {
            $('.wpcu_carousel_pagination_depend').hide();
        }
    });


});