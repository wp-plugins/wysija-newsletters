=== Wysija Newsletters ===
Contributors: wysija
Tags: newsletter, email, emailing, smtp
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.0

This plugin does one thing well: create and send newsletters from WordPress. Easily.

== Description ==

Sending newsletters from WordPress has just gotten a lot easier. Our editor let’s you drag and drop your articles, images, dividers and logo into your newsletter. Pick a theme and change its fonts and colors directly while you’re editing. Managing your lists and subscription forms is straightforward. Configuration is dummy proof. And if you’re astray or lost, [we’re here](http://support.wysija.com/) to help.

= Features =

* Pick a theme. Modify its design
* Simple one column design. Looks good everywhere, mobile phones included
* Drag & drop your articles, free form text, images and horizontal lines
* Get stats for each campaign: open, clicks, unreads
* Put a subscription form as a sidebar widget or in your pages
* Import your lists like a breeze. Manage them without pain
* Segment your lists based on opened, clicked & bounced
* your site's users have their own synced list
* Quick and easy configuration
* WordPress Multisite ready
* We offer quick support
* Free version is limited to 2000 subscribers

= Premium version =

[Wysija Premium](http://www.wysija.com/wordpress-newsletter-plugin-premium/) offers these nifty extra features:

* Unlimited number of subscribers
* Stats for individual subscribers
* Automated bounce handling. Keeps your list clean
* We trigger your email queue, like a real cron job
* Priority support

= Future release =

* Auto newsletters, like Feedburner email alerts
* Possibility to add marketing tracking codes (Premium feature)
* Add social bookmark icons to your newsletter
* Add galleries to your newsletter

== Installation ==

There's 3 ways to install this plugin:

Note: premium users don't need to reinstall anything

= 1. The super easy way =
1. In your Admin, go to menu Plugins > Add
1. Search for Wysija
1. Click to install
1. Activate the plugin
1. A new menu `Wysija` will appear in your Admin

= 2. The easy way =
1. Download the plugin (.zip file) on the right column of this page
1. In your Admin, go to menu Plugins > Add
1. Select the tab "Upload"
1. Upload the .zip file you just downloaded
1. Activate the plugin
1. A new menu `Wysija` will appear in your Admin

= 3. The old way (FTP) =
1. Upload `wysija-newsletters` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. A new menu `Wysija` will appear in your Admin

== Frequently Asked Questions ==

Our [support site](http://support.wysija.com/) has articles and a responsive ticketing system to submit bugs.

== Screenshots ==

1. Sample newsletters.
2. The drag & drop editor.
3. Subscriber management.
4. Newsletter statistics.
5. Subscriber statistics (Premium version).
6. Sending method configuration in Settings.
7. Importing subscribers with a CSV.

== Changelog ==

= 1.0 =
* Premium upgrade available
* fix image selector width in editor
* fix front stats of email when email preview and show errors all
* fix front stats of email when show errors all
* fix import ONLY subscribed from external plugins such as Tribulant or Satollo
* fix retrieve wp.posts when time is different on mysql server and apache server
* fix changing encoding from utf8 to another was not sending
* newsletter background colour now displays in new Gmail
* less confusing queue sending status
* updated language file (pot) with 20 or so modifications

= 0.9.6 =
* fixed subscribe from a wysija confirmation page bug
* fixed campaigns "Column does not exists in model .."
* fixed address and unsubscribe links appearing at bottom of newsletter a second time
* fixed menu submenu no wysija but newsletters no js
* fixed bug statistics opened_at not inserted
* fixed bug limit subscribers updated on subscribers delete
* fixed daily cron scandir empty dir
* fixed subscribe from frontend without javascript error
* fixed subscribe IP server validation when trying in local
* fixed CSS issues with Wordpress 3.3
* improving interface of email sending in the newsletter's listing
* added delete newsletter option
* added language pot file
* added french translation

= 0.9.2 =
* fixed issue with synched users on multisite(each site synch its users only)
* fixed compatibility issue with wordpress 3.3(thickbox z-index)
* fixed issue with redundant messages after plugin import
* fixed version number display

= 0.9.1 =
* fixed major issue with browser check preventing Safari users from using the plugin
* fixed issue with wp_attachment function affecting Wordpress post insertion
* fixed issue when importing subscribers (copy/paste from Gmail)
* fixed issue related to Wordpress MU
* minor bugfixes

= 0.9 =
* Hello World. We just launched this plugin.