/**
 * Travail — global front-end behaviour used on every page: wishlist
 * toggle, category pills, testimonial slider, generic accordions.
 *
 * @package Travail
 */

( function () {
	'use strict';

	window.Travail = window.Travail || {};

	/**
	 * Wishlist heart buttons on Travail's own tour cards (see
	 * template-parts/tour/tour-card.php, used by the homepage
	 * "Popular experiences" rail). Tour Booking Manager's own
	 * [ttbm-tour-list] shortcode markup ships its own wishlist button
	 * already wired to the same AJAX action — this only handles
	 * Travail's custom card, which reuses the plugin's real
	 * `ttbm_wishlist_toggle` AJAX action/nonce so it actually persists.
	 */
	function initWishlistButtons() {
		var buttons = document.querySelectorAll( '[data-travail-wishlist-toggle]' );
		if ( ! buttons.length || 'undefined' === typeof travailSettings || ! travailSettings.wishlistNonce ) {
			return;
		}

		buttons.forEach( function ( button ) {
			button.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				var tourId = button.getAttribute( 'data-tour-id' );
				if ( ! tourId || button.disabled ) {
					return;
				}

				button.disabled = true;
				var formData = new FormData();
				formData.append( 'action', 'ttbm_wishlist_toggle' );
				formData.append( 'nonce', travailSettings.wishlistNonce );
				formData.append( 'tour_id', tourId );

				fetch( travailSettings.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' } )
					.then( function ( response ) {
						return response.json();
					} )
					.then( function ( data ) {
						if ( data && data.success ) {
							var inWishlist = !! data.data.in_wishlist;
							button.setAttribute( 'aria-pressed', inWishlist ? 'true' : 'false' );
							var svg = button.querySelector( 'svg' );
							if ( svg ) {
								svg.setAttribute( 'fill', inWishlist ? 'currentColor' : 'none' );
							}
						} else if ( data && data.data && data.data.need_login ) {
							window.location.href = travailSettings.myAccountUrl || '#';
						}
					} )
					.catch( function () {
						// Network/permission failure — leave the button's state unchanged rather than guessing.
					} )
					.finally( function () {
						button.disabled = false;
					} );
			} );
		} );
	}

	/**
	 * Generic accordion toggler — powers FAQ items and the itinerary
	 * day-by-day list. Looks for [data-travail-accordion-toggle].
	 */
	function initAccordions() {
		document.querySelectorAll( '[data-travail-accordion-toggle]' ).forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				var item = toggle.closest( '[data-travail-accordion-item]' );
				if ( ! item ) {
					return;
				}
				var isOpen = item.classList.contains( 'is-open' );
				var group = item.closest( '[data-travail-accordion-group]' );

				if ( group && group.getAttribute( 'data-travail-accordion-group' ) === 'single' ) {
					group.querySelectorAll( '[data-travail-accordion-item]' ).forEach( function ( sibling ) {
						sibling.classList.remove( 'is-open' );
						var siblingToggle = sibling.querySelector( '[data-travail-accordion-toggle]' );
						if ( siblingToggle ) {
							siblingToggle.setAttribute( 'aria-expanded', 'false' );
						}
					} );
				}

				item.classList.toggle( 'is-open', ! isOpen );
				toggle.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
			} );
		} );
	}

	/**
	 * Category pill / filter chip "active" state (visual only — actual
	 * filtering is handled server-side via query args or by
	 * tour-booking.js on the archive page).
	 */
	function initActivePills() {
		document.querySelectorAll( '[data-travail-pill-group]' ).forEach( function ( group ) {
			group.querySelectorAll( '.travail-pill, .travail-cat-card' ).forEach( function ( pill ) {
				pill.addEventListener( 'click', function ( event ) {
					if ( pill.tagName === 'A' && pill.getAttribute( 'href' ) && '#' !== pill.getAttribute( 'href' ) ) {
						return; // Let real links navigate.
					}
					event.preventDefault();
					group.querySelectorAll( '.is-active' ).forEach( function ( active ) {
						active.classList.remove( 'is-active' );
					} );
					pill.classList.add( 'is-active' );
				} );
			} );
		} );
	}

	/**
	 * Minimal testimonial slider — cross-fades slides on an interval and
	 * via dot navigation. No external carousel library.
	 */
	function initTestimonialSlider() {
		document.querySelectorAll( '[data-travail-testimonial-slider]' ).forEach( function ( slider ) {
			var slides = slider.querySelectorAll( '.travail-testi-slide' );
			var dots = slider.querySelectorAll( '.travail-testi-dots button' );
			if ( slides.length < 2 ) {
				return;
			}

			var index = 0;
			var show = function ( next ) {
				slides[ index ].classList.remove( 'is-active' );
				if ( dots[ index ] ) {
					dots[ index ].classList.remove( 'is-active' );
				}
				index = ( next + slides.length ) % slides.length;
				slides[ index ].classList.add( 'is-active' );
				if ( dots[ index ] ) {
					dots[ index ].classList.add( 'is-active' );
				}
			};

			dots.forEach( function ( dot, i ) {
				dot.addEventListener( 'click', function () {
					show( i );
				} );
			} );

			if ( ! window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
				setInterval( function () {
					show( index + 1 );
				}, 6000 );
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initWishlistButtons();
		initAccordions();
		initActivePills();
		initTestimonialSlider();
	} );
} )();
