=== Travail ===

Contributors: travailstudio
Requires at least: WordPress 6.0
Tested up to: WordPress 6.7
Requires PHP: 7.4
Version: 1.0.0
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A premium, Elementor-ready travel & tour booking theme built around
Tour Booking Manager. Works standalone, with Elementor, and with
WooCommerce.

== Description ==

Travail turns a Tour Booking Manager install into a polished travel
marketplace: a searchable tour archive, a rich single-tour page,
destination/category browsing and a fully Elementor-editable homepage
— all built on top of the plugin's own real data (pricing, availability,
extra services, reviews) rather than duplicating it.

== Requirements ==

* Recommended: Elementor (free) — for the fully visual homepage builder.
* Recommended: Tour Booking Manager (+ optionally the Pro add-on) — for
  tours, search, booking, wishlist and reviews. The theme runs without
  it, but tour-specific templates/widgets simply hide themselves.
* Optional: WooCommerce — only needed if Tour Booking Manager is
  configured to sell bookings through the WooCommerce cart/checkout.

== Installation ==

1. Appearance → Themes → Add New → Upload Theme → select the Travail
   .zip → Activate.
2. Follow the "Start Setup Wizard" notice, or go to
   Travail → Setup Wizard in the admin menu.
3. Install/activate Elementor and Tour Booking Manager from the
   "Plugins" step (or Travail → Recommended Plugins).
4. Run Travail → Demo Import for a ready-made starter site (pages,
   menus, footer widgets, default theme settings). Safe to run more
   than once.
5. Customize colors, header, footer and more under
   Appearance → Customize → Travail Theme Options.

== Where things live ==

* Theme Dashboard / Setup Wizard / Demo Import / Recommended Plugins /
  System Status / Documentation — admin menu "Travail".
* All visual settings — Appearance → Customize → Travail Theme Options.
* Homepage sections as Elementor widgets — search for "Travail" in the
  Elementor widget panel once Elementor is active.

== Architecture notes for developers ==

* Business logic (pricing, availability, booking, payments, wishlist,
  reviews) intentionally stays inside Tour Booking Manager / Tour
  Booking Manager Pro. The theme renders that data via the plugin's own
  shortcodes ([ttbm-top-search], [ttbm-tour-list]) and public hooks, and
  re-skins the plugin's own CSS classes in assets/css/tbm-restyle.css —
  see the comments at the top of that file and in
  inc/compatibility/tour-booking-manager.php before changing either.
* Child themes: hook into travail_before_header / travail_after_header /
  travail_before_content / travail_after_content / travail_before_footer /
  travail_after_footer, or any of the apply_filters() calls in
  inc/helpers.php and inc/template-functions.php, instead of copying
  whole template files.
* Text domain: `travail`. A translation template is provided at
  languages/travail.pot.

== Changelog ==

= 1.0.0 =
* Initial release.

== Credits ==

* Design reference: wanderly.html (bundled as the original design
  source, not used at runtime).
* Fonts: DM Serif Display & Plus Jakarta Sans (Google Fonts, OFL/Apache
  licensed) — loaded from fonts.googleapis.com by default; disable under
  Travail Theme Options → General to self-host instead.
* All theme code is original and licensed GPLv2 (or later), matching
  WordPress itself.
