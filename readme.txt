=== Wysija Newsletters ===
Contributors: wysija, benheu
Tags: newsletter, newsletters, newsletter signup, newsletter widget, subscribers, post notification, email subscription, email alerts, automatic newsletter, auto newsletter, autoresponder, follow up, email marketing, email, emailing, subscription
Requires at least: 3.1
Tested up to: 3.5
Stable tag: 2.1.8

Send your post notifications or newsletters from WordPress easily, and beautifully.

== Description ==

Create newsletters, post notifications and autoresponders. Drop your posts, images, social icons in your newsletter. Change fonts and colors on the fly. Manage all your subscribers. A new and simple newsletter solution for WordPress. Finally!

We built it with the idea that newsletters in WordPress should be easy. Not hard. Forget MailChimp, Aweber, etc. We're the good guys inside your WordPress.

= Check out this 2 minute video. =

http://vimeo.com/35054446

= Post notifications video. =

http://vimeo.com/46247528

= Features =

* Drag & drop visual editor, an HTML-free experience
* Post notifications, like Feedburner, Subscribe2 or MailChimp's RSS-to-Email
* [Selection of over 20 themes](http://www.wysija.com/newsletter-templates-wordpress/). Photoshop files included
* Get stats for each newsletter: opens, clicks, unreads, unsubscribes
* Add a subscription form in your sidebar or pages
* Your newsletters look the same in Gmail, iPhone, Android, Outlook, Yahoo, Hotmail, etc.
* Your WordPress users have their own list
* Import subscribers from MailChimp, Aweber, etc.
* One click import from MailPress, Tribulant, Satollo, Subscribe2, etc.
* Single or double opt-in, your choice
* Send with your web host, Gmail or SMTP
* Segment your lists based on opened, clicked & bounced
* Autoresponders, i.e. "Send email 3 days after someone subscribes"
* Unlimited number of lists
* Free version is limited to 2000 subscribers

= Premium version =

[Wysija Premium](http://www.wysija.com/wordpress-newsletter-plugin-premium/) offers these nifty extra features:

* Unlimited number of subscribers
* Stats for individual subscribers (opened, clicked)
* Total clicks for each link in your newsletter
* Access to Premium themes
* Automated bounce handling. Keeps your list clean, avoid being labeled a spammer
* Unlimited spam score tests with mail-tester.com
* Improve deliverability with DKIM signature
* We trigger your email queue, like a real cron job
* Don't reinstall. Simply activate!
* Priority support

[Visit our Premium page](http://www.wysija.com/wordpress-newsletter-plugin-premium/).

= Upcoming major release =

* Subscriber profiles, ie. gender, city, or whatever you want
* Dozens of mini improvements based on user feedback
* Possibility to insert your own HTML in newsletter

= Future releases =

* New stats page
* Custom post types support
* Display a list of past newsletters sent in a page of your site (shortcode)

= Support =

We got a dedicated website just to help you out. And we're quite quick to reply.

[support.wysija.com](http://support.wysija.com/)

= Translations in your language =

Translations are included in the plugin. Join the translation teams on [our Transifex page](https://www.transifex.com/projects/p/wysija/).

* Your language: [get a Premium license in exchange for your translation](http://support.wysija.com/knowledgebase/translations-in-your-language/)
* Arabic
* Catalan
* Chinese
* Croatian
* Czech
* Danish
* Dutch
* French
* German
* Greek
* Hungarian
* Italian
* Norwegian
* Polish
* Portuguese PT
* Portuguese BR
* Romanian
* Russian
* Slovak
* Slovenian
* Spanish
* Swedish
* Turkish

== Installation ==

There are 3 ways to install this plugin:

Note: premium users don't need to reinstall anything. It's the same plugin.

= 1. The super easy way =
1. In your Admin, go to menu Plugins > Add
1. Search for `Wysija`
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

= 3. The old and reliable way (FTP) =
1. Upload `wysija-newsletters` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. A new menu `Wysija` will appear in your Admin

== Frequently Asked Questions ==

= Got questions? =

Our [support site](http://support.wysija.com/) has plenty of articles and a ticketing system.

= Submit your feature request =

We got a User Voice page where you can [add or vote for new features](http://wysija.uservoice.com/forums/150107-feature-request).

== Screenshots ==

1. Sample newsletters.
2. The drag & drop editor.
3. Subscriber management.
4. Newsletter statistics.
5. Subscriber statistics (Premium version).
6. Sending method configuration in Settings.
7. Importing subscribers with a CSV.

== Changelog ==

= 2.1.8 - 2012-11-27 =
* added get HTML version
* improved Wysija homemade cron, available in Settings > Advanced
* removed validation for first name & last name on subscriber profile
* fixed incompatibility with "Root Relative URLs" plugin
* fixed conflict with plugin "Magic Members"
* fixed crashed on some servers on install
* fixed in newsletters listing, wrong list appearing in automatic newsletter
* fixed disappeared bounce email field in Settings > Advanced for free users
* fixed Internet Explorer issue on WordPress Articles selection widget
* fixed issue on IE8 where a draggable item was not disappearing after being dropped
* fixed WordPress Synched list wrong count when sending
* fixed image not being fetched from post content when inserting a WordPress post
* fixed not sending auto newsletter with event "after a new user is added to your site" when double optin was off
* fixed various plugins conflicting with our subscription form inserted into the content of a post or page

= 2.1.7 - 2012-11-09 =
* added Wysija custom cron option in Advanced Settings as an alternative to wp-cron
* fixed translation missing for "unsubscribe", "view in your browser" and "manage your subscription" links
* fixed escaping quotes on subject in step 3 send preview
* fixed wrong total of subscribers when sending
* fixed bounced tab appearing empty for free users
* fixed wrong selection in WordPress posts widget after a search(in visual editor)
* fixed security issue with swf uploading module

= 2.1.6 - 2012-11-04 =
* added basic Custom Post Type support in WordPress post widget
* added resend an Activation Email for another list even when already subscribed
* added posts autoload on scroll when adding single post in newsletter visual editor
* fixed PHP Notice: step2 of newsletter creation
* fixed PHP Notice: on debug class
* fixed our debug hijacking WP_DEBUG in the backend (thanks Ryann)
* fixed deprecated in bounce handling
* fixed scrollbar issue in WordPress Post popup on Chrome & Safari
* fixed conflict with Simple Links plugin
* fixed toolbar tabs disappearing in some languages (will be improved)
* fixed bounce error not properly displayed prevented saving settings

= 2.1.5 - 2012-10-16 =
* fixed Notice: Use of undefined constant WYSIJA_DBG - assumed 'WYSIJA_DBG' in [...]/wp-content/plugins/wysija-newsletters/core/model.php on line 842
* fixed bulk add subscriber to list when unsubscribed
* fixed private list removed on edit your subscriber profile
* fixed shortcodes not being properly stripped from post excerpt
* fixed line breaks being stripped from posts
* fixed text alignment issues in Outlook
* fixed font styling issues in email
* fixed auto newsletter for new subscriber when single optin
* fixed new subscriber notification when single optin
* fixed send preview email on automatic post notification newsletter
* fixed not sending followup when updating subscriptions

= 2.1.4 - 2012-09-26 =
* fixed missing "from name" when using Elastic Email
* fixed rare issue where Social bookmarks & Automatic latest posts were not saved
* fixed double scrollbars appearing on article selection popup
* fixed dkim wrong key
* fixed filled up sent on parameter without having sent the newsletter

= 2.1.3 - 2012-09-18 =

* added restAPI for elasticemail when detected in the smtp configuration
* improved install making sure that no crazy plugins will harm our initial setup (symptoms: Too many redirect crash or posting to social networks)
* fixed SQL comments inserted as tables in some weird server...
* fixed error 500 on update procedure of 2.1 when some roles were not existing. (add_cap on none object fatal error)
* improved install process not creating new sql connection, only using wpdb's one.
* fixed synched plugins (Subscribe2 etc...) when there was just the main list
* removed global css and javascript
* fixed issue where the widget would not save
* improved IE9 compatibility
* fixed excerpt function to keep line breaks
* fixed links with #parameters GA incompatibility -> Thanks Adam

= 2.1.2 - 2012-09-05 =

* major speed improvement and cache plugin compatibility
* added utf-8 encoding in iframe loaded subscription form.
* added security check for translated links (dutch translation issue with view in browser link)
* removed _nonce non sense in the visitors subscription forms.
* fixed loading issue in subscription form
* fixed styling issue in subscription form
* fixed accents issue in subscription form
* fixed DKIM activation settings not being saved
* fixed non translated unsubscribe and view in browser links
* fixed warning showing up on some servers configuration when sending a preview of the newsletter
* fixed popups in IE8 and improved overall display
* fixed openssl_error_string function breaking our settings screen on some configurations.
* fixed error with dkim on server without openssl functions
* fixed bounce error with the rule unsubscribe user

= 2.1.1 - 2012-09-02 =

* fixed update 2.1 error : Duplicate column name "is_public" may have caused some big slow down on some servers and some auto post to facebook (deepest apologies).
* fixed Outlook issue where text blocks would not have the proper width

= 2.1 - 2012-08-31 =

* added ability for subscribers to change their email and lists.
* added "View it in your browser" option.
* added advanced access rights with capabilities for subscribers management, newsletter management, settings and subscription widget.
* added new WordPress 3.3 plupload used when possible to use.
* added mail-tester.com integration for Premium (fight against spam).
* added DKIM signature for Premium to improve deliverability.
* added the possibility to preview your newsletter without images in visual editor.
* added background colors for blocks within the visual editor.
* added alternate background colors for automatic latest post widget.
* added possibility to add total number of subscribers in widget with shortcode.
* added widget option "Display label within for Email field".
* improved email rendering and email clients compatibility including the new Outlook 2013
* improved image upload with ssl.
* improved compatibility with access rights plugins like "Advanced Access Manager" or "User Role Editor".
* improved import system with clearer message.
* improved subscription widget, added security if there is no list selected.
* improved Auto newsletter edition, warning added before pausing it.
* improved popups for the visual editor (themes, images, add link,...)
* updated TinyMCE to latest version, the editor now reflects the newsletter styles
* compatibility with [Magic Action Box](http://wordpress.org/extend/plugins/magic-action-box/).
* fixed links style in headings.
* fixed no default value in optin form when JS disabled.
* fixed issue with automatic latest post widget where one article could appear more than once.

= 2.0.9.5 - 2012-08-15 =

* fixed post notification hook when post's status change from publish to draft and back to publish.
* fixed firewall 2 avoid troubles with image uploader automatically
* fixed problem of confirmation page on some servers when pretty links activated on wysijap post. Default is params link now.

= 2.0.9 - 2012-08-03 =

* improved debug mode with different level for different needs
* added logging function to monitor post notification process for instance
* improved send immediately post notification (in some case the trigger was not working... using different WordPress hook now)
* fixed post notification interface (step1 and step3) not compatible with WordPress lower than 3.3
* fixed issue when duplicating sent post notifications. You should not be able to copy a child email and then change it's type like an automatic newsletter etc...
* fixed zip format error when uploading your own theme (this error was happenning on various browsers)

= 2.0.8 - 2012-07-27 =

* added default style for subscription notification which was lost
* fixed php error on subscription form creation
* fixed php error on helper back

= 2.0.7 - 2012-07-21 =

* fixed strict error appearing on servers below php version 5.4
* fixed on export to a csv translate fields and don't get the columns namekeys
* added non translated 'Loading...' string on subscription's frontend

= 2.0.6 - 2012-07-20 =

* fixed unreliable WP_PLUGIN_URL when dealing with https constants now using plugins_url() instead
* fixed automatic newsletter resending itself on unsubscribe
* fixed when unsubscribing and registering to some lists, you will not be re-registered to your previous lists
* fixed issue with small height images not displaying in email
* fixed issue with post excerpt in automatic posts
* improved php 5.4 strictness compatibility

= 2.0.5 - 2012-07-13 =

* added extended check of caching plugin activation
* added security to disallow directory browsing
* added subscription form working now with Quick-cache and Hyper cache(Already working with WP Super Cache && W3 Total Cache)
* added onload attribute on iframe subscription form which seems more reliable
* added independant cron manager wysija_cron.php
* added cleaning the queue of deleted users or deleted emails through phpmyadmin for instance
* added theme menu erasing Wysija's menu when in the position right below ours

= 2.0.4 - 2012-07-05 =

* added for dummies check that list exists or subscription form widget not editable
* fixed problem with plugin wordpress-https when doing ajax subscription
* fixed issue with scheduled articles not being sent in post notification
* fixed rare issue when inserting a WordPress post would trigger an error
* fixed issue wrong count of ignored emails when importing
* fixed multi forms several send confirmation emails on one subscribing request
* fixed subject title in email template

= 2.0.3 - 2012-06-26 =

* fixed theme activation not working
* fixed google analytics code on iframe subscription forms
* fixed post notification bug with wrong category selected when fetching articles
* fixed issue regarding category selection in auto responder / post notifications
* fixed dollar sign being stripped in post titles
* fixed warning and notices when adding a list
* fixed on some server unsubscribe page or confirmation page redirecting to 404
* improved iframe system works now with short url and multiple forms

= 2.0.2 - 2012-06-21 =

* fixed missing title on widget when cache plugin activated
* fixed update procedure to Wysija version "2.0" failed! on some MySQL servers
* fixed W3C validation for subscription form with empty action: replace with #wysija
* fixed forbidden iframe subfolder corrected to a home url with params
* improved theme installation with PclZip
* fixed missing previously sent auto newsletter on newsletters page
* fixed broken url for images uploaded in WordPress 3.4
* fixed "nl 2 br" on unsubscribed notification messages for admins
* added meta noindex on iframe forms to avoid polluting Google Analytics
* added validation of lists on subscription form
* fixed issue with image alignment in automatic newsletters
* fixed url & alternative text encoding in header/footer
* fixed images thumbs not displaying in Images tab
* fixed popups' CSS due to WordPress 3.4 update
* fixed issues when creating new lists from segment

= 2.0.1 - 2012-06-16 =

* fixed subscribers not added to the lists on old type of widget

= 2.0 - 2012-06-15 =

* A whole lot of things happened since we launched.

= 0.9 - 2011/11/23 =
* Hello World.
