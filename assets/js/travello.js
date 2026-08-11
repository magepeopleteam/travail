/**
 * Travello homepage — interactions not already covered by the theme's
 * global scripts. Header scroll-shadow and the search modal are
 * intentionally NOT duplicated here: the Travello header shares the
 * exact [data-travail-header]/data-sticky contract and
 * #travail-search-toggle/#travail-search-modal ids as the default
 * header, so assets/js/navigation.js already handles both. Wishlist
 * buttons share main.js's [data-travail-wishlist-toggle] contract for
 * the same reason.
 *
 * Only enqueued on the Travello homepage — see inc/enqueue.php.
 *
 * @package Travail
 */

( function () {
	'use strict';

	/**
	 * Mobile nav drawer toggle.
	 */
	function initMobileMenu() {
		var toggle = document.getElementById( 'travello-mobile-menu-btn' );
		var menu = document.getElementById( 'travello-mobile-nav' );
		if ( ! toggle || ! menu ) {
			return;
		}

		toggle.addEventListener( 'click', function () {
			var isOpen = menu.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && menu.classList.contains( 'is-open' ) ) {
				menu.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	}

	/**
	 * Search tabs — visual-only switch (every tab submits the same tour
	 * search; see search.php's docblock for why).
	 */
	function initSearchTabs() {
		var wrap = document.querySelector( '[data-travello-search-tabs]' );
		if ( ! wrap ) {
			return;
		}

		wrap.addEventListener( 'click', function ( event ) {
			var btn = event.target.closest( '.travail-travello-search-tab' );
			if ( ! btn ) {
				return;
			}
			wrap.querySelectorAll( '.travail-travello-search-tab' ).forEach( function ( tab ) {
				tab.classList.remove( 'is-active' );
				tab.setAttribute( 'aria-pressed', 'false' );
			} );
			btn.classList.add( 'is-active' );
			btn.setAttribute( 'aria-pressed', 'true' );
		} );
	}

	/**
	 * Closes every open popup trigger/panel pair except the one passed in
	 * (if any), so opening one field's popup closes any other that was
	 * already open.
	 *
	 * @param {Element} [except] Trigger element to leave open.
	 */
	function closeAllPopups( except ) {
		document.querySelectorAll( '[data-travello-popup-trigger]' ).forEach( function ( trigger ) {
			if ( trigger === except ) {
				return;
			}
			trigger.classList.remove( 'is-active' );
			var panel = document.getElementById( trigger.getAttribute( 'data-travello-popup-trigger' ) );
			if ( panel ) {
				panel.classList.remove( 'is-open' );
			}
		} );
	}

	/**
	 * Wires a field-input's click to open/close its associated popup.
	 * The actual popup content (calendar/travelers) is built by the
	 * dedicated init functions below; this only owns show/hide + the
	 * "close others" behaviour shared by both.
	 */
	function initPopupTriggers() {
		var triggers = document.querySelectorAll( '[data-travello-popup-trigger]' );
		if ( ! triggers.length ) {
			return;
		}

		triggers.forEach( function ( trigger ) {
			trigger.addEventListener( 'click', function ( event ) {
				event.stopPropagation();
				var panel = document.getElementById( trigger.getAttribute( 'data-travello-popup-trigger' ) );
				if ( ! panel ) {
					return;
				}
				var wasOpen = panel.classList.contains( 'is-open' );
				closeAllPopups( wasOpen ? null : trigger );
				panel.classList.toggle( 'is-open', ! wasOpen );
				trigger.classList.toggle( 'is-active', ! wasOpen );
			} );
		} );

		document.addEventListener( 'click', function () {
			closeAllPopups( null );
		} );
	}

	/**
	 * Lightweight month calendar — writes a formatted date string into
	 * the real, plugin-facing text input (id given by data-travello
	 * -calendar-for) rather than only updating a decorative label, so
	 * the value still submits with the form.
	 */
	function initCalendar() {
		var popup = document.getElementById( 'travello-cal-popup' );
		if ( ! popup ) {
			return;
		}

		var targetId = popup.getAttribute( 'data-travello-calendar-for' );
		var target = targetId ? document.getElementById( targetId ) : null;
		if ( ! target ) {
			return;
		}

		var today = new Date();
		var month = today.getMonth();
		var year = today.getFullYear();
		var selectedDay = null;
		var monthNames = [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ];

		function render() {
			var daysInMonth = new Date( year, month + 1, 0 ).getDate();
			var firstDay = new Date( year, month, 1 ).getDay();
			var html = '<div class="travail-travello-cal-header">'
				+ '<button type="button" class="travail-travello-cal-nav" data-cal-prev aria-label="Previous month">‹</button>'
				+ '<span class="travail-travello-cal-month-label">' + monthNames[ month ] + ' ' + year + '</span>'
				+ '<button type="button" class="travail-travello-cal-nav" data-cal-next aria-label="Next month">›</button>'
				+ '</div><div class="travail-travello-cal-grid">';

			[ 'Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa' ].forEach( function ( d ) {
				html += '<div class="travail-travello-cal-day-name">' + d + '</div>';
			} );
			for ( var i = 0; i < firstDay; i++ ) {
				html += '<div></div>';
			}

			var isPastMonth = year < today.getFullYear() || ( year === today.getFullYear() && month < today.getMonth() );
			for ( var d = 1; d <= daysInMonth; d++ ) {
				var isPast = isPastMonth || ( year === today.getFullYear() && month === today.getMonth() && d < today.getDate() );
				var classes = 'travail-travello-cal-day';
				if ( selectedDay === d ) {
					classes += ' is-selected';
				}
				if ( isPast ) {
					classes += ' is-past';
				}
				html += '<button type="button" class="' + classes + '" data-day="' + d + '">' + d + '</button>';
			}
			html += '</div>';
			popup.innerHTML = html;

			var prevBtn = popup.querySelector( '[data-cal-prev]' );
			var nextBtn = popup.querySelector( '[data-cal-next]' );
			if ( prevBtn ) {
				prevBtn.addEventListener( 'click', function ( event ) {
					event.stopPropagation();
					if ( 0 === month ) {
						month = 11;
						year--;
					} else {
						month--;
					}
					render();
				} );
			}
			if ( nextBtn ) {
				nextBtn.addEventListener( 'click', function ( event ) {
					event.stopPropagation();
					if ( 11 === month ) {
						month = 0;
						year++;
					} else {
						month++;
					}
					render();
				} );
			}
			popup.querySelectorAll( '.travail-travello-cal-day:not(.is-past)' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function ( event ) {
					event.stopPropagation();
					selectedDay = parseInt( btn.getAttribute( 'data-day' ), 10 );
					target.value = monthNames[ month ] + ' ' + selectedDay + ', ' + year;
					popup.classList.remove( 'is-open' );
					var trigger = document.querySelector( '[data-travello-popup-trigger="' + popup.id + '"]' );
					if ( trigger ) {
						trigger.classList.remove( 'is-active' );
					}
				} );
			} );
		}

		render();
	}

	/**
	 * "Where to?" destination dropdown. Selecting a real destination sets
	 * the visible text plus the hidden location_filter (term id) the
	 * search form actually submits, and remembers the pick in
	 * localStorage so "Recently Searched" reflects this browser's own
	 * real history instead of a hardcoded example. Typing free text
	 * instead of picking a suggestion falls back to the hidden
	 * title_filter field (a real, if less precise, search) rather than
	 * silently submitting nothing.
	 */
	function initDestinationDropdown() {
		var input = document.getElementById( 'travello-dest-value' );
		var filterField = document.getElementById( 'travello-dest-filter' );
		var titleField = document.getElementById( 'travello-dest-title-filter' );
		var dropdown = document.getElementById( 'travello-dest-dropdown' );
		if ( ! input || ! dropdown ) {
			return;
		}

		var STORAGE_KEY = 'travailTravelloRecentDestinations';

		function getRecent() {
			try {
				var raw = window.localStorage.getItem( STORAGE_KEY );
				return raw ? JSON.parse( raw ) : [];
			} catch ( e ) {
				return [];
			}
		}

		function renderRecent() {
			var section = document.getElementById( 'travello-dest-recent-section' );
			var list = document.getElementById( 'travello-dest-recent-list' );
			if ( ! section || ! list ) {
				return;
			}
			var recent = getRecent();
			if ( ! recent.length ) {
				section.hidden = true;
				return;
			}
			list.textContent = '';
			recent.forEach( function ( item ) {
				var btn = document.createElement( 'button' );
				btn.type = 'button';
				btn.className = 'travail-travello-dropdown-item';
				btn.setAttribute( 'data-dest-id', item.id );
				btn.setAttribute( 'data-dest-name', item.name );

				var icon = document.createElement( 'span' );
				icon.className = 'travail-travello-dropdown-icon';
				icon.setAttribute( 'aria-hidden', 'true' );
				icon.textContent = '🕐';

				btn.appendChild( icon );
				btn.appendChild( document.createTextNode( item.name ) );
				list.appendChild( btn );
			} );
			section.hidden = false;
		}

		function remember( id, name ) {
			var recent = getRecent().filter( function ( item ) {
				return item.id !== id;
			} );
			recent.unshift( { id: id, name: name } );
			try {
				window.localStorage.setItem( STORAGE_KEY, JSON.stringify( recent.slice( 0, 3 ) ) );
			} catch ( e ) {
				// Storage unavailable (private mode, quota) — recent list just won't persist.
			}
		}

		function selectDestination( id, name ) {
			input.value = name;
			filterField.value = id;
			titleField.value = '';
			remember( id, name );
			dropdown.classList.remove( 'is-open' );
			var trigger = document.getElementById( 'travello-dest-input' );
			if ( trigger ) {
				trigger.classList.remove( 'is-active' );
			}
		}

		dropdown.addEventListener( 'click', function ( event ) {
			var item = event.target.closest( '[data-dest-id]' );
			if ( ! item ) {
				return;
			}
			event.stopPropagation();
			selectDestination( item.getAttribute( 'data-dest-id' ), item.getAttribute( 'data-dest-name' ) );
		} );

		input.addEventListener( 'input', function () {
			filterField.value = '';
			titleField.value = input.value;
		} );

		renderRecent();
	}

	/**
	 * Adults/children/infants counter popup — sums into the real hidden
	 * `people_filter` input the search form submits.
	 */
	function initTravelers() {
		var popup = document.getElementById( 'travello-travelers-popup' );
		var display = document.getElementById( 'travello-travelers-display' );
		var countInput = document.getElementById( 'travello-travelers-count' );
		if ( ! popup || ! display || ! countInput ) {
			return;
		}

		var counts = { adults: 2, children: 0, infants: 0 };

		function updateLabel() {
			var parts = [ counts.adults + ( 1 === counts.adults ? ' Adult' : ' Adults' ) ];
			if ( counts.children > 0 ) {
				parts.push( counts.children + ( 1 === counts.children ? ' Child' : ' Children' ) );
			}
			if ( counts.infants > 0 ) {
				parts.push( counts.infants + ( 1 === counts.infants ? ' Infant' : ' Infants' ) );
			}
			display.value = parts.join( ', ' );
			countInput.value = counts.adults + counts.children + counts.infants;
		}

		popup.addEventListener( 'click', function ( event ) {
			var btn = event.target.closest( '[data-action]' );
			if ( ! btn ) {
				return;
			}
			event.stopPropagation();
			var type = btn.getAttribute( 'data-type' );
			var action = btn.getAttribute( 'data-action' );
			if ( ! ( type in counts ) ) {
				return;
			}
			if ( 'inc' === action ) {
				counts[ type ]++;
			} else if ( 'dec' === action && counts[ type ] > ( 'adults' === type ? 1 : 0 ) ) {
				counts[ type ]--;
			}
			var valEl = popup.querySelector( '[data-type-val="' + type + '"]' );
			if ( valEl ) {
				valEl.textContent = counts[ type ];
			}
			updateLabel();
		} );

		updateLabel();
	}

	/**
	 * Newsletter form — real AJAX POST (nonce-verified server-side, see
	 * inc/homepage-travello.php) instead of a client-side-only illusion
	 * of success.
	 */
	function initNewsletterForm() {
		var form = document.querySelector( '[data-travello-newsletter-form]' );
		var status = document.getElementById( 'travello-newsletter-status' );
		if ( ! form || 'undefined' === typeof travailSettings ) {
			return;
		}

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			var button = form.querySelector( 'button[type="submit"]' );
			var formData = new FormData( form );
			formData.append( 'action', 'travail_travello_newsletter' );
			formData.set( 'nonce', form.querySelector( '#travello_newsletter_nonce' ) ? form.querySelector( '#travello_newsletter_nonce' ).value : '' );

			if ( button ) {
				button.disabled = true;
			}
			if ( status ) {
				status.textContent = '';
				status.removeAttribute( 'data-state' );
			}

			fetch( travailSettings.ajaxUrl, { method: 'POST', body: formData, credentials: 'same-origin' } )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( data ) {
					if ( status ) {
						status.textContent = data && data.data && data.data.message ? data.data.message : '';
						status.setAttribute( 'data-state', data && data.success ? 'success' : 'error' );
					}
					if ( data && data.success ) {
						form.reset();
					}
				} )
				.catch( function () {
					if ( status ) {
						status.textContent = travailSettings.i18n && travailSettings.i18n.genericError ? travailSettings.i18n.genericError : 'Something went wrong.';
						status.setAttribute( 'data-state', 'error' );
					}
				} )
				.finally( function () {
					if ( button ) {
						button.disabled = false;
					}
				} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initMobileMenu();
		initSearchTabs();
		initPopupTriggers();
		initDestinationDropdown();
		initCalendar();
		initTravelers();
		initNewsletterForm();
	} );
} )();
