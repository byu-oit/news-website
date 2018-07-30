CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers


INTRODUCTION
------------

Paragraphs Browser provides a form widget for the Paragraphs module. The
widgetdisplays a browser within a modal where paragraph types are grouped by
user defined categories, and provide optional description and image fields for
the browser display. Multiple browser types can be defined and paragraph types
may be grouped differently in each. An example implementation of a browser may
include the following groups: Banners, Galleries, Slideshows, Text, Media,
Lists.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/paragraphs_browser

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/paragraphs_browser


REQUIREMENTS
------------

This module requires the following modules:

 * Paragraphs (https://www.drupal.org/project/paragraphs)


INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module.

 * Visit:
   https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.


CONFIGURATION
-------------

 * Add Browsers

	 - First, you will need to add a Browser. This is done by navigating to
	 Structure » Paragraph Types and then clicking Manage Browsers. From
	 here you can add various Browsers, which are used by Paragraphs Entity
	 Reference revisions fields when the Paragraphs Browser widget is selected
	 under Form Display. These Browsers are used to broadly organize types of
	 Paragraphs, such as Layouts or Media.

 * Add Groups

	 - Each Browser can contain any number of Groups, which are used to further
	 organize &amp; filter Paragraphs within the Browser (e.g. Single Column and
	 Multi-Column Groups within the Layouts Browser). To add groups to a Browser,
	 navigate to Structure » Paragraph Types, click on Manage Browsers, and
	 then Click Manage Groups next to the desired Browser Type. From here you
	 will be able to Add, Edit, Remove, and Reorder your Groups.

 * Assign Paragraph Types To Groups

	 - Now that Browsers, and Browser Groups, have been created, it is time to
	 assign individual Paragraph Types to specific groups for display to content
	 editors. To do this, navigate to Structure » Paragraph Types »
	 [Your Paragraph Type], and then click Configure Groups. From this screen you
	 will be able to assign the Paragraph Type to specific Browser Groups. For
	 example, a 3-Column Layout Paragraph Type could be assigned to a Multi-
	 Column Group within the Layouts Browser.

 * Bring It All Together

	 - Now that you have created some Browsers and Groups, and assigned some
	 Paragraph Types to those Groups, it is time to enable the Paragraphs Browser
	 widget. To do this you will need a Content Type with a Paragraph Entity
	 Reference revisions field. Once this is created and configured to reference
	 your desired Paragraph Types, navigate to the Manage Form Display page and
	 change the Widget for this field to Paragraphs Browser. Next, click the
	 advanced settings wheel icon and select which Browser this field should use.
	 For example, a Main Content Paragraph field might reference a Layouts
	 Browser, which in turn contains groups of single and multi-column layouts
	 for content editors to choose from. Now, when adding or editing these node
	 types, the Paragraphs Browser widget should replace the default widget,
	 allowing editors to quickly and easily browse and filter various paragraph
	 types, including their respective preview images and descriptions.


MAINTAINERS
-----------

 * Michael Lander (michaellander) - https://www.drupal.org/u/michaellander
 * Stuart Clark (Deciphered) - https://www.drupal.org/u/deciphered
 * Joe Flores (Mojiferous) - https://www.drupal.org/u/mojiferous
 * Tanner Langley (tandroid) - https://www.drupal.org/u/tandroid

This project has been sponsored by:

 * Elevated Third

Specializing in Drupal-powered digital experiences that get results. Visit
https://www.elevatedthird.com/
