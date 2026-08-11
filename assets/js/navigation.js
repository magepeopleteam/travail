/**
 * Travail — header/navigation behaviour: sticky header, mobile menu,
 * search modal. Vanilla JS, no framework, namespaced under `Travail`.
 *
 * @package Travail
 */

( function () {
	'use strict';

	window.Travail = window.Travail || {};

	function initStickyHeader() {
		var header = document.querySelector( '[data-travail-header]' );
		if ( ! header || '1' !== header.getAttribute( 'data-sticky' ) ) {
			return;
		}

		var onScroll = function () {
			if ( window.scrollY > 60 ) {
				header.classList.add( 'is-scrolled' );
			} else {
				header.classList.remove( 'is-scrolled' );
			}
		};

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	function initMobileMenu() {
		var toggle = document.getElementById( 'travail-menu-toggle' );
		var menu = document.getElementById( 'travail-mobile-menu' );
		if ( ! toggle || ! menu ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var isOpen = menu.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			document.body.classList.toggle( 'travail-menu-open', isOpen );
		} );

		// Close on Escape.
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && menu.classList.contains( 'is-open' ) ) {
				menu.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	}

	function initSearchModal() {
		var toggle = document.getElementById( 'travail-search-toggle' );
		var modal = document.getElementById( 'travail-search-modal' );
		var closeBtn = document.getElementById( 'travail-search-close' );
		if ( ! toggle || ! modal ) {
			return;
		}

		var open = function () {
			modal.hidden = false;
			toggle.setAttribute( 'aria-expanded', 'true' );
			var input = modal.querySelector( 'input[type="search"]' );
			if ( input ) {
				input.focus();
			}
		};

		var close = function () {
			modal.hidden = true;
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.focus();
		};

		toggle.addEventListener( 'click', open );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', close );
		}

		modal.addEventListener( 'click', function ( event ) {
			if ( event.target === modal ) {
				close();
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! modal.hidden ) {
				close();
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initStickyHeader();
		initMobileMenu();
		initSearchModal();
	} );
} )();
