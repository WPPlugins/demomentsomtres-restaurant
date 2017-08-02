=== DeMomentSomTres Restaurant ===
Contributors: marcqueralt
Tags: custom post type, restaurant, menu, carte, restaurant menu, restaurant carte
Donate link: http://www.demomentsomtres.com/en/wordpress-plugins/demomentsomtres-restaurant/
Requires at least: 3.5
Tested up to: 4.5
Stable tag: trunk
License: GPLv2 or later

DeMomentSomTres Restaurants is specifically designed to Restaurants, Bars and cafeterias to show their menus in an easy way.

== Description ==

DeMomentSomTres Restaurants is specifically designed to Restaurants, Bars and cafeterias in order to show their menus in an easy to mantain way.

It manages publish date and also expiry date allowing you to plan activity.

You can get more information about the component at the [this plugin author's website](http://demomentsomtres.com/en/wordpress-plugins/demomentsomtres-restaurant/ "This plugin page at DeMomentSomTres website")

= Features =

* Dishlist: you can show as many dishes and plates.
* Specific taxonomy type `dishlist type`: the dish lists can be grouped under an specific taxonomy. So you can have daily menu, vegetarian menus...
* Shortcode to show last contents of a taxonomy: the short code is designed to show a group of the most recent menus in a dishlist type. It was designed in order to show the current menu in the home page.
* Shortcodes to mark Ecological, Proximity, Gluten Free and Price. This shortcodes has been integrated to the editor in order to make things easy.

= History & raison d'être =

On may 2013 DeMomentSomTres was asked to build a web for [a small restaurant in Argentona (Barcelona) called La Fonda del Casal](http://www.lafondadelcasal.cat "An example of web using the DeMomentSomTres Restaurant WordPress Plugin").

Our experience building websites for small restaurants showed us the need to have a plugin to make them easy to manage their menus and meals lists. They needed an easy way to publish that but we needed it not inside the blog. A custom type was required. 
They also needed a way to show the day menu in the frontpage without rewriting it. The shortcode was born. And this shortcode allowed us to put all the menus of diferent types together in a single page but that can be managed as many: wines, salads, meals... [Test it at Fonda del Casal Restaurant](http://www.lafondadelcasal.cat/plats "Live test menu").

Later we improved it including experiences from [La Fonda Sport](http://www.fondasport.net "Restaurant i càtering a Sant Jaume dels Domenys") and also from [Semproniana](http://www.semproniana.net "El restaurant d'Ada Parellada a Barcelona").

== Installation ==

It can be installed as any other WordPress plugin. 

**It does not require [DeMomentSomTres Tools Plugin](http://demomentsomtres.com/english/wordpress-plugins/demomentsomtres-tools) anymore.**

== Frequently Asked Questions ==

= How do I update from a version prior to 2.0? =
You have to perform the upgrade process from Settings area.

= What does this update process do =
It updates the name and the content of the date field 

= How do I show the last menu of a certain type inside a post or page =

You can use the shortcode `[demomentsomtres-restaurant-dish-list type=id count=1 hidden_titles title_format="h3" prefix="" suffix="" empty="" emptyurl="" classes="" blog_id="" id=""]` to show the N current dish lists of type id inside the post or page. 

* Type id is required. Type id is the ID of the dishlist type. It can be found on the dishlist types edition page.
* Count (optional): number of dishlists to show. It is assumed to be 1 if not present. 
* Hidden titles (optional): If you want to hide the titles the parameter hidden_titles must be present.
* Title format (optional): You can define the HTML tag for titles using title_format. If no value provided it is assumed to be h3.
* Prefix (optional): content to add at the start of the title.
* Suffix (optional): content to add at the end of the title.
* Empty (optional): text to show if no menu is found.
* Emptyurl (optional): url to link to if no menu is found.
* Classes (optional): classes to add to the dishlist in order to customize it.
* blog_id (optional): id of the blog where dish_list are selected.
* id (optional): css id for the element.

= Why there's no widget to show the menus lists =

The plugin [Recent Posts Widget Extended](http://wordpress.org/plugins/recent-posts-widget-extended/) is enough for our needs and is highly customizable. As we don't like to reinvent the wheel, we did not code any widget.

= Additional shortcodes =

There'are the following additional shortcodes with the following features:

* [ECO]: inserts an `<i class="icon-eco">Ecological</i>` element.
* [PROX]: inserts an `<i class="icon-prox">Proximity</i>` element.
* [CEL]: inserts an `<i class="icon-cel">Gluten Free</i>` element.
* [P XXX]: inserts an `<div class="price">XXX</div>` element.

= How do I show all the public dish lists =

You can call the restaurant archive to show the contents on an archive basis.

= How to customize the message on expired dish lists =

To customize expiry message you can use css class `.demomentsomtres-restaurant-expired`.

== Changelog ==
= 2.1 =
* Dishlist efficiency improved
* SiteOrigin compatibility
* Bug in MCE editor calling templates

= 2.0 =
* DeMomentSomTres Tools no longer retired.
* Multiple templates
* 4.5 compatibility

= 1.9.2 =
* css id added to shortcode

= 1.9.1 =
* WPML compatibility
* Expiry tomorrow by default
* WP 4.4 compatibility

= 1.9 = 
* Two templates

= 1.8.1 =
* Previous bug solved

= 1.8 =
* Bug if not multisite in shortcode

= 1.7 =
* Multisite supported by shortcode

= 1.6 =
* Compatibility upgrade to 4.0 and WPBakery Visual Composer

= 1.5 =
* Shows a message if DeMomentSomTres Tools is not installed

= 1.4 =
* Gluten free icon and shortcode

= 1.3 =
* demomentsomtres-restaurant-dish-list shortcode parameters added to include empty message and empty html link and title prefix and title postfix.
* Editor button to copy menu template.
* Customized message when menu is expired.

= 1.1.0 =

* an archive view of dishlists avoiding them to be always hidden in order to avoid 404 errors and help SEO positioning.
* validity range showed at the top of posts and excerpt.

= 1.0.0 =

Initial release