Travail's one-click demo import is code-driven rather than static-file
driven: there is no demo-content.xml / widgets.wie / customizer.dat in
this folder to keep in sync by hand every release.

The importer lives in inc/importer/class-travail-demo-importer.php (content) and
inc/importer/class-travail-elementor-page-builder.php (Elementor homepages), run from
Setup Wizard -> "Demo Import" or Travail -> Demo Import in wp-admin. It uses core WordPress
APIs only, records every object it creates in the `travail_demo_import_map` option, and is
safe to re-run: existing objects are detected and left alone (empty fields are back-filled).

What a full import creates (kept in step with the reference site):

  Pages        About, Contact, FAQ, Blog, Destinations, plus two Elementor homepages:
               "travello" (the live front page) and "travail-classic".
  Menus        Primary, Footer: Discover, Footer: Company, Footer: Support.
  Widgets      Blog sidebar: Search + Categories. The default Recent Posts / Recent
               Comments widgets WordPress carries over are removed (also from the
               tour-details sidebar, which additionally drops Search).
  Destinations 11 locations with a card image and country (Bandarban, Coxbazar, Bali, Dubai,
               Paris, Swiss Alps, Santorini, Tokyo, Iceland, Maldives, Patagonia).
  Activities   11 travel styles: Adventure, Beach, Culture, Hiking, Luxury, Wildlife, Family,
               Wellness (photo) and City Tours, Rural, Snow & Ice (plugin icon).
  Tours        11 tours (8 popular, 3 deals): featured image, gallery, destination, activity,
               duration, rating. 10 run on Repeated Dates (every day from the import day, never
               ending, two weekly off days); Discover Patagonia keeps a fixed departure date.
               Each tour also gets Tour Booking Manager's own city + country fields (from its
               destination) and its activity list, so the plugin's search, filters and activity
               pages work from real data.
  Tour details The "Completing tour details" step fills every section of the tour page, written
               per tour: itinerary, FAQ, included / excluded, 2-3 ticket types, 2-3 extra
               services, why-choose-us, "have a question" contact, min age, start place,
               languages, map, times, photo slider, organizer, category, guides, related tours
               and 5 reviews averaging the tour's rating. It only fills EMPTY values (a section
               it fills is switched on), so re-running it on an edited site changes nothing an
               owner typed. It also switches on the photo slider, language and group size of
               the sample tours below.
  Stories      3 blog posts.
  Theme        Hero / footer text defaults and the hero, why-us, newsletter and four
               service-card images (Tours, Hotels, Transport, Activities).

  Samples      The Tour Booking Manager sample content, from demo-import/plugin-samples.json with
               its photos in demo-import/images/ (nothing is downloaded): 5 tours (The Mentalist
               Tickets, Highlights of Naples and the Amalfi Coast, Deep-Sea Exploration on a
               Shampan, Beach Hopping at Inani, Boga Lake), 3 hotels, 5 tour guides and 20 places,
               with their organizers, categories, activities, FAQs, itineraries, extra services,
               tickets, galleries and "related tours" links. Links between them are rebuilt with
               the new site's IDs. Dates are moved forward so the tours stay upcoming; 4 of the 5
               run on Repeated Dates (daily, never ending, two weekly off days), matching the
               plugin's own dummy import. A tour,
               hotel, guide or place whose title already exists is reused, never duplicated.

Not created here (comes from Tour Booking Manager itself): the "find" / hotel-search pages.

To refresh plugin-samples.json after changing those posts, re-export them from the site
(posts, post meta, terms and the images they use) in the same shape; the importer reads it as is.

demo-import/elementor/ is reserved for exported Elementor template JSON; none is shipped.
