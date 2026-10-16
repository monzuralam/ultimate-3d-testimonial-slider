/**
 * Frontend: initializes Swiper on every slider container, using the
 * options from its data-uts-config attribute (see uts_slider_config()),
 * and lays out masonry containers.
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

// Swiper 11's loop mode needs more slides than are visible at once (about
// 3 visible + 2 for the centered coverflow/slide effects; cards needs about
// 9 in practice) and does not duplicate them itself. With fewer testimonials, the whole set is
// repeated; the copies are hidden from screen readers and the dots still
// show one per testimonial.
const LOOP_MIN_SLIDES = { auto: 9, cards: 9, single: 5 };

const addLoopCopies = ( element, config ) => {
	const wrapper = element.querySelector( '.swiper-wrapper' );
	wrapper
		.querySelectorAll( '.ucs-loop-copy' )
		.forEach( ( copy ) => copy.remove() );

	const originals = Array.from( wrapper.children );
	if ( ! config.loop || ! originals.length ) {
		return originals.length;
	}

	let min = LOOP_MIN_SLIDES.single;
	if ( 'cards' === config.effect ) {
		min = LOOP_MIN_SLIDES.cards;
	} else if ( 'auto' === effectOptions( config ).slidesPerView ) {
		min = LOOP_MIN_SLIDES.auto;
	}
	const sets = Math.ceil( min / originals.length );
	for ( let set = 1; set < sets; set++ ) {
		originals.forEach( ( slide ) => {
			const copy = slide.cloneNode( true );
			copy.classList.add( 'ucs-loop-copy' );
			copy.setAttribute( 'aria-hidden', 'true' );
			wrapper.appendChild( copy );
		} );
	}

	return originals.length;
};

// One dot per testimonial when the slides were repeated for the loop.
const syncLoopDots = ( swiper, count ) => {
	const el = swiper.pagination && swiper.pagination.el;
	if ( ! el || ! count ) {
		return;
	}
	el.querySelectorAll( '.swiper-pagination-bullet' ).forEach(
		( bullet, index ) => {
			// Inline style: Swiper's CSS (display: inline-block) would override [hidden].
			bullet.style.display = index >= count ? 'none' : '';
			bullet.classList.toggle(
				'swiper-pagination-bullet-active',
				index === swiper.realIndex % count
			);
		}
	);
};

const initSlider = ( element ) => {
	const config = readConfig( element );

	// Re-initializing (e.g. the admin preview) replaces the old instance.
	if ( element.swiper ) {
		element.swiper.destroy( true, true );
	}

	if ( prefersReducedMotion() ) {
		config.autoplay = false;
	}

	const count = addLoopCopies( element, config );
	const dots = ( swiper ) => syncLoopDots( swiper, count );

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
		on: {
			paginationRender: dots,
			paginationUpdate: dots,
		},
	} );
};

// Masonry: each card spans as many 1px grid rows as it is tall (plus the
// gap), so cards keep their own height and still fill the columns from left
// to right. Without JS the layout is a plain grid.
const MASONRY_GAP = 20;

const layoutMasonry = ( element ) => {
	element.classList.add( 'ucs-masonry-ready' );
	element.querySelectorAll( '.ucs-item' ).forEach( ( item ) => {
		const height = Math.ceil( item.getBoundingClientRect().height );
		item.style.gridRowEnd = `span ${ height + MASONRY_GAP }`;
	} );
};

const initMasonry = ( element ) => {
	if ( element.utsMasonry ) {
		element.utsMasonry.disconnect();
	}
	layoutMasonry( element );

	// Cards change height when images load or the width changes.
	if ( window.ResizeObserver ) {
		const observer = new window.ResizeObserver( () =>
			layoutMasonry( element )
		);
		element
			.querySelectorAll( '.ucs-item' )
			.forEach( ( item ) => observer.observe( item ) );
		element.utsMasonry = observer;
	}
};

/**
 * Initialize every slider (and lay out every masonry) inside `root`. Public as `window.utsSlider.init()`
 * for markup added after page load (the admin preview, AJAX-loaded content).
 *
 * @param {Element|Document} root Element to search in. Defaults to the document.
 * @return {Swiper[]} The slider instances.
 */
const init = ( root = document ) => {
	root.querySelectorAll( '.ucs-container.masonry' ).forEach( initMasonry );

	return Array.from( root.querySelectorAll( '.ucs-container.slider' ) ).map(
		initSlider
	);
};

window.utsSlider = { init };

init();
