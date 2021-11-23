=== JVM Gutenberg Rich Text Icons ===
Contributors: jorisvanmontfort
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VXZJG9GC34JJU
Tags: gutenberg, editor, icons, icon set, font awesome, fontello, ACF
Requires at least: 5.4
Tested up to: 5.8.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add Font Awesome icons, or icons from a custom icon set to rich text fields anywhere in the Gutenberg block editor!

== Description ==
Add inline icons to rich text fields like: paragraphs, headings, lists or buttons anywhere in the Gutenberg block editor.

The icon set also creates a new field type for Advanced Custom Fields (ACF) : JVM Icon. This means you can create custom fields that work with a the font awsome icon set or even beter: A custom loaded icon set.

This plugin ships with the free Font Awesome 4.7 icon set as default icon set. I am aware this is not the most recent version but 4.7 is entirely free and works fine for my projects mostly. If you want to use some other icon set or a custom created icon set, keep reading.

The plugin simply inserts icons in the following HTML format:

`
<i class="icon fa fa-address-book" aria-hidden="true"> </i>
`

The CSS class names and available icons can be all be modified to your liking if you are prepared to write some PHP hooks for your WordPress theme.

**CSS file** 
A slightly customized version of the Font Awesome 4.7 CSS file is loaded by default on the front end and backend to make the plugin work out of the box. If you want to use a custom icon set it is advised to overide the icon set json file and CSS file using hooks provided by this plugin.

**Custom icon set file** 
By default the Font Awesome 4.7 icon set is loaded from: wp-content/plugins/jvm-richtext-insert-icons/src/icons.json. The json file contains all css classes that can be turned into icons by Font Awesome 4.7 CSS file. You can load a custom json icon set file  by calling a filter hook in your (child) theme functions.php. 
For example:

`
function add_my_icons($file) {
    $file = get_stylesheet_directory().'/path_to_my/icons.json';
    return $file;
}

add_filter( 'jvm_richtext_icons_iconset_file', 'add_my_icons');
`

The icon config file can also be in fontello format since version 1.0.3. Have a look at: <https://fontello.com> to create your customized icon set.

**Custom CSS file** 
By default the Font Awesome 4.7 CSS is loaded from: wp-content/plugins/jvm-richtext-insert-icons/dist/fa-4.7/font-awesome.min.css. You can load a custom CSS file for your icon set by calling a filter hook in your (child) theme functions.php. 
For example:

`
function add_my_css($cssfile) {
    $cssfile = get_stylesheet_directory_uri().'/path_to_my/cssfile.css';
    return $cssfile;
}

add_filter( 'jvm_richtext_icons_css_file', 'add_my_css');
`

If you choose the load your own CSS file and want to disable the default CSS file use the following code:

`
add_filter( 'jvm_richtext_icons_css_file', '__return_false');
`
All icon markup has the classname "icon" prefixed to the icon HTML inserted. If you want to use some other prefix you can add a filter. Like this:

`
function my_icon_class($css_class_name) {
    return 'my-custom-css-class-name';
}

add_filter( 'jvm_richtext_icons_base_class', 'my_icon_class');
`

== Changelog ==

= 1.0.4 =
Bug fix: Replaced the deprecated block_editor_settings hook by the new block_editor_settings_all hook. This fixes a deprecated notice.

= 1.0.3 =
New feature: ACF field for the JVM icon set loaded.
New feature: Font icon config file can now also ben in fontello format

= 1.0.2 =
Bugfix: Changed backend asset loading to load only on new posts and edit post pages. In version 1.0.1 scripts for this plugin loaded on all backend pages and kept breaking the widget text editor.

= 1.0.1 =
Php error fix for some php versions on plugin activation.

= 1.0.0 =
Initial release

= Stable =
1.0.0