/**
 * Frontend: initializes the Swiper coverflow slider.
 */
import Swiper from 'swiper';
import { EffectCoverflow, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/pagination';
import './main.scss';

const element = document.getElementById( 'ucs-container-slider' );

if ( element ) {
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
}
