=== Ocean Sounds ===
Contributors: oceansounds
Tags: audio, ambient, relaxation, ocean, background sound
Requires at least: 5.6
Tested up to: 6.6
Requires PHP: 7.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plays a relaxing ocean/nature soundtrack when a visitor arrives on your site, with a non-blocking floating widget to switch melodies or turn the sound off.

== Description ==

Ocean Sounds adds a small, floating audio player to your site that starts playing a calming ocean (or any ambient) soundtrack when a visitor arrives.

* Choose one or more audio tracks from your Media Library (or paste a direct URL).
* If more than one track is configured, visitors get a dropdown to switch between them.
* Visitors can always turn the sound on/off from the floating widget — their choice is remembered (via `localStorage`) for future visits.
* The player script is loaded with `defer` and never blocks page rendering. If a browser blocks autoplay-with-sound (a standard browser policy), playback starts automatically on the visitor's first click/tap instead of failing silently or throwing errors.
* Set the default volume, widget position (corner of the screen), and whether sound should try to autoplay at all.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/ocean-sounds` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the "Plugins" screen.
3. Go to Settings → Ocean Sounds and add at least one audio track (MP3/OGG) from your Media Library.
4. Configure autoplay, default volume, and widget position as desired.

== Frequently Asked Questions ==

= Why doesn't the sound start immediately for every visitor? =

Modern browsers block unmuted audio autoplay until the visitor interacts with the page at least once (a click, tap, or key press). This is a browser-level restriction, not a plugin limitation. When blocked, Ocean Sounds automatically starts playback on the visitor's first interaction instead.

= Can visitors turn the sound off? =

Yes. The floating widget always includes an on/off control, and the visitor's choice is remembered on their device for future visits.

== Changelog ==

= 1.0.0 =
* Initial release.
