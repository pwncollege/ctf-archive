/*
    Plugin: WooCommerce Product Carousel, Slider & Grid Ultimate
    Plugin URI: https://wordpress.org/plugins/woo-product-carousel-slider-and-grid-ultimate/
    Author: wpWax
    Version: 1.0
*/
document.addEventListener("DOMContentLoaded", function () {
    (function ($) {

        function alljs() {
            // Style reset for buttons <p> tag
            document.querySelectorAll('.wpcu-button p.woocommerce').forEach((el, id) => {
                el.setAttribute('style', 'none');
            });

            //Lazy load
            let lazyLoadContainer = document.querySelectorAll('.wpcu-lazy-load');
            if (lazyLoadContainer.length !== 0) {
                document.querySelectorAll('.wpcu-products').forEach((el) => {
                    el.classList.remove('wpcu-lazy-load');
                })
            }

            /* Check WPCU Carousel Data */
            let checkData = function (data, value) {
                return typeof data === 'undefined' ? value : data;
            };
            /* WPCU Carousel */
            let wpcuCarousel = document.querySelectorAll('.wpcu-carousel');
            wpcuCarousel.forEach(function (el) {
                let swiper = new Swiper(el, {
                    slidesPerView: checkData(parseInt(el.dataset.wpcuItems), 4),
                    spaceBetween: checkData(parseInt(el.dataset.wpcuMargin), 30),
                    loop: checkData(JSON.parse(el.dataset.wpcuLoop.toLowerCase()), false),
                    slidesPerGroup: checkData(parseInt(el.dataset.wpcuPerslide), 1),
                    speed: checkData(parseInt(el.dataset.wpcuSpeed), 3000),
                    autoplay: checkData(JSON.parse(el.dataset.wpcuAutoplay), {}),
                    navigation: {
                        nextEl: '.wpcu-carousel-nav__btn--next',
                        prevEl: '.wpcu-carousel-nav__btn--prev',
                    },
                    pagination: {
                        el: '.wpcu-carousel-pagination',
                        type: 'bullets',
                        clickable: true
                    },
                    breakpoints: checkData(JSON.parse(el.dataset.wpcuResponsive), {})
                })
            });
        }

        alljs();

        /* Elementor Edit Mode */
        $(window).on('elementor/frontend/init', function () {
            setTimeout(() => {
                if (elementorFrontend.isEditMode()) {
                    alljs();
                    elementorFrontend.hooks.addAction('frontend/element_ready/widget', function() {
                        alljs();
                    });
                }
            }, 6000);
            setTimeout(() => {
                alljs();
            }, 0);
        });

    })(jQuery);
})