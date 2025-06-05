(function ($) {
    // element
    const element = document.getElementById("ucs-container-slider");

    // Check if element exists
    if (!element) {
        return;
    }

    // Swiper
    new Swiper(element, {
        effect: "coverflow",
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: "auto",
        gap: 20,
        coverflowEffect: {
            rotate: 50,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });

})(jQuery);
