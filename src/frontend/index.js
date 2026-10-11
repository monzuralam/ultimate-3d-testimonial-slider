/**
 * Frontend: initializes a Swiper coverflow slider on every slider container.
 */
import Swiper from 'swiper';
import { EffectCoverflow, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/pagination';
import './main.scss';

const initSlider = ( element ) => {
	new Swiper( element, {
		modules: [ EffectCoverflow, Pagination ],
		effect: 'coverflow',
		grabCursor: true,
		centeredSlides: true,
		slidesPerView: 'auto',
		coverflowEffect: {
			rotate: 50,
			stretch: 0,
			depth: 100,
			modifier: 1,
			slideShadows: false,
		},
		pagination: {
			el: element.querySelector( '.swiper-pagination' ),
			clickable: true,
		},
	} );
};

document.querySelectorAll( '.ucs-container.slider' ).forEach( initSlider );
