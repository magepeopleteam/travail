No custom Elementor controls are registered by Travail 1.0.0 — every
widget under elementor/widgets/ is built entirely from Elementor's own
built-in controls (Text, Textarea, Media, Repeater, Icons, Url, Select,
Switcher, Number), per the "do not unnecessarily recreate Elementor's
existing controls" guidance.

Kept as part of the theme's documented architecture in case a future
version needs a genuinely custom control type.
