Travail's one-click demo import is code-driven rather than static-file
driven — there is no demo-content.xml / widgets.wie / customizer.dat in
this folder to keep in sync by hand every release.

The actual importer lives in inc/importer/class-travail-demo-importer.php
and inc/onboarding/class-travail-onboarding.php (Setup Wizard → "Demo
Import" step, or Travail → Demo Import in wp-admin). It creates pages,
menus, footer widgets and default theme settings directly through core
WordPress APIs (wp_insert_post, wp_create_nav_menu, register_sidebar
options, set_theme_mod), tracks every object it creates in the
`travail_demo_import_map` option, and is safe to re-run — existing
objects are detected and left alone rather than duplicated.

demo-import/elementor/ is reserved for exported Elementor template JSON
(Elementor's own "Templates → Saved Templates → Export" format) once a
demo homepage has been built and tested with Elementor installed. None
are shipped in 1.0.0 — see the "Elementor" step's message in the Setup
Wizard, which currently points users at the "Travail" widget category to
rebuild the homepage visually instead of importing a canned layout.
