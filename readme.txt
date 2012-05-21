=== Wysija Newsletters ===
Contributors: wysija
Tags: newsletter, newsletters, manager newsletter, newsletter signup, newsletter widget, subscribers, subscription, email marketing, email, emailing, smtp, automatic, 
Requires at least: 3.0
Tested up to: 3.3.2
Stable tag: 1.1.5

This plugin does one thing well: create and send newsletters from WordPress. Easily.

== Description ==

Drag and drop your articles, images, social bookmarks and dividers in your newsletter. Pick one of 20 themes. Change fonts and colors on the fly. Manage your lists and subscription forms with a few clicks. Configuration is dummy proof. And if you're lost, [we're here](http://support.wysija.com/) to help. Sending newsletters from WordPress is finally fun.


= One minute video demo =

http://vimeo.com/35054446

= Features =

* Drag & drop visual editor. This is an html-free experience
* Pick one of 20 themes. Photoshop files included
* Get stats for each newsletter: opens, clicks, unreads, unsubscribes
* Add a subscription form as a sidebar widget or in your pages
* Your newsletters look the same in Gmail, iPhone, Android, Outlook, Yahoo, Hotmail, etc.
* Your WordPress users have their own list
* Import subscribers from MailChimp, Aweber, etc.
* One click import from MailPress, Tribulant, Satollo, Subscribe2, etc.
* Single or double opt-in, your choice
* Send with your web host, Gmail or SMTP
* Segment your lists based on opened, clicked & bounced
* We offer quick [support](http://support.wysija.com/)
* Free version is limited to 2000 subscribers

= Premium version =

[Wysija Premium](http://www.wysija.com/wordpress-newsletter-plugin-premium/) offers these nifty extra features:

* Unlimited number of subscribers
* Stats for individual subscribers (opened, clicked)
* Total clicks for each link in your newsletter
* Access to Premium themes
* Automated bounce handling. Keeps your list clean, avoid being labeled a spammer
* We trigger your email queue, like a real cron job
* Don't reinstall. Simply activate!
* Priority support

= Upcoming major release =

* Subscription to post notifications, like Feedburner email alerts
* Schedule sending of newsletter in future
* Autoresponder, i.e. "Send email in 3 days after X event"

= Future releases =

* Possibility to add marketing tracking codes (Premium feature)
* Support for custom post types
* Display a list of newsletters sent in a page of your site (shortcode)
* Add galleries to your newsletter

= Translations in your language =

* Your language: [get a Premium license in exchange for your translation](http://support.wysija.com/knowledgebase/translations-in-your-language/)
* Chinese - Mandarin (thanks Michael!)
* Czech (thx Ondra)
* Danish (thx Frederik)
* Dutch (dank je wel John)
* French (our bird did it)
* German (danke Wolfgang & others)
* Greek - 75% complete (thx Giorgio)
* Hungarian (thx Csaba!)
* Italian - 75% complete (grazie Nick)
* Norwegian (tysen takk Magnus)
* Polish (thx to Marcin)
* Portuguese PT - partial (obrigado Alvaro)
* Portuguese BR - (Raphael & Djio)
* Romanian (multumesc Silviu)
* Slovak (thx Jan)
* Spanish (gracias Fernando)

== Installation ==

There's 3 ways to install this plugin:

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

= 3. The old way (FTP) =
1. Upload `wysija-newsletters` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. A new menu `Wysija` will appear in your Admin

== Frequently Asked Questions ==

= Where's the "View in your browser link" ? =

There isn't any. Newsletters made with Wysija look good in all major email clients, so we avoided adding it. We're considering including it later.

= Can I drop custom post types ? =

Not yet. We're working on it for our 1.3 release.

= Submit your feature resquest =

We got a User Voice page where you can [add or vote for new features](http://wysija.uservoice.com/forums/150107-feature-request).

= Get in touch with our responsive support team =

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

= 1.1.5 - 2012-05-21 =

* improved report after importing csv
* fixed Warning: sprintf() /helpers/back.php on some environnements
* fixed roles for creating newsletters or managing subscribers "parent roles can edit as well as child roles if a child role is selected"
* fixed cron wysija's frequencies added in a cleaner way to avoid conflict with other plugins
* fixed w3c validation on confirmation and unsubscription page
* improved avoiding duplicates on environment with high sending frequencies
* removed php show errors lost in resolveConflicts


= 1.1.4 - 2012-05-14 =

* added last name to recipient name in header
* fixed automatic redirection for https links in newsletter
* fixed conflict with Advanced Custom Fields (ACF) plugin in the newsletter editor
* fixed conflict with the WpToFacebook plugin
* fixed validation on import of addresses with trim
* fixed dysfunctional unsubscribe link when Google Analytics campaign inserted
* added alphanumeric validation on Google Analytics input
* display clicked links in stats without Google Analytics parameters
* fixed page/post newsletter subscription widget when javascript conflict returns base64 string
* fixed WP users synch when subscriber with same email already exists
* fixed encoded url recorded in click stats
* added sending status In Queue to differentiate with Not Sent
* fixed automatic bounce handling
* added custom roles and permissions


= 1.1.3 - 2012-03-31 =

* fixed unsubscribe link redirection
* fixed rare issue preventing Mac users from uploading images
* added Norwegian translation
* added Slovak translation

= 1.1.2 - 2012-03-26 =

* fixed automatically recreates the subscription page when accidentally deleted
* fixed more accurate message about folder permissions in wp-content/uploads
* fixed possibility to delete synchronisable lists
* fixed pagination on subscribers lists' listing
* fixed google analytics tracking code
* fixed relative path to image in newsletter now forced to absolute path
* fixed widget alignment when labels not within field default value is now within field
* fixed automatic bounce handling error on some server.
* fixed scripts enqueuing in frontend, will print as long as there is a wp_footer function call in your theme
* fixed theme manager returns error on install
* fixed conflict with the SmallBiz theme
* fixed conflict with the Events plugin (wp-events)
* fixed conflict with the Email Users plugin (email-users)
* fixed outlook 2007 rendering issue

= 1.1.1 - 2012-03-13 =

* fixed small IE8 and IE9 compatibility issues 
* fixed fatal error for new installation
* fixed wysija admin white screen on wordpres due to get_current_screen function
* fixed unsubscribe link disappearing because of qtranslate fix
* fixed old separators just blocked the email wizard
* fixed unsubscribe link disappearing because of default color
* fixed settings panel redirection
* fixed update error message corrected :"An error occured during the update" sounding like update failed even though it succeeded
* fixed rendering of aligned text
* fixed daily report email information
* fixed export: first line with comma, the rest with semi colon now is all semi colon
* fixed filter by list on subscribers when going on next pages with pagination
* fixed get_avatar during install completely irrelevant
* fixed wordpress post in editor when an article had an image with height 0px
* fixed when domain does not exist, trying to send email, we need to flag it as undelivered after 3 tries and remove it from the queue
* fixed user tags [user:firstname | defaul:subscriber] left over when sent through queue and on some users
* fixed get_version when wp-admin folder doesn't exist...
* fixed Bulk Unsubscribe from all list "why can't I add him"

= 1.1 - 2012/03/03 =

* support for first and last names
* 14 new themes. First Premium themes
* added social bookmarks widget
* added new divider widget
* added first name and last name feature in subscription form, newsletter content and email subject
* header is now image only and not text/image
* small changes in Styles tab of visual editor
* new full width footer image area (600px)
* added transparency feature to header, footer, newsletter
* newsletter width for content narrowed to 564px 
* improved line-height for titles in text editor
* fixed Outlook and Hotmail padding issue with images
* improved speed of editor
* possibility to import automatically and keep in Sync lists from all major plugins: MailPress, Satollo, WP-Autoresponder, Tribulant, Subscribe2, etc. 
* possibility to change "Unsubscribe" link text in footer
* choose which role can edit subscribers
* preview of newsletter in new window and not in popup
* added possibility to choose between excerpt or full article on inserting WP post
* theme management with API. Themes are now externalized from plugin.
* removed numbered lists from text editor because of inconsistent display, notably Outlook

= 1.0.1 - 2012/01/18 =

* added SMTP TLS support, useful for instance with live.com smtp
* added support for special Danish chars in email subscriptions
* fixed menu position conflict with other themes and plugins
* fixed subscription form works with jquery 1.3, compatible for themes that use it
* fixed issue of drag & drop of WP post not working with php magic quotes
* fixed permissions issue. Only admins could use the plugin despite changing the permissions in Settings > Advanced. 
* fixed display of successful subscription in widget displays better in most theme
* fixed synching of WordPress user registering through frontend /wp-login.php?action=register
* fixed redirection unsubscribe link from preview emails
* fixed cross site scripting security threat
* fixed pagination on newsletter statistics's page
* fixed javascript conflict with Tribulant's javascript's includes
* improved detection of errors during installation

= 1.0 - 2011/12/23 =
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

= 0.9.6 - 2011/12/18 =
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

= 0.9.2 - 2011/12/12 =
* fixed issue with synched users on multisite(each site synch its users only)
* fixed compatibility issue with wordpress 3.3(thickbox z-index)
* fixed issue with redundant messages after plugin import
* fixed version number display

= 0.9.1 - 2011/12/7 =
* fixed major issue with browser check preventing Safari users from using the plugin
* fixed issue with wp_attachment function affecting Wordpress post insertion
* fixed issue when importing subscribers (copy/paste from Gmail)
* fixed issue related to Wordpress MU
* minor bugfixes 

= 0.9 - 2011/12/23 =
* Hello World.