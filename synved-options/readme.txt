=== WordPress Options ===
Contributors: Synved
Donate link: http://synved.com/wordpress-options/
Tags: free, wordpress options, settings API, automatic UI, automatic settings, upload files, media files, create settings pages, custom plugin options, easy theme options, plugin, manage, automatic, api, time, administration, filter
Requires at least: 3.1
Tested up to: 3.9.1
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily add options to your products, themes or plugins! Simple to use for designers but still providing powerful features for developers.

== Description ==

[WordPress Options](http://synved.com/wordpress-options/ "WordPress Options – add options to your products, the easy way!") is a free WordPress plugin specifically directed at designers and developers who wish to easily and quickly add options to their WordPress products with minimal code while at the same time providing a lot of features for power developers to extend the way the options behave.

> #### Eager to get started using the plugin and write some code to see it in action?
> Just hop directly into the [introductory tutorial](http://synved.com/blog/help/tutorials/introduction-to-using-wordpress-options/ "Introduction to using WordPress Options")! The most efficient way to learn is by experiment. Also make sure you stay tuned for more tutorials in the future. You can ensure you won’t miss any by following us!

If you then also decide you want to provide an intuitive interface in your options panels for your users to easily install premium addons, you might want to consider purchasing the [Addon Installer](http://synved.com/product/wordpress-options-addon-installer/ "Easily distribute premium upgrades for your WordPress products with this addon for WordPress Options").

The plugin provides many different option types that cover most of any designer/developer requirements. The list of option types so far: *boolean*, *text*, *integer*, *decimal*, *color*, *image*, *video*, *media*, *style*, *script*, *user*, *author*, *category*, *page*, *tag-list*, *options-page*, *options-section*, *addon*. 

= Features =
* Creates all Options UI elements automatically
* Automatic submission and storage for options
* Uses WordPress Settings API for maximum compatibility
* Built-in value type validation ensures reliability and security
* Multiple option types to select all kinds of data
* Includes option types for WordPress-specific data
* Supports custom validation for all option types
* Options pages can be placed anywhere in the admin menu
* Callback system to dynamically change item data
* Optional addon that allows authors to [sell their own addons](http://synved.com/product/wordpress-options-addon-installer/)

= Related Links: =

* [WordPress Options Official Page](http://synved.com/wordpress-options/ "WordPress Options – add options to your products, the easy way!")
* [Simple tutorial to get you started](http://synved.com/blog/help/tutorials/introduction-to-using-wordpress-options/ "Introduction to using WordPress Options")
* [Addon Installer which allows you to distribute your own premium addons](http://synved.com/product/wordpress-options-addon-installer/ "Easily distribute premium upgrades for your WordPress products")
* [The free Stripefolio theme](http://synved.com/stripefolio-free-wordpress-portfolio-theme/ "A free WordPress theme that serves as a readable blog and a full-screen portfolio showcase") provides a very good and complete real world example on how to use the WordPress Options framework in your own themes or plugins

== Installation ==

Please look at the [introductory tutorial](http://synved.com/blog/help/tutorials/introduction-to-using-wordpress-options/ "Introduction to using WordPress Options")
Also the free [Stripefolio theme](http://synved.com/stripefolio-free-wordpress-portfolio-theme/ "A free WordPress theme that serves as a readable blog and a full-screen portfolio showcase") provides a very good and complete real world example on how to use the WordPress Options framework in your own themes or plugins

== Frequently Asked Questions ==

= How can I see this framework in action? =

Have a look at the free [Stripefolio theme](http://synved.com/stripefolio-free-wordpress-portfolio-theme/ "A free WordPress theme that serves as a readable blog and a full-screen portfolio showcase") which provides a very good and complete real world example on how to use the WordPress Options framework in your own themes or plugins

= How does the code look like? =

`$test_options = array(
	'show_tips' => array(
		'default' => false, 
		'label' => __('Show Tips', 'my-test')
	),
	'background_image' => array(
		'type' => 'image', 
		'label' => __('Background Image', 'my-test')
	),
	'header_logo_image' => array(
		'type' => 'image', 
		'label' => __('Logo Image', 'my-test'),
		'tip' => __('Maximum size is 1024x150 pixels', 'my-test')
	)
);

synved_option_register('my_test', $test_options);`

== Screenshots ==

1. An example of very basic code to create your options pages
2. This is the resulting options page created automatically with the code in the image above
3. An example code snippet on how to define your own custom (premium) addon option field
4. What the WordPress Options framework will create automatically for your custom addon code above

== Changelog ==

= 1.4.5 =
* Fix to reduce potential conflicts with other plugins using jQuery UI

= 1.4.4 =
* Fixed some warnings and notices

= 1.4.3 =
* Added usage of module property
* Move addons automatically when upgrading to ensure they are kept
* Other misc fixes

= 1.4.2 =
* Add link-target item property to link plugins to settings

= 1.4.1 =
* Adjusted callback system
* Rendering now supports context
* Fixes and cleanups

= 1.3.9 =
* First public release.

