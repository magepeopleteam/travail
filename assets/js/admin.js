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

	function manualLinks( $card, manual ) {
		var slug = $card.data( 'slug' );
		manual = manual || {};
		var installUrl = manual.install_url || ( travailAdmin.pluginInstallUrl + '?s=' + encodeURIComponent( slug ) + '&tab=search&type=term' );
		var pluginsUrl = manual.plugins_url || travailAdmin.pluginsUrl;
		var zipUrl = manual.zip_url || ( 'https://downloads.wordpress.org/plugin/' + encodeURIComponent( slug ) + '.latest-stable.zip' );
		var uploadUrl = manual.upload_url || ( travailAdmin.pluginInstallUrl + '?tab=upload' );
		$card.find( '.travail-plugin-manual' ).remove();
		var $box = $( '<div class="travail-plugin-manual" style="margin-top:10px;font-size:13px;line-height:1.7;"></div>' );
		$box.append( $( '<strong></strong>' ).text( 'Install manually instead:' ) );
		var $list = $( '<ol style="margin:6px 0 0 18px;padding:0;"></ol>' );
		$list.append( $( '<li></li>' ).append( 'Open ', $( '<a target="_blank" rel="noopener"></a>' ).attr( 'href', installUrl ).text( 'Plugins \u2192 Add New' ), ' and click Install Now, then Activate.' ) );
		$list.append( $( '<li></li>' ).append( 'Or ', $( '<a target="_blank" rel="noopener"></a>' ).attr( 'href', zipUrl ).text( 'download the ZIP' ), ' and ', $( '<a target="_blank" rel="noopener"></a>' ).attr( 'href', uploadUrl ).text( 'upload it' ), '.' ) );
		$list.append( $( '<li></li>' ).append( 'Then ', $( '<a target="_blank" rel="noopener"></a>' ).attr( 'href', pluginsUrl ).text( 'activate it on the Plugins screen' ), ' and reload this page.' ) );
		$box.append( $list );
		$card.append( $box );
	}

	function handleResponse( $card, response, successMessage, xhr ) {
		var $message = $card.find( '.travail-plugin-message' );
		if ( response && response.success ) {
			$message.css( 'color', '#2e7d32' ).text( successMessage );
			$card.find( '.travail-plugin-manual' ).remove();
			setTimeout( function () {
				window.location.reload();
			}, 900 );
		} else {
			var msg = ( response && response.data && response.data.message ) || '';
			if ( ! msg && xhr ) {
				msg = 'The server did not answer correctly (HTTP ' + ( xhr.status || 'error' ) + ( xhr.status === 0 ? ', request timed out or was blocked' : '' ) + ').';
			}
			msg = msg || travailAdmin.genericError || 'Installation failed.';
			$message.css( 'color', '#c0392b' ).text( msg );
			manualLinks( $card, response && response.data && response.data.manual );
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
			.fail( function ( xhr ) {
				handleResponse( $card, null, '', xhr );
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
			.fail( function ( xhr ) {
				handleResponse( $card, null, '', xhr );
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
