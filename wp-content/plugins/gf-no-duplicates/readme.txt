=== GF No Duplicates ===
Contributors: samuelaguilera
Tags: gravityforms, Gravity Forms, duplicated submission, duplicated entry, duplicates
Requires at least: 4.9
Tested up to: 6.5.2
Stable tag: 1.2
Requires PHP: 7.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html

Prevents duplicate Gravity Forms submissions caused by the same POST request sent more than once.

== Description ==

Gravity Forms already has some built-in duplicate submission prevention techniques, including the No Duplicates setting for a field in your form, preventing the same field value from being used multiple times for the same form. Using this setting is a rock solid approach to avoid duplicate submissions, but it requires having a field in your form that you can consider as a source of unique data per submission.

This add-on helps using the same idea, without requiring having a field to enable the No Duplicates setting, by dynamically adding a hidden input with a random token to the form, and checks the [POST request](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST) received for this token value to prevent duplicate submissions.

This will **stop identical POST requests** from being accepted for entry creation, like the ones that some browsers will send when the browser back or refresh buttons are clicked or when browser tabs are restored in mobile devices.

= Limitations =

1. If for any reason the visitor manually fills the form with the same information, and submits it again, **blocking this visitor's behavior is not within the scope of this plugin functionality**.

To put in another way, the add-on is intended to **block automated resend of the same POST request data, it doesn't block submissions voluntarily initiated by the visitor**.

2. If your site is still receiving repeated POST requests **created before enabling the add-on**, therefore not containing the token field, you would still receive duplicates for these entries. There's no way for the add-on to be able stop duplicates for requests **created before enabling the add-on**.

= How it works: =

1. **When the add-on is enabled** a hidden input with a random token as value is added to each form dynamically in the front-end.
2. When a POST request is received and handled by Gravity Forms, GF No Duplicates checks if this POST request has the token and if its value was used already in an existing entry **for the form tied to the POST request**.
3. If the token parameter exists but is empty or there's any form entry where the token value was used already, GF No Duplicates stops the submission.
4. The form is replaced with an error message, which contains a link to the form page, suggesting the visitor to click the link to start a fresh new submission.

= Requirements =

* PHP 7.0 or higher.
* WordPress 4.9 or higher.
* Gravity Forms 2.5 or higher.
* The page where the form is embedded must be **excluded from cache** (if you use any caching plugin or server cache, see note below).

= Usage =

* Install and activate it as you do with any other plugin.
* Enjoy!

Optionally, you can customize the duplicate submission message shown to users from the settings page or using a filter (see the FAQ).

= Note about Caching =

The add-on functionality relies on a **random** token generated **dynamically**, so for obvious reasons caching the page where your form is embedded would prevent the add-on from working as expected. This is not a limitation of the add-on but the expected if you're serving a static version of your page, which is the only reason to cache a page, you shouldn't never cache pages where you expect dynamic data.

Most caching plugins provide you a way to exclude URLs from cache, this is for a reason, use it.

There are some web hosting providers using cache at server level and not providing their customers with an interface to exclude URLs from cache (e.g. WP Engine, Kinsta, ...), you can still ask your host support staff to add the exclusion for you.

You can also use [Fresh Forms](https://wordpress.org/plugins/fresh-forms-for-gravity/) for automated cache exclusion if you use any of the embedding methods and caching plugins supported (see Fresh Forms description for more details).

== Frequently Asked Questions ==

= Is it possible to customize the message shown for duplicate submissions? =

Yes. You can do this from the Forms > Settings > GF No Duplicates settings page. Or using the gnd_duplicate_submission_message filter. See the example below:

`add_filter( 'gnd_duplicate_submission_message', function( $message, $form ) {
	$message = 'Your custom text goes here';
	return $message;
}, 10, 2 );`

Using the filter will override the default message and any custom message that you may have added in the settings page.

== Changelog ==

= 1.2 =

* Added a settings page to allow customizing the duplicate submission message.

= 1.1.1 =

* Added the gnd_duplicate_submission_message filter to allow customizing the duplicate submission message.

= 1.1 =

* Improved logging messages to facilitate the log analysis.
* Changed the way duplicate submissions are informed to the user from a validation message to replacing the form with a message to prevent visitors ignoring the error and creating a duplicate by clicking the submit button.
* Prevent forms with User Registration Update User feeds skipping the GND token validation.

= 1.0 =

* First public release.
