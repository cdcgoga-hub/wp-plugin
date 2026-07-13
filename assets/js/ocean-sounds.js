/**
 * Ocean Sounds — front-end player.
 *
 * Non-blocking: this file is loaded with `defer`, does no synchronous
 * network/DOM work at parse time, and never throws on autoplay rejection
 * (browsers routinely block unmuted autoplay until a user gesture).
 */
( function () {
	'use strict';

	var settings = window.OceanSoundsSettings;
	if ( ! settings || ! settings.tracks || ! settings.tracks.length ) {
		return;
	}

	var STORAGE_ON    = 'oceanSoundsOn';
	var STORAGE_TRACK = 'oceanSoundsTrack';

	function readStoredBool( key, fallback ) {
		try {
			var value = window.localStorage.getItem( key );
			if ( value === null ) {
				return fallback;
			}
			return value === '1';
		} catch ( e ) {
			return fallback;
		}
	}

	function readStoredInt( key, fallback ) {
		try {
			var value = window.localStorage.getItem( key );
			if ( value === null ) {
				return fallback;
			}
			var parsed = parseInt( value, 10 );
			return isNaN( parsed ) ? fallback : parsed;
		} catch ( e ) {
			return fallback;
		}
	}

	function storeValue( key, value ) {
		try {
			window.localStorage.setItem( key, value );
		} catch ( e ) {
			// Storage unavailable (private mode, disabled, etc.) — fail silently.
		}
	}

	function init() {
		var tracks = settings.tracks;
		var trackIndex = readStoredInt( STORAGE_TRACK, settings.defaultTrack || 0 );
		if ( trackIndex < 0 || trackIndex >= tracks.length ) {
			trackIndex = 0;
		}

		var soundOn = readStoredBool( STORAGE_ON, !! settings.autoplay );

		var container = document.getElementById( 'ocean-sounds-widget' );
		if ( ! container ) {
			return;
		}

		var audio = document.createElement( 'audio' );
		audio.loop = true;
		audio.preload = 'auto';
		audio.src = tracks[ trackIndex ].url;
		audio.volume = Math.min( 100, Math.max( 0, settings.volume || 50 ) ) / 100;

		var toggleBtn = document.createElement( 'button' );
		toggleBtn.type = 'button';
		toggleBtn.className = 'ocean-sounds-toggle';
		toggleBtn.setAttribute( 'aria-pressed', soundOn ? 'true' : 'false' );

		var icon = document.createElement( 'span' );
		icon.className = 'ocean-sounds-icon';
		icon.setAttribute( 'aria-hidden', 'true' );

		var label = document.createElement( 'span' );
		label.className = 'ocean-sounds-toggle-label';

		function updateToggleUI() {
			toggleBtn.setAttribute( 'aria-pressed', soundOn ? 'true' : 'false' );
			icon.textContent = soundOn ? '🔊' : '🔇';
			label.textContent = soundOn
				? ( settings.i18n && settings.i18n.pause ? settings.i18n.pause : 'ხმის გამორთვა' )
				: ( settings.i18n && settings.i18n.play ? settings.i18n.play : 'ხმის ჩართვა' );
		}
		updateToggleUI();

		toggleBtn.appendChild( icon );
		toggleBtn.appendChild( label );

		var attemptPlay = function () {
			var playPromise = audio.play();
			if ( playPromise && typeof playPromise.catch === 'function' ) {
				playPromise.catch( function () {
					// Autoplay blocked by the browser; wait for a user gesture.
					armGestureFallback();
				} );
			}
		};

		var gestureEvents = [ 'click', 'touchstart', 'keydown' ];
		var gestureArmed  = false;

		function onFirstGesture() {
			if ( soundOn ) {
				audio.play().catch( function () {
					/* Still blocked; user can use the toggle button directly. */
				} );
			}
			gestureEvents.forEach( function ( evt ) {
				document.removeEventListener( evt, onFirstGesture, true );
			} );
			gestureArmed = false;
		}

		function armGestureFallback() {
			if ( gestureArmed ) {
				return;
			}
			gestureArmed = true;
			gestureEvents.forEach( function ( evt ) {
				document.addEventListener( evt, onFirstGesture, true );
			} );
		}

		if ( soundOn ) {
			attemptPlay();
		}

		toggleBtn.addEventListener( 'click', function () {
			soundOn = ! soundOn;
			storeValue( STORAGE_ON, soundOn ? '1' : '0' );
			updateToggleUI();

			if ( soundOn ) {
				attemptPlay();
			} else {
				audio.pause();
			}
		} );

		container.appendChild( toggleBtn );

		if ( tracks.length > 1 ) {
			var select = document.createElement( 'select' );
			select.className = 'ocean-sounds-track-select';
			select.setAttribute(
				'aria-label',
				settings.i18n && settings.i18n.trackLabel ? settings.i18n.trackLabel : 'Melody'
			);

			tracks.forEach( function ( track, index ) {
				var option = document.createElement( 'option' );
				option.value = String( index );
				option.textContent = track.name;
				if ( index === trackIndex ) {
					option.selected = true;
				}
				select.appendChild( option );
			} );

			select.addEventListener( 'change', function () {
				var newIndex = parseInt( select.value, 10 );
				if ( isNaN( newIndex ) || ! tracks[ newIndex ] ) {
					return;
				}
				trackIndex = newIndex;
				storeValue( STORAGE_TRACK, String( trackIndex ) );

				var wasPlaying = ! audio.paused;
				audio.src = tracks[ trackIndex ].url;
				if ( wasPlaying || soundOn ) {
					audio.play().catch( function () {
						armGestureFallback();
					} );
				}
			} );

			container.appendChild( select );
		}

		container.appendChild( audio );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
