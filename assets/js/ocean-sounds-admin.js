/* global jQuery, wp, OceanSoundsAdmin */
( function ( $ ) {
	'use strict';

	var rowIndex = $( '#ocean-sounds-tracks-body .ocean-sounds-track-row' ).length;

	function updateVolumeDisplay() {
		$( '#ocean-sounds-volume' ).on( 'input change', function () {
			$( '.ocean-sounds-volume-value' ).text( $( this ).val() );
		} );
	}

	function addTrackRow() {
		$( '#ocean-sounds-add-track' ).on( 'click', function () {
			var template = document.getElementById( 'ocean-sounds-track-row-template' ).innerHTML;
			var html = template.split( '__INDEX__' ).join( rowIndex );
			$( '#ocean-sounds-tracks-body' ).append( html );
			rowIndex++;
		} );
	}

	function removeTrackRow() {
		$( '#ocean-sounds-tracks-body' ).on( 'click', '.ocean-sounds-remove-track', function () {
			$( this ).closest( 'tr' ).remove();
		} );
	}

	function chooseAudio() {
		var frame;

		$( '#ocean-sounds-tracks-body' ).on( 'click', '.ocean-sounds-choose-audio', function ( e ) {
			e.preventDefault();
			var $button = $( this );
			var $urlField = $button.siblings( '.ocean-sounds-track-url' );

			frame = wp.media( {
				title: OceanSoundsAdmin.mediaTitle,
				button: { text: OceanSoundsAdmin.mediaButton },
				library: { type: 'audio' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$urlField.val( attachment.url );

				var $nameField = $button.closest( 'tr' ).find( 'input[name*="[name]"]' );
				if ( ! $nameField.val() ) {
					$nameField.val( attachment.title || OceanSoundsAdmin.untitled );
				}
			} );

			frame.open();
		} );
	}

	$( function () {
		updateVolumeDisplay();
		addTrackRow();
		removeTrackRow();
		chooseAudio();
	} );
} )( jQuery );
