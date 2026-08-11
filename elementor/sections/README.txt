No custom Elementor Section/Container types are registered by Travail
1.0.0 — Elementor's own Section/Container + Column layout system already
covers everything the theme's homepage sections need, so adding a
custom section type here would just be unnecessary API surface.

This folder (and elementor/controls/) is kept as part of the theme's
documented architecture in case a future version needs one — see
elementor/widgets/ for the theme's actual Elementor integration.
