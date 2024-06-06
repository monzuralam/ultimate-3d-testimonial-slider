(function($){
    // copy shortcode
    $(".uts-copy-shortcode").click(function() {
        const copyText = $(this).prev();
        copyText.select();
        document.execCommand("copy");
    });

    // Switch Tabs
    $('.shortcode-generator-metabox .uts-tab-nav li').click(function() {
        const tabId = $(this).find('a').attr('href');
        $('.uts-tab-nav li').removeClass('active');
        $('.uts-tab-content .uts-tab-content-wrapper').removeClass('active');
        $(this).addClass('active');
        $(tabId).addClass('active');
        return false;
    });
})(jQuery);