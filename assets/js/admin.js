/**
 * Travail — admin screens: plugin install/activate buttons on the
 * "Recommended Plugins" page. Uses jQuery since it's already loaded on
 * every wp-admin screen (no extra dependency).
 *
 * @package Travail
 */

/* global travailAdmin, jQuery */
( function ( $ ) {
	'use strict';

	if ( 'undefined' === typeof travailAdmin ) {
		return;
	}

	function setBusy( $card, busy ) {
		$card.find( 'button' ).prop( 'disabled', busy );
		$card.find( '.travail-plugin-spinner' ).toggleClass( 'is-active', busy );
	}

	function handleResponse( $card, response, successMessage ) {
		var $message = $card.find( '.travail-plugin-message' );
		if ( response && response.success ) {
			$message.css( 'color', '#2e7d32' ).text( successMessage );
			setTimeout( function () {
				window.location.reload();
			}, 900 );
		} else {
			var msg = ( response && response.data && response.data.message ) || travailAdmin.genericError || 'Something went wrong.';
			$message.css( 'color', '#c0392b' ).text( msg );
		}
	}

	$( document ).on( 'click', '.travail-install-plugin', function () {
		var $btn = $( this );
		var $card = $btn.closest( '.travail-plugin-card' );
		setBusy( $card, true );

		$.post( travailAdmin.ajaxUrl, {
			action: 'travail_install_plugin',
			nonce: travailAdmin.nonce,
			slug: $btn.data( 'slug' ),
		} )
			.done( function ( response ) {
				handleResponse( $card, response, 'Installed & activated!' );
			} )
			.fail( function () {
				handleResponse( $card, null, '' );
			} )
			.always( function () {
				setBusy( $card, false );
			} );
	} );

	$( document ).on( 'click', '.travail-activate-plugin', function () {
		var $btn = $( this );
		var $card = $btn.closest( '.travail-plugin-card' );
		setBusy( $card, true );

		$.post( travailAdmin.ajaxUrl, {
			action: 'travail_activate_plugin',
			nonce: travailAdmin.nonce,
			plugin_file: $btn.data( 'plugin-file' ),
		} )
			.done( function ( response ) {
				handleResponse( $card, response, 'Activated!' );
			} )
			.fail( function () {
				handleResponse( $card, null, '' );
			} )
			.always( function () {
				setBusy( $card, false );
			} );
	} );

	$( document ).on( 'click', '.travail-set-homepage', function () {
		var $btn = $( this ).prop( 'disabled', true );
		var $card = $btn.closest( '.travail-homepage-card' );
		var $message = $card.find( '.travail-homepage-card__message' );

		$.post( travailAdmin.ajaxUrl, {
			action: 'travail_set_active_homepage',
			nonce: travailAdmin.nonce,
			page_id: $btn.data( 'page-id' ),
		} )
			.done( function ( response ) {
				if ( response && response.success ) {
					$message.css( 'color', '#2e7d32' ).text( ( response.data && response.data.message ) || 'Updated.' );
					setTimeout( function () {
						window.location.reload();
					}, 700 );
				} else {
					$message.css( 'color', '#c0392b' ).text( ( response && response.data && response.data.message ) || 'Something went wrong.' );
					$btn.prop( 'disabled', false );
				}
			} )
			.fail( function () {
				$message.css( 'color', '#c0392b' ).text( 'Request failed — please try again.' );
				$btn.prop( 'disabled', false );
			} );
	} );
} )( jQuery );
