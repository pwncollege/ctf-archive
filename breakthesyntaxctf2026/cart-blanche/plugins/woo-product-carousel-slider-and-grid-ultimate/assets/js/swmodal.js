document.addEventListener('DOMContentLoaded', function () {
    (function ($) {
        // Modal
        let wpcuModal = document.querySelector('.wpcu-modal-js');
        $('body').on('click', '.wpcu-btn-modal-js', function (e) {
            e.preventDefault();
            $('.wpcu-modal').addClass('wpcu-modal--loading');
            var data_target = $(this).attr("data-wpcu-target");
            var product_id = $(this).attr("data-product-id");
            var nonce = $(this).attr("data-nonce");
            document.querySelector(`.${data_target}`).classList.add('wpcu-show');
            $.ajax({
                url: wcpcsu_quick_view.ajax_url,
                type: "POST",
                data: {
                    action: "ajax_quick_view",
                    product_id: product_id,
                    nonce: nonce
                },
                success: function (html) {
                    $('.wpcu-modal').removeClass('wpcu-modal--loading');
                    $('.wpcu-modal__body').empty().append(html);
                },
                error: function error(_error) {
                    console.log(_error);
                }

            });
        });

        $('body').on('click', '.wpcu-modal-close-js', function (e) {
            e.preventDefault();
            $(this).closest('.wpcu-modal-js').removeClass('wpcu-show');
        });

        $(document).bind('click', function (e) {
            if (e.target == wpcuModal) {
                wpcuModal.classList.remove('wpcu-show');
            }
        });

    })(jQuery)
})