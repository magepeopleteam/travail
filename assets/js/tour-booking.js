/**
 * Travail — tour archive (filter drawer, view/sort toggles) and single
 * tour booking card (guest steppers, extra services, live total).
 *
 * This file intentionally does NOT calculate real prices/availability —
 * it only mirrors values the server already rendered into data
 * attributes, and the actual booking submission is handled by whatever
 * form action/AJAX action Tour Booking Manager (Pro) registers. See
 * inc/compatibility/tour-booking-manager.php for how those hooks are
 * respected instead of duplicated.
 *
 * @package Travail
 */

( function () {
	'use strict';

	/* ── Archive: mobile filter drawer ─────────────────────────── */
	function initFilterDrawer() {
		var openBtn = document.querySelector( '[data-travail-filter-open]' );
		var drawer = document.querySelector( '.travail-filter-sidebar' );
		var backdrop = document.querySelector( '.travail-filter-drawer-backdrop' );
		var closeBtn = document.querySelector( '[data-travail-filter-close]' );

		if ( ! openBtn || ! drawer ) {
			return;
		}

		var open = function () {
			drawer.classList.add( 'is-open' );
			if ( backdrop ) {
				backdrop.classList.add( 'is-open' );
			}
		};
		var close = function () {
			drawer.classList.remove( 'is-open' );
			if ( backdrop ) {
				backdrop.classList.remove( 'is-open' );
			}
		};

		openBtn.addEventListener( 'click', open );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', close );
		}
		if ( backdrop ) {
			backdrop.addEventListener( 'click', close );
		}
	}

	/* ── Archive: grid/list view toggle (persisted in the URL) ─── */
	function initViewToggle() {
		var toggle = document.querySelector( '.travail-view-toggle' );
		var grid = document.querySelector( '[data-travail-tour-grid]' );
		if ( ! toggle || ! grid ) {
			return;
		}

		toggle.querySelectorAll( 'button' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				toggle.querySelectorAll( 'button' ).forEach( function ( b ) {
					b.classList.remove( 'is-active' );
					b.setAttribute( 'aria-pressed', 'false' );
				} );
				btn.classList.add( 'is-active' );
				btn.setAttribute( 'aria-pressed', 'true' );
				grid.classList.toggle( 'travail-tour-grid--list', 'list' === btn.getAttribute( 'data-view' ) );

				try {
					var url = new URL( window.location.href );
					url.searchParams.set( 'view', btn.getAttribute( 'data-view' ) );
					window.history.replaceState( {}, '', url );
				} catch ( e ) {
					// Older browsers without URL() support silently keep server-rendered state.
				}
			} );
		} );
	}

	/* ── Archive: sort select reloads with a query arg ─────────── */
	function initSortSelect() {
		var select = document.querySelector( '.travail-sort-select' );
		if ( ! select ) {
			return;
		}
		select.addEventListener( 'change', function () {
			try {
				var url = new URL( window.location.href );
				url.searchParams.set( 'orderby', select.value );
				window.location.href = url.toString();
			} catch ( e ) {
				select.form && select.form.submit();
			}
		} );
	}

	/* ── Single tour: booking card guest steppers + live total ─── */
	function initBookingCard() {
		var card = document.querySelector( '[data-travail-booking-card]' );
		if ( ! card ) {
			return;
		}

		var basePrice = parseFloat( card.getAttribute( 'data-base-price' ) || '0' );
		var totalEl = card.querySelector( '[data-travail-total]' );
		var currency = card.getAttribute( 'data-currency-symbol' ) || '$';

		var recalculate = function () {
			var guestCount = 0;
			card.querySelectorAll( '[data-travail-guest-input]' ).forEach( function ( input ) {
				guestCount += parseInt( input.value, 10 ) || 0;
			} );
			guestCount = Math.max( guestCount, 1 );

			var total = basePrice * guestCount;

			card.querySelectorAll( '[data-travail-extra-service]' ).forEach( function ( checkbox ) {
				if ( checkbox.checked ) {
					total += parseFloat( checkbox.getAttribute( 'data-price' ) || '0' );
				}
			} );

			if ( totalEl ) {
				totalEl.textContent = currency + total.toFixed( 2 );
			}

			var hiddenGuests = card.querySelector( 'input[name="ttbm_guest_total"]' );
			if ( hiddenGuests ) {
				hiddenGuests.value = guestCount;
			}
		};

		card.querySelectorAll( '[data-travail-stepper-btn]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var input = card.querySelector( '[data-travail-guest-input="' + btn.getAttribute( 'data-target' ) + '"]' );
				if ( ! input ) {
					return;
				}
				var min = parseInt( input.getAttribute( 'min' ) || '0', 10 );
				var max = parseInt( input.getAttribute( 'max' ) || '20', 10 );
				var value = ( parseInt( input.value, 10 ) || 0 ) + ( 'increase' === btn.getAttribute( 'data-travail-stepper-btn' ) ? 1 : -1 );
				value = Math.max( min, Math.min( max, value ) );
				input.value = value;
				var valueDisplay = card.querySelector( '[data-travail-guest-value="' + btn.getAttribute( 'data-target' ) + '"]' );
				if ( valueDisplay ) {
					valueDisplay.textContent = value;
				}
				recalculate();
			} );
		} );

		card.querySelectorAll( '[data-travail-extra-service]' ).forEach( function ( checkbox ) {
			checkbox.addEventListener( 'change', recalculate );
		} );

		var mobileBar = card.querySelector( '.travail-booking-card__mobile-bar' );
		if ( mobileBar ) {
			mobileBar.addEventListener( 'click', function () {
				card.classList.toggle( 'is-collapsed' );
			} );
		}

		recalculate();
	}

	/* ── Single tour: simple gallery lightbox ──────────────────── */
	function initGalleryLightbox() {
		var gallery = document.querySelector( '.travail-tour-gallery' );
		if ( ! gallery ) {
			return;
		}
		gallery.querySelectorAll( 'img' ).forEach( function ( img ) {
			img.addEventListener( 'click', function () {
				var full = img.getAttribute( 'data-full' ) || img.src;
				var overlay = document.createElement( 'div' );
				overlay.className = 'travail-lightbox';
				overlay.setAttribute( 'role', 'dialog' );
				overlay.setAttribute( 'aria-modal', 'true' );
				overlay.innerHTML = '<img src="' + full + '" alt="" /><button type="button" aria-label="Close" class="travail-lightbox__close">&times;</button>';
				overlay.style.cssText = 'position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.9);display:flex;align-items:center;justify-content:center;';
				overlay.querySelector( 'img' ).style.cssText = 'max-width:90vw;max-height:90vh;object-fit:contain;';
				overlay.querySelector( 'button' ).style.cssText = 'position:absolute;top:20px;right:20px;color:#fff;font-size:32px;background:none;border:none;cursor:pointer;';
				document.body.appendChild( overlay );
				var close = function () {
					overlay.remove();
				};
				overlay.addEventListener( 'click', function ( e ) {
					if ( e.target === overlay || e.target.closest( '.travail-lightbox__close' ) ) {
						close();
					}
				} );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initFilterDrawer();
		initViewToggle();
		initSortSelect();
		initBookingCard();
		initGalleryLightbox();
	} );
} )();
