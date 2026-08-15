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

	function setSubmenuState( item, isOpen ) {
		var link = item.querySelector( ':scope > a' );
		var button = item.querySelector( ':scope > .travail-submenu-toggle' );
		item.classList.toggle( 'is-open', isOpen );
		if ( link ) {
			link.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		}
		if ( button ) {
			button.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		}
	}

	function closeSiblingSubmenus( item ) {
		var parent = item.parentElement;
		if ( ! parent ) {
			return;
		}
		Array.prototype.forEach.call( parent.children, function ( sibling ) {
			if ( sibling !== item && sibling.classList.contains( 'menu-item-has-children' ) ) {
				setSubmenuState( sibling, false );
				sibling.querySelectorAll( '.menu-item-has-children.is-open' ).forEach( function ( nested ) {
					setSubmenuState( nested, false );
				} );
			}
		} );
	}

	function initDropdowns() {
		var menus = document.querySelectorAll( '.travail-nav, .travail-mobile-menu, .travail-travello-nav, .travail-travello-mobile-nav' );
		if ( ! menus.length ) {
			return;
		}

		menus.forEach( function ( menu ) {
			menu.querySelectorAll( '.menu-item-has-children' ).forEach( function ( item ) {
				var link = item.querySelector( ':scope > a' );
				if ( ! link || item.querySelector( ':scope > .travail-submenu-toggle' ) ) {
					return;
				}

				link.setAttribute( 'aria-haspopup', 'true' );
				link.setAttribute( 'aria-expanded', 'false' );

				var button = document.createElement( 'button' );
				button.type = 'button';
				button.className = 'travail-submenu-toggle';
				button.setAttribute( 'aria-expanded', 'false' );
				button.setAttribute( 'aria-label', ( link.textContent || '' ).trim() + ' submenu' );
				item.insertBefore( button, link.nextSibling );

				button.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					event.stopPropagation();
					var willOpen = ! item.classList.contains( 'is-open' );
					closeSiblingSubmenus( item );
					setSubmenuState( item, willOpen );
				} );
			} );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( '.menu-item-has-children' ) ) {
				return;
			}
			document.querySelectorAll( '.menu-item-has-children.is-open' ).forEach( function ( item ) {
				setSubmenuState( item, false );
			} );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' !== event.key ) {
				return;
			}
			document.querySelectorAll( '.menu-item-has-children.is-open' ).forEach( function ( item ) {
				setSubmenuState( item, false );
			} );
		} );
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
			if ( ! isOpen ) {
				menu.querySelectorAll( '.menu-item-has-children.is-open' ).forEach( function ( item ) {
					setSubmenuState( item, false );
				} );
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && menu.classList.contains( 'is-open' ) ) {
				menu.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				document.body.classList.remove( 'travail-menu-open' );
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
		initDropdowns();
		initMobileMenu();
		initSearchModal();
	} );
} )();
