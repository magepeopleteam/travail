<?php
/**
 * View: Documentation.
 *
 * @package Travail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sections = array(
	'installation'    => array(
		'title'   => __( 'Installation & Activation', 'travail' ),
		'content' => __( 'Upload Travail via Appearance → Themes → Add New → Upload Theme, then Activate. Right after activation you\'ll see a notice linking to the Setup Wizard — use it to install recommended plugins and import demo content.', 'travail' ),
	),
	'plugins'         => array(
		'title'   => __( 'Installing Plugins', 'travail' ),
		'content' => __( 'Elementor is required for the fully visual homepage builder. Tour Booking Manager (and its Pro add-on) power tours, search and booking — install them from your license account, then activate. WooCommerce is optional and only needed for cart/checkout. Visit Travail → Recommended Plugins to install/activate Elementor and WooCommerce in one click, or check current status on Travail → System Status.', 'travail' ),
	),
	'demo-import'     => array(
		'title'   => __( 'Demo Import', 'travail' ),
		'content' => __( 'Travail → Demo Import creates a starter set of pages, a primary menu, footer widgets and homepage theme settings. It is safe to run more than once — it will not duplicate pages it already created (each imported item is tracked so re-running only fills in what is missing). It never deletes existing content.', 'travail' ),
	),
	'elementor'       => array(
		'title'   => __( 'Building Pages with Elementor', 'travail' ),
		'content' => __( 'Every homepage section (Hero, Tour Search, Tour Grid/Carousel, Destinations, Categories, Testimonials, Blog, CTA, Newsletter, Features) is available as an Elementor widget under the "Travail" category. Create/edit a Page, set it as your front page under Settings → Reading, then edit it with Elementor and drag in any Travail widget — no theme code is hardcoded in the way.', 'travail' ),
	),
	'tours'           => array(
		'title'   => __( 'Setting Up Tours', 'travail' ),
		'content' => __( 'Tours are managed entirely inside Tour Booking Manager (Tours → Add New). Travail only renders that data — categories, locations, pricing, availability, extra services, reviews. Assign a Location term (used for "Destinations") and a Category term to every tour so the homepage destination/category widgets have something to show.', 'travail' ),
	),
	'woocommerce'     => array(
		'title'   => __( 'WooCommerce', 'travail' ),
		'content' => __( 'If Tour Booking Manager is configured to sell through WooCommerce, checkout/cart/My Account inherit Travail\'s styling automatically — no extra setup required. If you don\'t use WooCommerce, hide it from Recommended Plugins and Travail keeps working normally.', 'travail' ),
	),
	'header-footer'   => array(
		'title'   => __( 'Header & Footer', 'travail' ),
		'content' => __( 'Customize logo, header style (transparent/solid), header CTA button and announcement bar under Appearance → Customize → Travail Theme Options → Header. Footer columns pull from Appearance → Menus (Footer Column 1/2/3 locations) or Appearance → Widgets (Footer Column 1/2/3 sidebars) — whichever has content.', 'travail' ),
	),
	'theme-settings'  => array(
		'title'   => __( 'Theme Settings', 'travail' ),
		'content' => __( 'All visual settings live in the native Customizer (Appearance → Customize → Travail Theme Options) grouped into General, Colors & Typography, Header, Footer, Social, Blog, Tours & Booking, Breadcrumbs, Performance and Custom CSS.', 'travail' ),
	),
	'child-theme'     => array(
		'title'   => __( 'Child Themes', 'travail' ),
		'content' => __( 'Travail exposes travail_before_header, travail_after_header, travail_before_content, travail_after_content, travail_before_footer and travail_after_footer action hooks, plus numerous apply_filters() calls (see inc/helpers.php and inc/template-functions.php), so most customizations don\'t require copying template files into a child theme at all.', 'travail' ),
	),
	'translation'     => array(
		'title'   => __( 'Translation', 'travail' ),
		'content' => __( 'Every string uses the "travail" text domain and is wrapped in __()/_e()/esc_html__(). Generate a .pot file with WP-CLI (wp i18n make-pot .) or Loco Translate, place .po/.mo files in /languages, and set your site language under Settings → General.', 'travail' ),
	),
	'troubleshooting' => array(
		'title'   => __( 'Troubleshooting', 'travail' ),
		'content' => __( 'Start with Travail → System Status. A red ✕ means a hard requirement is unmet (e.g. PHP version); a yellow ⚠ is a recommendation. If tour data isn\'t rendering, confirm Tour Booking Manager is active and that the tour has a published status, a price and at least one Location/Category term assigned.', 'travail' ),
	),
);
?>
<div class="wrap travail-admin-wrap">
	<div class="travail-admin-header">
		<h1><?php esc_html_e( 'Documentation', 'travail' ); ?></h1>
		<p><?php esc_html_e( 'Everything you need to set up and customize Travail.', 'travail' ); ?></p>
	</div>

	<div class="travail-docs-toc">
		<ul>
			<?php foreach ( $sections as $id => $section ) : ?>
				<li><a href="#travail-doc-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $section['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php foreach ( $sections as $id => $section ) : ?>
		<div class="travail-docs-section" id="travail-doc-<?php echo esc_attr( $id ); ?>">
			<h2><?php echo esc_html( $section['title'] ); ?></h2>
			<p><?php echo esc_html( $section['content'] ); ?></p>
		</div>
	<?php endforeach; ?>
</div>
