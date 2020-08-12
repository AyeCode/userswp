=== UsersWP - User Profile & Registration ===
Contributors: stiofansisland, paoltaia, ayecode, ismiaini
Donate link: https://www.ko-fi.com/stiofan
Tags: login form, registration, registration form, user profile, user registration
Requires at least: 4.9
Tested up to: 5.5
Stable tag: 1.2.2.5
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Light weight frontend user registration and login plugin.

== Description ==

[Addons](https://userswp.io/downloads/category/addons/) | [Demos](https://wpgeo.directory/userswp/) | [Docs](https://userswp.io/docs/)
= THE ONLY LIGHTWEIGHT USER PROFILE PLUGIN FOR WORDPRESS. CUSTOMIZE 100% OF ITS DESIGN WITH YOUR FAVORITE PAGE BUILDER. FULLY COMPATIBLE WITH ELEMENTOR, OXYGEN, DIVI, BEAVER BUILDER, GUTENBERG AND MANY OTHER PAGE BUILDERS. USERSWP FEATURES FRONT END USER PROFILE, USERS DIRECTORY, A REGISTRATION AND A LOGIN FORM. =

While BuddyPress, Ultimate Member, Profile Builder and similar plugins are excellent, we wanted to create something much lighter and simpler to use.

Something that could be gentle on your server resources. With less options to make it easy to setup, 100% compatible with page buidlers and more hooks to make it infinitely extensible for developers.

Today UsersWP is by far the simplest solution available to manage users on WordPress. It takes seconds to setup, it is super fast and it's perfect to create a community of users within your website.

= FEATURES =

* Drag and Drop forms builder with all kind of custom fields for your user profiles.
* Shortcode for the Login form
* Shortcode for the Registration form
* Shortcode for the Edit Account form
* Shortcodes for the Users Directory
* Shortcodes for the User profile  
* Shortcode for the Password Recovery form
* Shortcode for the Change Password form
* Shortcode for the Reset Password form
* Shortcode for the Authorbox
* Custom menu items like login/logout links and links to relevant pages.

After activation all pages are created with the correct shortcodes so that you are good to go in seconds.

All shortcodes are available as widgets and blocks too, so they can be used via page builders.

You can customize the design of both the Users Directory and the User Profile templates using your favorite page builder. For example, you can decide where any element of the user profile appears. Elements like: the Avatar, the header banner, the name and all custom fields you created.    

= User Profile =

The user profile features a cover image, an avatar and an optional tabbed menu showing :

* User's posts
* User's comments
* Custom fields (if any)

You can chose if you want to hide any section of it and where to show the custom fields. In a sidebar or in their own tab.

Or you can redesign the template completely just like you modify any WordPress page. Using shortcodes, blocks or widgets, through the classic editor, Gutenberg or any page builder. 

Otherwise just customize the PHP templates as you wish within your child theme.

= Free Add-ons =

We provide some free extensions:

[Social Login](https://wordpress.org/plugins/userswp-social-login/)
[ReCAPTCHA](https://wordpress.org/plugins/userswp-recaptcha/)

= Premium Add-ons =

UsersWP can be extended with several add-ons. Few examples are:
* [~~GeoDirectory~~](https://userswp.io/downloads/geodirectory/) - NOW BUILT IN TO CORE - Create a tab for each listing type submitted, reviews and favorite listings.
* [WooCommerce](https://userswp.io/downloads/woocommerce/) - Connect WooCommerce with UsersWP, display orders and reviews in user profile pages.
* [bbPress](https://userswp.io/downloads/bbpress-2/) - Connect bbPress with UsersWP, display forum interactions in user profile pages.
* [Easy Digital Downloads](https://userswp.io/downloads/easy-digital-downloads/) - Display “Downloads” and “Purchases” tab in user profile pages.
* [WP Job Manager](https://userswp.io/downloads/wp-job-manager/) - Connects WP Job Manager with UsersWP, display Jobs tab in user profile pages.
* [MailChimp](https://userswp.io/downloads/mailchimp/) - Allows the user to subscribe to your newsletters via Mailchimp during registration.
* [Moderation](https://userswp.io/downloads/moderation/) - Lets you manually approve or reject user signups.
* [Restrict User Signups](https://userswp.io/downloads/restrict-user-signups/) - Restrict usernames and emails or even email domains to prevent spam.
* [myCRED](https://userswp.io/downloads/mycred/) - myCRED add on for UsersWP, earn points and lets users send points.
* [Profile Progress](https://userswp.io/downloads/profile-progress-2/) - Assign %'s and show profile completion progress bar via a widget.
* [Followers](https://userswp.io/downloads/followers/) - Your users can follow each other, just like twitter with this addon.
* [Friends](https://userswp.io/downloads/friends/) - Your users can send and accept friend requests, just like facebook with this addon.
* [Online Users](https://userswp.io/downloads/online-users/) - This addon displays the list of users who are currently online and more.
* [Frontend Post](https://userswp.io/downloads/frontend-post/) - Lets users submit blog post from the frontend.
* [Multisite Creator](https://userswp.io/downloads/multisite-creator/) - Lets your users to create new site for multisite on registration.


There are many others and we release new Add-ons frequently. You can see the full collection here: [UsersWP Premium Add-ons](https://userswp.io/downloads/category/addons/)

Should you find any bug, please report it in the [support forum](https://userswp.io/support/) and we will fix it asap!

UsersWP is 100% translatable.

== Installation ==

= Minimum Requirements =

* WordPress 4.9 or greater

* PHP version 5.2.4 or greater

* MySQL version 5.0 or greater

= Automatic installation =

Automatic installation is the easiest option. To do an automatic install of UsersWP, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type UsersWP and click Search Plugins. Once you've found our plugin you install it by simply clicking Install Now. [UsersWP installation](https://userswp.io/docs/userswp-overview/)

= Manual installation =

The manual installation method involves downloading UsersWP and uploading it to your webserver via your favourite FTP application. The WordPress codex will tell you more [here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).

= Updating =

Automatic updates should seamlessly work. We always suggest you backup up your website before performing any automated update to avoid unforeseen problems.

== Frequently Asked Questions ==

No questions so far, but don't hesitate to ask!

== Screenshots ==

1. User Profile Page.
2. Users Directory.
3. Registration Form.
4. Drag and Drop Profile Builder.
5. Login Form.
6. Edit Account Form.

== Changelog ==

= 1.2.2.5 =
* Add CSV to allowed mimes while importing users - ADDED
* Setting to exclude our AUI scripts from the backend pages - ADDED
* Use our AUI library for all bootstrap elements - CHANGED
* T&C and GDPR inputs in registration forms are now custom fields in form builder - CHANGED
* Optimised and removed unnecessary old CSS and JS codes - CHANGED

= 1.2.2.4 =
* Settings for minimum and maximum password length - ADDED
* Settings for minimum length for username - ADDED
* Font Awesome icon setting added so that addons can use for selecting icons - ADDED
* Users to exclude setting changed to select2 multiselect - CHANGED
* Allow to get UWP user meta using WP default get_user_meta() function with prefix uwp_meta_ and custom field key - CHANGED
* User Badge System - ADDED
* Filter for users pending email activation in backend users list - ADDED

= 1.2.2.3 =
* Remove alert on profile avatar and banner image change - FIXED

= 1.2.2.2 =
* Exclude invoice and other CPT from the post counts in user profile - FIXED
* Apply sorting to search result as well while sorting for search result in users listing page - FIXED
* Allow to add placeholder to custom fields via form builder - FIXED
* Allow to enable registration from setup wizard if disable in WP settings - ADDED
* User meta data on profile page for SEO - ADDED
* Allow to change height width of avatar and banner upload from backend - ADDED
* GD Listings shortcode parameter current_author should display listing of displayed user in profile - FIXED

= 1.2.2.1 =
* Change wp_mail_from fallback to wordpress@yourdomain.com - FIXED
* Country field not displaying in more info tab - FIXED
* Allow user to resend activation link on login if not activated account - FIXED
* Allow displaying user profile header anywhere - FIXED
* Allow user to remove cover image - ADDED
* Class in frontend body tag for UsersWP pages - ADDED
* Pass arguments to template via function parameter instead of using global variable - CHANGED
* Separate shortcode for User Avatar image, Cover image and Post Counts - CHANGED

= 1.2.2 =
* Delete listing in profile page is not reloading after deleting - FIXED
* Tool to make custom fields dynamic strings available for translation - ADDED
* Delete account displaying errors after delete - FIXED
* Menu items might not be hidden if page slug changed or they have other classes - FIXED

= 1.2.1.9 =
* Oxygen editor and other known page builder plugins compatibility - ADDED
* Menu pages not triggering lightbox when page name changed - FIXED
* Sub menu items can trigger parent menu item lightbox - FIXED
* Wait longer for invisible re-captcha response before showing error - FIXED
* WP Custom Admin Interface plugin conflict - FIXED

= 1.2.1.8 =
* Custom field should auto generate key from title if key is not provided - FIXED
* Child tab not showing output - FIXED
* Date format not working properly for custom field with type date - FIXED
* Login redirect url encoded causes redirect issue - FIXED
* Short tag to send user submitted information in email for register form - ADDED

= 1.2.1.7 =
* Activation link tag not working for new registrations - FIXED

= 1.2.1.6 =
* AyeCode Connect notice now shows on extensions pages - ADDED

= 1.2.1.5 =
* Account success email sent to user instead of admin - FIXED
* Register button disabled after clicking without recaptcha - FIXED
* Listing showing default post type even if not added from settings - FIXED

= 1.2.1.4 =
* Email templates for sending emails from UsersWP - CHANGED
* Changes regarding including templates and removed old template functionality - CHANGED
* Allow users to delete their account from frontend - ADDED
* Flush rewrite rules on 404 page - REMOVED
* Remove check for profile page for profile header shortcode so one can display it anywhere on site - CHANGED
* Filter for adding #tab-content link for profile tabs - ADDED

= 1.2.1.2 =
* Changes for reCaptcha addon not showing error if not ticked - FIXED
* New comment template time can show wrong date in some cases - FIXED
* Redirect sub template pages to home for non loggedin users - FIXED
* Recommended plugins step added to setup wizard - ADDED
* User meta shortcode for multiselect field not displaying values - FIXED
* GDPR acceptance on registration - ADDED

= 1.2.1.1 =
* Formatting textarea custom field in the tab content - FIXED
* Short tag to send user submitted information in email for account update form - ADDED
* Reload recaptcha after form submission error - FIXED
* Limit the author bio content in author box which can be changed by filter - CHANGED
* Plugin setup wizard - ADDED
* Default and custom redirect option to register and login page - ADDED
* Remove password neg on reset password - ADDED
* Password strength indicator on reset password form - ADDED
* Country field in account form conflict with the Location manager plugin - FIXED
* Status showing activity, followers and friends tables missing even no add on active - FIXED
* Remove general shortcodes tab in settings - FIXED
* Remove Is Public option from form builder for default fields - FIXED

= 1.2.0.12 =
* GD List Manager plugin integration in core - ADDED
* Checking author archive enabled in Yoast plugin old version throws PHP error - FIXED
* Bio show more/less option not working correctly - FIXED

= 1.2.0.11 =
* Old password showing invalid always on change password form - FIXED
* Changes to fix unknown column error for avatar and banner usermeta - FIXED
* Changes to fix redirection issue when author redirect is disabled - FIXED
* Using profile tabs widget twice on a page can result in a error - FIXED

= 1.2.0.10 =
* Profile tabs privacy settings for users - ADDED
* Avatar and banner fields showing blank on admin side edit user profile page - FIXED
* Hide cover image argument in profile header shortcode not working for bootstrap layout - FIXED
* Display table for profile image not displaying right on firefox - FIXED

= 1.2.0.9 =
* Profile tabs now used #anchor so to take user to the content - ADDED
* Profile image on small screens can be forced to 100% width - FIXED
* User favourites not displaying correctly to other users in some cases - FIXED

= 1.2.0.8 =
* Profile image upload not showing upload progress - FIXED
* Profile image crop function set higher memory limits for large images - FIXED

= 1.2.0.7 =
* Ajax undefined variable when adding new banner image - FIXED
* Default image can sometimes be broken on localhost - FIXED
* Registration block JS can brake the block render in backend - FIXED
* Register can open in modal lightbox even if set not to - FIXED
* Menus items as sub items can trigger parent modal - FIXED

= 1.2.0.6 =
* Country field not working on register lightbox - FIXED
* Added admin notice if Yoast has disabled user profiles - ADDED

= 1.2.0.4 =
* Tabs not showing other users tabs properly - FIXED
* HTML font size on some themes causing small font and buttons - FIXED

= 1.2.0 =
* New bootstrap UI styles option - ADDED
* New improved tabs system introduced - ADDED
* Login / Register lightbox option added - ADDED
* Password strength indicator added (BS-UI only) - ADDED

= 1.1.4 =
* Elementor template with UsersWP shortcode does not output the other content than shortcode - FIXED
* Changing login URL breaks Jetpack SSO - FIXED
* Redirect back to admin panel after login from UWP login page if redirect wp-admin login is enabled - FIXED
* Login with AJAX in bootstrap modal - ADDED

= 1.1.3 =
* Excluding user ids can cause user loop not to show - FIXED

= 1.1.2 =
* Allow to disable avatar image override by UsersWP plugin - ADDED
* Author box breaking the sites with elementor - FIXED
* UsersWP pages URL fixes for Polylang compatibility - FIXED
* Missing fields in Form builder for account and register forms in multisite - FIXED
* Add select all to add UsersWP menu items - ADDED
* Allow admin to approve users having pending email verification - ADDED
* Shortcodes for profile page individual elements for preview in builder like elementor - ADDED
* New parameters for profile page individual shortcodes - ADDED
* Display states for UWP pages in page list table - ADDED
* Social icon not showing icon image if set from backend - FIXED
* Updated Font Awesome version to 1.0.11 - CHANGED
* Use 404 redirection instead of loading the 404.php from the theme for user profile not found - FIXED
* Individual shortcodes and widgets for profile and users page for gutenberg blocks and elementor plugin - ADDED
* Page is not created on activation if the same slug is used by menu - FIXED

= 1.1.1 =
* Redirect not working after registration for auto approve auto login - FIXED
* Remove subtabs in GD listings, favourite and reviews tabs if no content - FIXED
* Extensions screen containing all available add ons for UsersWP and recommended plugins - ADDED

= 1.1.0 =
* New settings screen, all addons will need to be updated - BREAKING CHANGE
* Replace chosen with select2 - CHANGED
* Super Duper updated to v1.0.12 - CHANGED
* Allow user to register without password field - FIXED
* Display Author Box for posts or custom post types - ADDED
* Settings for Author Box - ADDED
* Replace author link with the profile page - CHANGED
* Setting to disable author link replaced with the profile page - ADDED
* Allow fieldset to display in profile own tab - FIXED
* Replace default lost password URL with UWP forgot password page URL - FIXED
* Fieldset in profile own tab display field only if value assigned - FIXED

= 1.0.23 =
* UWP Login widget breaks page for logged in users - FIXED
* Default WP registration page not being redirected if set to do so - FIXED
* Field privacy setting "Let User Decide" is not working - FIXED
* Change password form not rendering - FIXED

= 1.0.22 =
* Delete UWP pages on uninstall - FIXED
* Elementor builder error page could not loaded while edit some UWP pages - FIXED
* Convert shortcodes to widgets - CHANGED
* Filter WP default register URL with UWP registration page URL - CHANGED
* Super Duper updated to v1.0.7 - CHANGED
* Profile page listings tab shows incorrect count when WPML installed - FIXED
* Login widget now has option to show logged in dashboard (DEFAULT BEHAVIOUR CHANGED)- ADDED

= 1.0.21 =
* Update users meta from WP user data in background instead of on activation - FIXED
* Follow redirect param for the links on the login and register forms - FIXED
* Added UWP installation mode in status page - ADDED
* Improved field privacy settings and functionality - CHANGED
* Invoice actions in profile invoice tab - ADDED
* Use default description instead of bio - CHANGED
* Allow to remove dummy users from tools - ADDED
* Improved fix user data in tools - CHANGED
* Improved Import Export functionality - CHANGED
* Removed Read more link from profile tab items - CHANGED
* Moved actions after summary in profile tab items - CHANGED
* Use the new listing status for listings tab and count - CHANGED
* Allow to change per page limit for number of users to export csv - CHANGED

= 1.0.20 =
* Compatibility with GDV2 - ADDED
* Uninstall fails due to missing dependency functions - FIXED
* Separate the invoicing functionality from the GD class - CHANGED
* Change wpDiscuz profile URL to UWP profile URL - FIXED
* Users can Mute specific email notifications - ADDED
* Profile tab order not working - FIXED
* Custom fields in available tab settings with ordering - ADDED
* Status report for compatibility checks and plugin support - ADDED
* Updated to use Font Awesome 5 JS version - CHANGED
* Profile tab order gives error sometimes - FIXED

= 1.0.19 =
* Activator class missing some dependency functions can make plugin update fail - FIXED

= 1.0.18 =
* Choose tabs in profile setting not displaying tabs after save - FIXED
* Fix strings typos and translations- FIXED
* WP min version - CHANGED
* Remove password field from account - FIXED

= 1.0.17 =
* In some cases the usermeta table is not created on activation - FIXED

= 1.0.16 =
* Fix Default Avatar settings in admin - FIXED
* Tab re-order in profile tab settings page - FIXED
* Listing Type Tab Order as GD CPT Order - FIXED
* Multisite compatibility changes - CHANGED

= 1.0.15 =
* Fix text domain in privacy message - FIXED
* Fix design issues for login and register forms in widget and shortcode - FIXED

= 1.0.14 =
* Added filter to change the default subtab displayed under listing tab in user profile - ADDED
* Fix escape slashes in content of profile fields - FIXED
* Comment count includes other post types as well - FIXED
* Allows admin to change avatar and banner of other user from backend - CHANGED
* Change settings text from "Dashboard" to "Loginbox" - CHANGED
* Implement widget and gutenberg blocks using super duper class - CHANGED
* Filter to exclude account fields from edit profile fields in admin side - ADDED
* Reviews tab using gravatar when comments are using UsersWP profile - FIXED
* Default avatar showing next to review instead of listing featured image - FIXED
* GDPR Compliance - ADDED
* Login redirect issues for last user page - FIXED

= 1.0.13 =
* Allow to change icon for fieldset while displaying as a profile tab - CHANGED
* Option to exclude user from lists is missing from wp-admin - FIXED
* Change text domains in class geodirectory plugin - FIXED
* Delete user meta on removing from site of multisite network - FIXED
* Add hook to modify the content of forgot password email. - ADDED
* Add Resend Activation Email for users - ADDED
* Reviews should not be displayed for deleted listings - FIXED
* Option to disable default password nag notice - ADDED
* Added WPML compatibility - ADDED
* Style is not proper on pages with shortcode but not assigned in page settings - FIXED
* Filter to force redirect after registration - ADDED

= 1.0.12 =
* Filter added to wp_login_url() to filter change all login urls to the UWP one - CHANGED
* Settings added to change subject & content for Password change notification - CHANGED
* redirect_to REQUEST variable will take priority over UWP page redirects - CHANGED
* Login/register specific notices will only show on their respective locations - CHANGED
* Icon url in custom field icon does not working - FIXED
* Merge UWP GeoDirectory functionality into UWP core - CHANGED
* First/Last name fields breaks the design if labels are displayed in forms - FIXED
* Old avatar/banner file should be removed on a new avatar/banner upload - CHANGED
* after login it should redirect to prev page - FIXED
* Add default image options - FIXED
* Conflict with Google Captcha plugin. - FIXED
* Use site specific dummy user passwords - CHANGED

= 1.0.11 =
* Some emails have an extra opening p tag - FIXED
* WP 4.8.3 can cause Edit profile page to not display all fields - FIXED

= 1.0.10 =
* Default value can now be used to set the default country by setting the the two letter country code, example "de" - ADDED
* Fatal error: Class ‘UsersWP_Meta’ not found in new installation - FIXED

= 1.0.9 =
* Upgrade function changed to run on version change and not on activation hook only - FIXED

= 1.0.8 =
* For admin use option added - ADDED
* Username label changed to "Username or Email" - CHANGED
* New email notification tags breaks reset and activate keys - FIXED
* Profile image change popup can sometimes be hidden or not clickable - FIXED
* uwp_form_input_field_xxx filter added - ADDED

= 1.0.7 =
* Edit user in wp-admin does not display country input correctly - FIXED
* Tools page added - ADDED
* Major code refactoring - CHANGED
* Class names renamed from Users_WP to UsersWP for better naming and consistency - CHANGED
* Email links are displayed as plain text - FIXED
* Login widget redirects to current page - ADDED
* Register admin notification setting - ADDED
* File Preview Not working properly - FIXED
* Font awesome icon displayed instead of count 1 for custom field "display as tab" - CHANGED
* Updates not creating the new table columns - FIXED
* Fieldset with its fields can be displayed in own profile tab - ADDED
* Url field value not getting printed in more info tab - FIXED
* Bio not displaying correctly - FIXED
* User bio slashes contains duplicate slashes - FIXED
* User privacy settings not working correctly - FIXED
* Email notifications code refactored to override via hooks - CHANGED
* Email error logs now contains full error in json format - ADDED
* Extra tags added for forgot and activate mails - ADDED
* Register widget - ADDED
* WPML compatibility - ADDED
* Display confirm password and confirm email fields only on register form - CHANGED
* Avatar breaks when social login profile url used - FIXED
* Facebook profile image can break profile image - FIXED
* Some profile page CSS changes - CHANGED

= 1.0.6 =
* First release on WordPress.org - :)
* Checks profile tabs array is unique before saving - ADDED
* fade and show class renamed to avoid conflict with other themes - CHANGED
* Chosen select inputs on form builder CSS issue, too thin - FIXED

= 1.0.4 =
* PHP < 5.5 compatibility changes - FIXED

= 1.0.3 =
* Added callback to show info type setting - ADDED
* Profile tabs now appear in the order they are added - CHANGED

= 1.0.1 =
* First beta release.

= 1.0.0 =
* First alpha release.

== Upgrade Notice ==

= 1.1.0 =
* New settings screen, all addons will need to be updated - BREAKING CHANGE
