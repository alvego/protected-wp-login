=== Protected wp-login ===
Contributors: alvego
Donate link: 
Tags: protection, login, secure, key
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin protects your admin's login page (secure key in url).

== Description ==

Simple and easy WordPress plugin for protecting your login form.

Plugin implements setting the security key for the login form.

Thereby significantly reducing the likelihood of automatic brute force.

The security key is transmitted manually as GET parameter for login form.

For example: http://example.com/wp-login.php?sk=my_secure_key

Warning: Be careful if `sk` parameter is not specified, the login will not be possible, even if specifying the correct username and password.

== Installation ==

1. Upload `protected-wp-login` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure plugin: `Dashboard -> Settings -> Protected wp-login`

== Frequently asked questions ==

= A question that someone might have =

An answer to that question.

== Screenshots ==

1. Plugin options (Dashboard -> Settings -> Protected wp-login)
2. 

== Changelog ==
= 2.1 =
* Avoiding the use of anonymous functions to support php versions below 5.3 

= 2.0 =
* Add stealth mode option (totally hide login form)
* sleep() has been removed (high cpu loading reason)

= 1.0 =
* Initial release

== Upgrade notice ==
= 2.0 =
* Support backward compatibility with v1.0

= 1.0 =
* Implemented basic idea

== Arbitrary section 1 ==