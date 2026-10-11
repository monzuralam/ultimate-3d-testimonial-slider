/**
 * Frontend: initializes Swiper on every slider container, using the
 * options from its data-uts-config attribute (see uts_slider_config()).
 */
import Swiper from 'swiper';
import {
	Autoplay,
	EffectCards,
	EffectCoverflow,
	EffectFade,
	Navigation,
	Pagination,
} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-cards';
import 'swiper/css/effect-coverflow';
import 'swiper/css/effect-fade';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import './main.scss';

const DEFAULTS = {
	effect: 'coverflow',
	speed: 300,
	loop: false,
	autoplay: false,
	pagination: true,
	navigation: false,
	coverflow: { rotate: 50, depth: 100, stretch: 0 },
};

const readConfig = ( element ) => {
	try {
		return {
			...DEFAULTS,
			...JSON.parse( element.dataset.utsConfig || '{}' ),
		};
	} catch {
		return DEFAULTS;
	}
};

const effectOptions = ( config ) => {
	switch ( config.effect ) {
		case 'fade':
			return {
				slidesPerView: 1,
				fadeEffect: { crossFade: true },
			};

		case 'cards':
			return {
				slidesPerView: 1,
				cardsEffect: { slideShadows: false },
			};

		case 'slide':
			return {
				slidesPerView: 'auto',
				centeredSlides: true,
				spaceBetween: 20,
			};

		default:
			return {
				slidesPerView: 'auto',
				centeredSlides: true,
				coverflowEffect: {
					rotate: config.coverflow.rotate,
					stretch: config.coverflow.stretch,
					depth: config.coverflow.depth,
					modifier: 1,
					slideShadows: false,
				},
			};
	}
};

const initSlider = ( element ) => {
	const config = readConfig( element );

	new Swiper( element, {
		modules: [
			Autoplay,
			EffectCards,
			EffectCoverflow,
			EffectFade,
			Navigation,
			Pagination,
		],
		effect: config.effect,
		speed: config.speed,
		loop: config.loop,
		grabCursor: true,
		...effectOptions( config ),
		autoplay: config.autoplay
			? { delay: config.autoplay, pauseOnMouseEnter: true }
			: false,
		pagination: config.pagination
			? {
					el: element.querySelector( '.swiper-pagination' ),
					clickable: true,
				}
			: false,
		navigation: config.navigation
			? {
					prevEl: element.querySelector( '.swiper-button-prev' ),
					nextEl: element.querySelector( '.swiper-button-next' ),
				}
			: false,
	} );
};

document.querySelectorAll( '.ucs-container.slider' ).forEach( initSlider );
