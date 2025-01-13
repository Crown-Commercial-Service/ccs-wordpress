=== Media Library Categories ===
Contributors: jeffrey-wp
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SSNQMST6R28Q2
Tags: category, categories, media, library, medialibrary
Requires at least: 4.0
Tested up to: 6.7
Stable tag: 2.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds the ability to use categories in the media library.

== Description ==

Adds the ability to use categories in the WordPress Media Library. When activated a dropdown of categories will show up in the media library.
You can change / add / remove the category of multiple items at once with bulk actions.
There is even an option to filter on categories when using the gallery shortcode.

= Features WordPress Media Library Categories =
* add / edit / remove categories from media items
* change the category of multiple items at once with bulk actions
* category options & management in the Media Library
* filter on categories in the media library
* filter on categories in the gallery shortcode
* taxonomy filter
* support for WordPress 4.0 – 6.7

> <strong>Try Premium version - 100% money back guarantee</strong>
> WordPress Media Library Categories Premium adds the option to filter on categories when inserting media into a post or page.
> [Try now - 100% money back guarantee](https://1.envato.market/c/1206953/275988/4415?subId1=wpmlcp&subId2=readme&u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fmedia-library-categories-premium%2F6691290)

== Installation ==

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area
2. Search for 'Media Library Categories'
3. Click 'Install Now' and activate the plugin
4. A dropdown of categories will show up in the media library


For a manual installation via FTP:

1. Upload the 'Media Library Categories' directory to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' screen in your WordPress admin area
3. A dropdown of categories will show up in the media library


To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.
2. Activate the plugin through the 'Plugins' screen in your WordPress admin area
3. A dropdown of categories will show up in the media library

== Frequently Asked Questions ==

= How to use separate categories for the WordPress Media Library (and don't use the same categories as in posts & pages)? =
By default the WordPress Media Library uses the same categories as WordPress does (such as in posts & pages). If you want to use separate categories you can use a custom taxonomy, this can be set under Settings → Media (or click on the settings quicklink on the WordPress plugins overview page).


= How to use category in the [gallery] shortcode? =
To only show images from one category in the gallery you have to add the '`category`' attribute to the `[gallery]` shortcode.
The value passed to the '`category`' attribute can be either the `category slug` or the `term_id`, for example with the category slug:
<code>
[gallery category="my-category-slug"]
</code>
Or with term_id:
<code>
[gallery category="14"]
</code>
If you use an incorrect slug by default WordPress shows the images that are attached to the page / post that is displayed. If you use an incorrect term_id no images are shown.

Aside from this behavior, the `[gallery]` shortcode works as it does by default with the built-in shortcode from WordPress ([see the WordPress gallery shortcode codex page](https://codex.wordpress.org/Gallery_Shortcode)). If you only want to show attachments uploaded to the page and filtered by category than use the '`id`' in combination with the '`category`' attribute. For example (the id of the post is 123):
<code>
[gallery category="my-category-slug" id="123"]
</code>
Or leave id empty for current page / post:
<code>
[gallery category="my-category-slug" id=""]
</code>
In this example the slug is used, but you could also use the term_id.


= How can I filter on categories when inserting media into a post or page? =
This feature is only available in the [premium version](https://1.envato.market/c/1206953/275988/4415?subId1=wpmlcp&subId2=readme&u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fmedia-library-categories-premium%2F6691290)


= I want to thank you, where can I make a donation? =
Maintaining a plugin and keeping it up to date is hard work. Please support me by making a donation. Thank you.
[Please donate here](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SSNQMST6R28Q2)

== Screenshots ==

1. Filter by category in the media library. Use bulk actions to add and remove categories of multiple images at once.
2. Manage categories in the media library
3. Filter by category when inserting media [(premium version)](https://1.envato.market/c/1206953/275988/4415?subId1=wpmlcp&subId2=readme&u=https%3A%2F%2Fcodecanyon.net%2Fitem%2Fmedia-library-categories-premium%2F6691290)

== Changelog ==

= 2.0.2 =
* Update Select2 dependency

= 2.0.1 =
* Fix XXS vulnerability

= 2.0.0 =
* Add escape function for input field custom taxonomy slug

= 1.9.9 =
* Fix 'jQuery.fn.load() is deprecated' warning

= 1.9.8 =
* Remember selected category in dropdown when filtered

= 1.9.7 =
* Add autocomplete search to the category dropdown in Media Library list view (which can be turned on under Settings → Media)
* Add usability fix to highlight media library settings section when directly linked

= 1.9.6 =
* Fix taxonomy checkbox on media modal (when using custom taxonomy)

= 1.9.5 =
* Add autocomplete search to the category dropdown in Media Library grid view (which can be turned on under Settings → Media)
* Fix some translation strings

= 1.9 =
* Add interface (located under Settings → Media) to separate the media categories from the default WordPress categories

= 1.8 =
* Indent child categories in checklist media popup

= 1.7 =
* Support WordPress 5.0
* Support multiple slugs and id's in gallery shortcode
* Support WordPress Coding Standards 1.1.0
* Add support for [Dark Mode](https://wordpress.org/plugins/dark-mode/) in WordPress

[See complete changelog for all versions](https://jeffrey-wp.com/media-library-categories-changelog/?utm_source=plugin&utm_medium=changelog&utm_campaign=wpmlc).