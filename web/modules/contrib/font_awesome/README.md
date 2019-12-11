# Font awesome

Integrates Drupal with [Font Awesome](https://fontawesome.com/)
the web's most popular icon set and toolkit.


## Features

* Provides a widget with an icon picker for plain text fields
* Adds a formatter for plain text fields that so they render as an icon

The module relies on the [Libraries provider module](https://www.drupal.org/project/libraries_provider)
to be able to load the JS and SVG version of Font Awesome.

## Installation

Once installed you can create plain text fields and configure their widgets
to show the [Font Awesome icon picker](https://github.com/farbelous/fontawesome-iconpicker).

Similarly you can configure the icon formatter for this fields.

## Similar modules

[Font Awesome icons](https://www.drupal.org/project/fontawesome)

* This module relies on fields to show icons so the library will only be loaded when is necessary.
* The storage of this module is simple text fields so no new field type is defined or templates override.
* The library handling options (CDN or local, minification, etc.) are offloaded to the Libraries Provider module.
* The widget provided already implements the [Font Awesome icon picker](https://www.drupal.org/project/fontawesome_iconpicker)
* The minimum supported version of Font Awesome is 5.8.0.
* No [Layering](https://fontawesome.com/how-to-use/on-the-web/styling/layering) or [power transforms](https://fontawesome.com/how-to-use/on-the-web/styling/power-transforms) supported here yet but classes can be added manually to the icon on the widget.
* No partial loading of Font Awesome supported yet.
* Ckeditor is not integrated in this module yet.


## Contributions

The project is open to improvements and new features
but also feel free to open any discussion about
how to promote and stabilize it further so more modules and themes
can rely on it.

Patches on drupal.org are accepted but merge requests on
[gitlab](https://gitlab.com/upstreamable/drupal-font-awesome) are preferred.

## Real time communication

You can join the [#font-awesome](https://drupalchat.me/channel/font-awesome)
channel on [drupalchat.me](https://drupalchat.me).

## Notes

If you are looking for a way to add Font Awesome icons to your menus you can combine Font Awesome with the following modules:

* [Menu item content fields](https://www.drupal.org/project/menu_item_fields): Add ability to add fields to your menu items.
* [Link Field Display Mode Formatter](https://www.drupal.org/project/link_field_display_mode_formatter): Display the icon inside the link inlined with the item title.

It's all about leveraging the power of composability!
