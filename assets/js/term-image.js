/**
 * Travail — media-library picker for the "Card Image" term-meta field
 * on the ttbm_tour_location / ttbm_tour_cat add/edit screens.
 *
 * @package Travail
 */

/* global jQuery, wp */
( function ( $ ) {
	'use strict';

	if ( 'undefined' === typeof wp || ! wp.media ) {
		return;
	}

	var frame;

	$( document ).on( 'click', '.travail-term-image-select', function ( event ) {
		event.preventDefault();
		var $button = $( this );
		var $wrap = $button.closest( '.form-field, td' );

		frame = wp.media( {
			title: travailAdmin && travailAdmin.chooseImageTitle ? travailAdmin.chooseImageTitle : 'Choose Image',
			multiple: false,
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			$wrap.find( '#travail-term-image-id' ).val( attachment.id );
			var previewUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
			$wrap.find( '.travail-term-image-preview' ).html( '<img src="' + previewUrl + '" alt="" style="max-width:120px;margin-top:10px;display:block;" />' );
			$wrap.find( '.travail-term-image-remove' ).show();
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.travail-term-image-remove', function ( event ) {
		event.preventDefault();
		var $button = $( this );
		var $wrap = $button.closest( '.form-field, td' );
		$wrap.find( '#travail-term-image-id' ).val( '' );
		$wrap.find( '.travail-term-image-preview' ).empty();
		$button.hide();
	} );
} )( jQuery );
