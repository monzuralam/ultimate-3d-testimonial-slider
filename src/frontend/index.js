/**
 * Frontend: initializes Swiper on every slider container, using the
 * options from its data-uts-config attribute (see uts_slider_config()).
 */
import Swiper from 'swiper';
import {
	Autoplay,
	EffectCards,
	EffectCoverflow,
	EffectCreative,
	EffectCube,
	EffectFade,
	EffectFlip,
	Navigation,
	Pagination,
} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-cards';
import 'swiper/css/effect-coverflow';
import 'swiper/css/effect-creative';
import 'swiper/css/effect-cube';
import 'swiper/css/effect-fade';
import 'swiper/css/effect-flip';
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

		case 'cube':
			// 3D cube turning to the next card.
			return {
				slidesPerView: 1,
				cubeEffect: { shadow: false, slideShadows: false },
			};

		case 'flip':
			// Card flips over to show the next one.
			return {
				slidesPerView: 1,
				flipEffect: { slideShadows: false },
			};

		case 'creative':
			// Current card slides off and back while the next one comes in.
			return {
				slidesPerView: 1,
				creativeEffect: {
					prev: { shadow: false, translate: [ '-120%', 0, -500 ] },
					next: { shadow: false, translate: [ '120%', 0, -500 ] },
				},
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

// Respect the visitor's "reduce motion" setting: no autoplay.
const prefersReducedMotion = () =>
	!! window.matchMedia &&
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

const initSlider = ( element ) => {
	const config = readConfig( element );

	// Re-initializing (e.g. the admin preview) replaces the old instance.
	if ( element.swiper ) {
		element.swiper.destroy( true, true );
	}

	if ( prefersReducedMotion() ) {
		config.autoplay = false;
	}

	return new Swiper( element, {
		modules: [
			Autoplay,
			EffectCards,
			EffectCoverflow,
			EffectCreative,
			EffectCube,
			EffectFade,
			EffectFlip,
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

/**
 * Initialize every slider inside `root`. Public as `window.utsSlider.init()`
 * for markup added after page load (the admin preview, AJAX-loaded content).
 *
 * @param {Element|Document} root Element to search in. Defaults to the document.
 * @return {Swiper[]} The slider instances.
 */
const init = ( root = document ) =>
	Array.from( root.querySelectorAll( '.ucs-container.slider' ) ).map(
		initSlider
	);

window.utsSlider = { init };

init();
