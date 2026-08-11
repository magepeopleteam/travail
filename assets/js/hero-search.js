/**
 * Travail — homepage hero search widget: guest stepper popover.
 * The form itself is a plain <form method="get">, so search works
 * perfectly with JavaScript disabled; this file only makes the guest
 * field feel like the reference design.
 *
 * @package Travail
 */

( function () {
	'use strict';

	function initGuestStepper( wrapper ) {
		var popover = wrapper.querySelector( '[data-travail-guest-popover]' );
		var trigger = wrapper.querySelector( '[data-travail-guest-trigger]' );
		var display = wrapper.querySelector( '[data-travail-guest-display]' );
		if ( ! popover || ! trigger ) {
			return;
		}

		trigger.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			var isOpen = popover.hidden === false;
			popover.hidden = isOpen;
			trigger.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( ! wrapper.contains( event.target ) ) {
				popover.hidden = true;
				trigger.setAttribute( 'aria-expanded', 'false' );
			}
		} );

		var updateDisplay = function () {
			var total = 0;
			wrapper.querySelectorAll( '[data-travail-guest-input]' ).forEach( function ( input ) {
				total += parseInt( input.value, 10 ) || 0;
			} );
			if ( display ) {
				display.textContent = total > 0
					? total + ' ' + ( 1 === total ? travailSettings.i18n.guestSingular || 'Guest' : travailSettings.i18n.guestPlural || 'Guests' )
					: display.getAttribute( 'data-placeholder' ) || 'Guests';
			}
		};

		wrapper.querySelectorAll( '[data-travail-stepper-btn]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var input = wrapper.querySelector( '[data-travail-guest-input="' + btn.getAttribute( 'data-target' ) + '"]' );
				if ( ! input ) {
					return;
				}
				var min = parseInt( input.getAttribute( 'min' ) || '0', 10 );
				var max = parseInt( input.getAttribute( 'max' ) || '20', 10 );
				var value = parseInt( input.value, 10 ) || 0;
				value += 'increase' === btn.getAttribute( 'data-travail-stepper-btn' ) ? 1 : -1;
				value = Math.max( min, Math.min( max, value ) );
				input.value = value;
				var valueDisplay = wrapper.querySelector( '[data-travail-guest-value="' + btn.getAttribute( 'data-target' ) + '"]' );
				if ( valueDisplay ) {
					valueDisplay.textContent = value;
				}
				updateDisplay();
			} );
		} );

		updateDisplay();
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-travail-guest-field]' ).forEach( initGuestStepper );
	} );
} )();
