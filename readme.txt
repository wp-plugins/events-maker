=== Events Maker by dFactory===
Contributors: dfactory
Donate link: http://www.dfactory.eu/
Tags: event, event categories, events management, event management, events manager, event manager, event organizer, events calendar, events maker, google map, map, locations, organizers, widget, attendance, attendee, Booking, calendar, event registration, ticket, tickets, ticketing, bookings
Requires at least: 3.5
Tested up to: 4.3.0
Stable tag: 1.6.6
License: MIT License
License URI: http://opensource.org/licenses/MIT

Fully featured event management system including recurring events, locations management, full calendar, iCal feed/files, google maps and more.

== Description ==

[Events Maker](http://www.dfactory.eu/plugins/events-maker/) is a fully featured event management plugin from [dFactory](http://www.dfactory.eu/) including recurring events, locations management, full calendar, iCal feed/files support and google maps integration.

= Powerful, flexible events management =

Events Maker allows you to easily create and manage your events. But at the same time is powerful and very flexible in customization. 

= Responsive Events Calendar =

Users can browse for your events in a beautiful, responsive events calendar and display all events for the selected month, week od day.

= Automatic iCal support =

Events Maker automatically generates an iCal feed/files for all your events, categories, tags, locations, organizers and single events which can be used to import event information into calendar applications such as Google Calendar, iCalendar, Outlook, ThunderBird, etc.

= Locations with Google Maps =

Support for event locations and Google Maps has never been easier. Thanks to the geolocation you won't need to mess around with latitude and longitude.

= WPML & Polylang compatibility =

Events Maker allows you to create and manage events, categories, locations and organizers in different languages using WPML or Polylang. So now, if you build a multilingual website, you won't have to worry about the multilingual capability anymore.

= Features include: =

* Easy Events management
* Built using WordPress custom post types, custom taxonomies and custom post fields
* Ajax Events Calendar
* Event Search with customizable attributes
* Display options for single events
* Multiple Tickets and Pricing
* Locations list page
* Organizers list page
* Full Calendar page
* Automatic iCal feed/files support
* Advanced recurring events, including daily, monthly, yearly and custom occurrences
* Compatibility with Twenty Thirteen, Twenty Fourteen and Twenty Fifteen themes 
* Featured events
* Event Categories and Tags
* Event Ogranizers
* Organizers contact details and image
* Event Locations
* Breadcrumb navigation
* Google maps
* Duplicating events
* Builtin event gallery
* RSS feed support
* Customizeable template files
* Events sorting
* Google Rich Snippets ready
* 7 Events widgets
* Multiple custom functions
* Advanced hooks for developers
* Custom permissions for Events
* Multisite support
* Compatible with WPML & Polylang
* Compatible with SEO plugins
* Customizable permalink structure
* .pot file for translations included

= Future plans: =

* Front-end events submissions
* Event registration & booking system

= Translations: =

* Brazilian Portuguese - by Adson Nunes
* Catalan - by Jordi Altimira
* Dutch - by [Heleen van den Bos](http://www.bostekst.nl/)
* Finnish - by [Ari-Pekka Koponen](http://www.versi.fi/)
* French - by Marc Abel
* German - by [Martin Stoehr](http://www.stoehrinteractive.com)
* Hebrew - by [Ahrale Shrem](http://atar4u.com/)
* Hungarian - by Meszaros Tamas
* Italian - by [Lorenzo De Tomasi](http://isotype.org)
* Japanese - by stranger-jp
* Norwegian - by Anders Kleppe
* Polish - by Bartosz Arendt
* Russian - by Valerii Levachkov
* Serbian - by Andrija Kokanovic
* Spanish - by [Borisa Djuraskovic](http://www.webhostinghub.com/), Miren Askasibar


== Installation ==

1. Install Events Maker either via the WordPress.org plugin directory, or by uploading the files to your server
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Events Maker settings under Events menu and set your desired options.

== Frequently Asked Questions ==

= Q. I have a question =

A. Chances are, someone else has asked it. Check out the support forum at: http://www.dfactory.eu/support/

== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
4. screenshot-4.png
5. screenshot-5.png
6. screenshot-6.png

== Changelog ==

= 1.6.6 =
* Fix: Calendar widget AJAX issue
* Fix: Multiple template bufixes
* Tweak: Confirmed WP 4.3 compatibility

= 1.6.5 =
* New: Event Search with customizable attributes

= 1.6.4 =
* Fix: Calendar widget incorrect day archive link
* Fix: Call to undefined method Events_Maker_Settings::update_menu() on plugin deactivation
* Fix: Polylang get_post_language was called incorrectly admin notice
* Tweak: filter hook for Full Calendar script args

= 1.6.3 =
* Fix: Incorrect Polylang terms links
* Tweak: em-events shortcode extended with offset parameter

= 1.6.2 =
* Fix: WPML & Polylang calendar transient / cache not clearing on save
* FIx: Update from 1.3 or below display_notice() errors

= 1.6.1 =
* Fix: WPML permalinks translation issue for languages other than default
* Fix: pll_default_language() missing in Polylang permalinks translation

= 1.6.0 =
* New: Locations list page
* New: Organizers list page
* New: Support for transient / object cache for calendar query
* New: Event date start/end query parameters accept any format recognizable by strtotime
* Tweak: Improved WPML & Polylang compatibility, including permalink translation

= 1.5.4 =
* Fix: Country field error in organizer details
* Tweak: Enhanced country field backward compatibility

= 1.5.3 =
* Fix: Google Map not showing on single event and location pages 

= 1.5.2 =
* Fix: array_keys() error in calendar widget

= 1.5.1 =
* Fix: Sorting affecting other post types
* Fix: Error while saving curreny settings

= 1.5.0 =
* New: Events sorting
* New: Generate iCal button on single event page
* Tweak: Code rewritten into singleton
* Tweak: Improved support for Twenty Fifteen theme
* Tweak: Additional filter hooks for developers

= 1.4.6 =
* Tweak: Link to All events in Events List widget

= 1.4.5 =
* New: Display event category color in events list in admin
* New: Hungarian translation, thanks to Meszaros Tamas

= 1.4.4 =
* New: Event gallery
* Tweak: Full calendar updated to 2.2.6

= 1.4.3 =
* Fix: Date archive display all events when permalinks disabled

= 1.4.2 =
* New: Hebrew translation, thanks to [Ahrale Shrem](http://atar4u.com/)

= 1.4.1 =
* Fix: View Calendar button not displayed in admin
* Fix: iCal feed date not converting properly to GMT
* Tweak: iCal feed extended with location, organizer and categories data

= 1.4.0 =
* New: iCal support for Google Calendar, iCalendar, Outlook, ThunderBird and others
* Fix: Woocommerce clash with featured column content
* Fix: Event locations widget empty due to a typo in taxonomy name

= 1.3.4 =
* New: German translation, thanks to [Martin Stoehr](http://www.stoehrinteractive.com)
* New: Finnish translation, thanks to [Ari-Pekka Koponen](http://www.versi.fi/)

= 1.3.3 =
* New: Introducing [em-events] shortcode
* Tweak: Documentation improvements

= 1.3.2 =
* Fix: Event content not displayed for single events
* Fix: General and display options not resetting
* Tweak: Event options not set in wpml-config.xml

= 1.3.1 =
* Fix: Post row actions removed from post types other than event
* Fix: Full calendar pages not displayed on other languages using WPML or Polylang

= 1.3.0 =
* New: Featured events
* New: Duplicating events
* Tweak: Multiple admin UI tweaks
* Tweak: Adjust admin date display to selected time format
* Tweak: Full Calendar updated to 2.2.1

= 1.2.4 =
* New: Italian translation, thanks to [Lorenzo De Tomasi](http://isotype.org)
* New: Recurring events option for event calendar widget
* Fix: All day events display in full calendar view
* Fix: First day of the week option not working
* Fix: Event display options metabox not working properly
* Fix: A typo in em_get_organizers_for() function
* Tweak: Breadrumb date incorrect in archive page view

= 1.2.3 =
* Fix: Translation files currupted
* Fix: Disabling default templates not working

= 1.2.2 =
* Fix: Google map not displayed poperly if location data was empty
* Fix: Location and organizer details displayed improperly
* Fix: Undefined variable: options in class-metaboxes.php
* Fix: Undefined offset: 1 in events-maker.php

= 1.2.1 =
* Fix: Event query not working properly for multiple post types
* Tweak: Currency list extended
* Tweak: Added category color column

= 1.2.0 =
* New: Template files rewritten, for much greater flexibility and extensibility
* New: Events breadcrumb navigation
* New: Default event display options setting
* New: RSS feed support
* New: Location image field
* New: Event category color
* New: Russian translation, thanks to Valerii Levachkov
* Tweak: Recurring events available in Events List widget
* Tweak: Additional Full Calendar styling classes, thanks to Kuba S.
* Tweak: Leave widget title empty if was not entered
* Tweak: Full Calendar updated to 2.1
* Tweak: Confirmed WP 4.0 compatibility
* New: Option to donate this plugin :)

= 1.1.6 =
* New: French translation, thanks to Marc Abel

= 1.1.5 =
* New: Norwegian translation, thanks to Anders Kleppe

= 1.1.4 =
* New: Serbian translation, thanks to Andrija Kokanovic
* Tweak: Spanish translation updated, thanks to Miren Askasibar

= 1.1.3 =
* Fix: Incorrect paths for template files in theme
* Fix: small typos in theme

= 1.1.2 =
* Fix: Event categories, organizers and locations not displayed in theme
* Fix: Display options notice in single event

= 1.1.1 =
* New: Catalan translation, thanks to Jordi Altimira
* Fix: Catchable fatal error while tags/categories display

= 1.1.0 =
* New: Advanced recurring events, including daily, monthly, yearly and custom occurrences
* New: Interactive Full Events Calendar display on specified page
* New: Template files compatibility with Twenty Thirteen and Twenty Fourteen themes 
* Tweak: Settings page adjusted to WP native user interface
* Tweak: Multiple bugfixes and improvements

= 1.0.10 =
* New: Added 3 Ajax Calendar widget CSS styles
* Tweak: UI improvements for WP 3.8

= 1.0.9 =
* New: Spanish translation, thanks to [Borisa Djuraskovic](http://www.webhostinghub.com)
* New: Brazilian Portuguese translation, thanks to Adson Nunes

= 1.0.8 =
* Fix: Show past events option not working on taxonomy pages
* Fix: wp_footer missing in location pages
* Tweak: Added current day class to Ajax News Calendar 

= 1.0.7 =
* Fix: Ajax Events calendar not working properly with Polylang
* Fix: Google Map display on loacations template
* Tweak: Added thumbnail selection in Events List Widget

= 1.0.6 =
* New: Multisite support
* New: Dutch translation, thanks to Heleen van den Bos

= 1.0.5 =
* Fix: Use default option not working
* Tweak: numberposts changed to posts_per_page in get_events() function
* Tweak: Improved widgets display
* Tweak: Ajax Calendar default CSS added
* Tweak: Events query cleanup (removed categories_arr and other taxonomy parameters)

= 1.0.4 =
* New: Organizer image field for event organizers
* Tweak: Improved excerpt display in widgets
* Tweak: Improved general and permalniks settings reset
* Tweak: Documentation links added to settings page and welcome screen

= 1.0.3 =
* Fix: Single events not displaying if sorting by start or end date selected
* Fix: Custom fields data not saving if tickets were not used
* Tweak: Empty start and end dates handling if it was not provided
* Tweak: Improved WPML support using wpml-config file

= 1.0.2 =
* Fix: Single events 404 error if Show past events option was disabled

= 1.0.1 =
* New: Japanese translation, thanks to stranger-jp
* Fix: Label not saving in Events List widget

= 1.0.0 =
Initial release

== Upgrade Notice ==

= 1.6.6 =
* Fix: Calendar widget AJAX issue
* Fix: Multiple template bufixes
* Tweak: Confirmed WP 4.3 compatibility