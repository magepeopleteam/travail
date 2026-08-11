/**
 * Travail — Customizer live-preview bindings for settings that are safe
 * to reflect instantly without a full refresh.
 *
 * @package Travail
 */

( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.customize ) {
		return;
	}

	wp.customize( 'travail_footer_description', function ( value ) {
		value.bind( function ( newValue ) {
			document.querySelectorAll( '.travail-footer-brand p' ).forEach( function ( el ) {
				el.textContent = newValue;
			} );
		} );
	} );

	wp.customize( 'travail_color_primary', function ( value ) {
		value.bind( function ( newValue ) {
			document.documentElement.style.setProperty( '--travail-color-primary', newValue );
			document.documentElement.style.setProperty( '--travail-forest', newValue );
		} );
	} );

	wp.customize( 'travail_color_accent', function ( value ) {
		value.bind( function ( newValue ) {
			document.documentElement.style.setProperty( '--travail-color-accent', newValue );
			document.documentElement.style.setProperty( '--travail-coral', newValue );
		} );
	} );
} )( window.wp );
