OAuth Addon
========

The OAuth Addon enables developers to easily provide OAuth specific features to their application, allowing users to login or register by authenticating with an existing 3rd party provider (i.e. Google, Facebook, Twitter, etc.) via OAuth 2.0. Once a 3rd party authenticated user session has been provided the developer can use the session to provide other services.

**DISCLAIMER:** This is a [Div Starter](https://github.com/DivTruth/div-starter) addon which can be used as is or serve as a boiler plate to your site's application. If you have questions about Div Starter please see the documenation there. Also, if you haven't already please read the [Div Blend Approach](http://divblend.com/div-blend/) which outlines the framework and its intentions.

**In this readme:** [Features](#features) - [Requirements](#requirements) - [Quick Start](#quick-start) - [FAQ](#faq) - [Roadmap](#roadmap) - [History](#history)

Features
--------
* Providers currently include
   * Google
   * Office 365
   * Salesforce
   * ...more to come (easy to add as well)
* Single Sign-On (SSO) for all providers
   * Match a WP user to their provider by email
   * Unlink accounts from user profile

Requirements
------------
* Requires [Div Starter](https://github.com/DivTruth/div-starter) and all of its dependencies. 
* By default relies on the [Site Menu addon](https://github.com/DivTruth/site-menus-addon), can be adjusted to house settings elsewhere

Quick Start
-----------
* Simple add this package to your Div Starter (or Site Application) within `/mu-plugins/`. Assuming [Div Library](https://github.com/DivTruth/div-library) is activated the add on should be initiated. Navigate to "Site Options -> Settings" (by default) to begin setting up

FAQ
---
* For questions please tweet us [@DivTruth](https://twitter.com/DivTruth)

Roadmap
-------
* Add more providers
* Register a new WP user 
* Link more accounts from user's profile
* Specific settings page
* Add icon sets
* Ability to acquire a registering user's third-party username / nickname / email address and auto-populate the WordPress user profile. Works as an alternative to the userXX naming pattern.
* Add better setup documentation per provider
* Add more customization settings for (per feature)

Contribution
-------
If you would like to contribute to this addon please fork and submit a pull request. If you have questions you can reach out to us directly on [Twitter](https://twitter.com/DivTruth). If you have a feature request please submit [here](https://github.com/DivTruth/oauth-addon/issues)