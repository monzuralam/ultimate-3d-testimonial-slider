(function($){
    // copy shortcode
    $(".uts-copy-shortcode").click(function() {
        const copyText = $(this).prev();
        copyText.select();
        document.execCommand("copy");
    });
})(jQuery);