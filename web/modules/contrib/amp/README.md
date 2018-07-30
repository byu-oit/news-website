# Accelerated Mobile Pages (AMP)

## Requirements

* [AMP Theme](https://www.drupal.org/project/amptheme)
* [Token](https://www.drupal.org/project/token)
* [AMP PHP Library](https://github.com/Lullabot/amp-library)
* PHP version 5.5.+

## Introduction

The AMP module is designed to convert Drupal pages into pages that comply with the AMP standard. At this time, only node pages are converted.

When the AMP module is installed, AMP can be enabled for any node type. At that point, AMP content becomes available on URLs such as `node/1?amp=1` or `node/article-title?amp=1`. There are also special formatters for text, image, and video fields geared towards outputting the appropriate AMP components.

## Install AMP for Drupal

You should be [using Composer to manage Drupal site dependencies](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies).

With Composer, adding AMP tools to your site project is much like adding other modules or themes with Composer:

On the command line, enter the following commands in your project root directory:

* Download the AMP module and its dependencies, including the AMP library, with `composer require drupal/amp`.
* Download the AMP theme with `composer require drupal/amptheme`.

The AMP theme provides an AMP Base theme and an ExAMPle subtheme. You can use the included ExAMPle subtheme or create your own custom subtheme that is based on the AMP Base theme. If you want to create a custom AMP subtheme, add that subtheme to the /themes directory.

## Enable AMP

* Install at least one AMP subtheme, which can be the ExAMPle subtheme included in amptheme or a custom AMP theme you create. The AMP subthemes should have AMP Base set as their base theme; because of this, you do not need to manually enable AMP Base.
  * To install an AMP subtheme through the user interface, go to /admin/appearance.
  * To install an AMP subtheme through the command line with Drush, enter the command `drush en ampsubtheme_example` or `drush en YOUR_SUBTHEME_NAME`.
  * Do not make any AMP themes the default theme. AMP themes are only used on AMP pages.
  * Go to the theme settings page, `/admin/appearance/settings/{AMP-SUBTHEME-NAME}`. Uncheck the box to use the theme's default logo and upload a logo for the AMP subtheme, then save that change.
* Enable the AMP module. Note that this should be done after the AMP themes have been installed.

### Provide initial AMP configuration
* Go to `/admin/config/content/amp` and select your AMP configuration options:

#### Theme
* Select a theme for the AMP pages. Don't select the AMP Base theme. The subtheme you installed in the previous step should appear as an option and that is the one you should select.
* Select and save the options.

#### Content Types
* Find the list of your content types at the top of the AMP Configuration page.
* Click the link to "Enable AMP in Custom Display Settings".
* Open "Custom Display Settings" fieldset, check AMP, click Save button (this brings you back to the AMP config form).
* Click "Configure AMP view mode".
* The AMP view mode is where you can control which fields will display on the AMP page for each content type. You might only want a title, image, and body.
* There are special formatters for text, image, and iframe fields in order to output AMP components, so be sure to use them in the AMP view mode. Make sure to use the AMP Text formatter for the body field.
* Click Save button (this brings you back to the AMP config form).
* To change these later, go to `/admin/structure/types/manage/{CONTENT-TYPE}/display/amp` and set up the fields for the AMP version of each content type.

#### Analytics (optional)
* Enter your Google Analytics Web Property ID and click save.
* This will automatically be added to the AMP version of your pages.

#### Adsense (optional)
* Enter your Google AdSense Publisher ID and click save.
* Visit `/admin/structure/block` to add and configure Adsense blocks to your layout.
* Each block requires a Width, Height, and Data-slot.

#### DoubleClick (optional)
* Enter your Google DoubleClick for Publishers Network ID and click save.
* Visit `/admin/structure/block` to add and configure add DoubleClick blocks to your layout.
* Each block requires a Width, Height, and Data-slot.

#### AMP Pixel (optional)
* Check the "Enable amp-pixel" checkbox.
* Fill out the domain name and query path boxes.
* Click save.

### Set up blocks for AMP pages
* Go to `/admin/structure/block/list/{AMP-SUBTHEME-NAME}` and set up the blocks for the AMP page.
  * The AMP page is a simple page, with a header, content area, and footer. You should remove most blocks from this theme. We suggest just displaying the branding, title and content on the page. Start simple and add more elements later if desired.
  * If you want ads on your AMP pages, add AMP Ad blocks as desired. The ads will use the IDs provided in your AMP configuration.

## Configure Structured Data for AMP

AMP pages require Schema.org metadata be provided as JSON-LD in the head of the page. This can be accomplished using the [Schema.org Metadata module](https://www.drupal.org/project/schema_metadata). See links to documentation and other instructions on its project page. 

That module contains numerous sub-modules that can be used to display Schema.org metadata on various kinds of content. Enable the Schema.org Metatag base module and the Schema.org Article module. Then configure your AMP content types to display article metadata.

* Compare the JSON with the guidelines available at https://developers.google.com/search/docs/data-types/articles.
* You can copy the script element into the Structured Data Testing tool to verify that all information meets the requirements: https://search.google.com/structured-data/testing-tool.

## Module Architecture Overview

The [AMP Theme](https://www.drupal.org/project/amptheme) is designed to produce the very specific markup that the AMP standard requires. The AMP theme is triggered for any node delivered on an `?amp=1` path. As with any Drupal theme, the AMP theme can be extended using a subtheme, allowing publishers as much flexibility as they need to customize how AMP pages are displayed. This also makes it possible to do things like place AMP ad blocks on the AMP page using Drupal's block system.

The AMP Base theme takes care of converting some of the larger parts of the page into AMP. The aptly named ExAMPle theme demonstrates how to customize the appearance of AMP pages with custom styles. You will likely want to create your own custom AMP subtheme with your own styles.

The [AMP PHP Library](https://github.com/Lullabot/amp-library) analyzes HTML entered by users into rich text fields and reports issues that might make the HTML non-compliant with the AMP standard.  The library does its best to make corrections to the HTML, where possible, and automatically converts images and iframes into their AMP equivalents. More automatic conversions may be available in the future. The PHP Library is CMS agnostic, designed so that it can be used by both the Drupal 8 and Drupal 7 versions of the Drupal module, as well as by non-Drupal PHP projects. The Composer installation will take care of adding this library when the AMP module is added.

The module is responsible for the basic functionality of providing an AMP version of Drupal pages, including the following tasks:

- Create an AMP view mode, so users can decide which fields should be displayed in which order on the AMP version of a page.
- Create an AMP route, which will display the AMP view mode on an AMP path (i.e. `node/1?amp=1`).
- Create formatters for common fields, like text, image, video, and iframe that can be used in the AMP view mode to display AMP components for those fields.
- Create AMP ad blocks that can be placed by the theme.
- The theme can place AMP pixel items in the page markup where appropriate, based on the configuration options.
- Create an AMP configuration page where users can identify which ad and analytics systems to use, and identify which theme is the AMP theme.
- Create a way for users to identify which content types should provide AMP pages, and a way to override individual nodes to prevent them from being displayed as AMP pages (to use for odd pages that wouldn’t transform correctly).
- Make sure that paths that should not work as AMP pages generate 404s instead of broken pages.
- Make sure that aliased paths work correctly, so if `node/1` has an alias of `my-page`, `node/1?amp=1` has an alias of `my-page?amp=1`.
- Create a system so the user can preview the AMP page.

The body field presents a special problem, since it is likely to contain lots of invalid markup, especially embedded images, videos, tweets, and iframes. There is no easy way to convert a blob of text with invalid markup into AMP markup. At the same time, this is a common problem that other projects will run into. Our solution is a separate, stand-alone, [AMP PHP Library](https://github.com/Lullabot/amp-library) to transform that markup, as best it can, from non-compliant HTML to AMP. The AMP Text field formatter for the body will use that library to render the body in the AMP view mode.

We have done our best to make this solution as turnkey as possible, but more could be added to this module in the future. At this point only node pages can be converted to AMP. The initial module supports AMP tags such as `amp-ad`, `amp-pixel`, `amp-img`, `amp-video`, `amp-analytics`, and `amp-iframe`. Support for additional extended components may be added in the future. For now the module supports Google Analytics, AdSense, and DoubleClick for Publisher ad networks. Additional network support could be added down the road.

## Supported AMP components

- [amp-ad](https://www.ampproject.org/docs/reference/amp-ad.html)
- [amp-pixel](https://www.ampproject.org/docs/reference/amp-pixel.html)
- [amp-img](https://www.ampproject.org/docs/reference/amp-img.html)
- [amp-video](https://www.ampproject.org/docs/reference/amp-video.html)
- [amp-analytics](https://www.ampproject.org/docs/reference/extended/amp-analytics.html)
- [amp-iframe](https://www.ampproject.org/docs/reference/extended/amp-iframe.html)

Support for additional [extended components](https://www.ampproject.org/docs/reference/extended.html) may be added down the road.

## How to disable AMP
If you choose to disable AMP on your site for one or all content types, you can do so through the AMP configuration page at `/admin/config/content/amp`.

* View the list of AMP-enabled content types.
* For each AMP-enabled content type you wish to disable, click the link to "Disable AMP in Custom Display Settings".
* Open the "Custom Display Settings" fieldset, uncheck AMP, and click the Save button (this brings you back to the AMP config form).

Once the AMP view mode is disabled, that content type will no longer display AMP-formatted pages when `?amp=1` is appended to a URL for that content.

## How UI format is modified upon AMP conversion
To recap, when a page is viewed with `?amp=1` at the end of the URL (or when `&amp=1` is added to an existing query string), and that page has a content type with an AMP view mode enabled, the AMP version of that page will be displayed.

The overall format of the AMP version of a page is determined by the AMP subtheme selected for the site. The layout of the subtheme's regions, and the blocks selected for those regions, determines the overall structure of the AMP page.

The content of an individual AMP page is controlled through the fields selected in the AMP view mode for that content type. AMP-specific formatters for those fields provide options to customize the markup of AMP elements.

In particular, fields with large amounts of text (including the body field) can use the AMP Text formatter to take advantage of the AMP PHP Library. This takes care of automatic conversion of markup into AMP-friendly markup, which is particularly important for embedded content like videos and images.

The end result should be valid AMP markup, which can be verified with the AMP validation tools available through the AMP configuration page.

