<?php
/*
Plugin Name: Visitorkit
Description: A simple plugin to add the Visitorkit tracking snippet to your WordPress site.
Author: Visitorkit
Author URI: https://visitorkit.com
Version: 1.0.0
License: GPL v2 or later
Plugin URI: https://visitorkit.com/docs

Visitorkit for WordPress
Copyright (C) 2021 Khabin LLC

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

const VK_SITE_ID_OPTION_NAME = "visitorkit_site_id";
const VK_PLUGIN_VERSION = '1.0.0';

/**
 * @since 1.0.0
 */
function visitorkit_get_site_id()
{
  return get_option(VK_SITE_ID_OPTION_NAME, "");
}

/**
 * @since 1.0.0
 */
function visitorkit_register_settings()
{
  $visitorkit_logo_html = sprintf(
    '<a href="https://visitorkit.com/" style="margin-left: 6px;"><img src="%s" width=20 height=20 style="vertical-align: bottom;"></a>',
    plugins_url("visitorkit.png", __FILE__)
  );

  // register page + section
  add_options_page(
    "Visitorkit Settings",
    "Visitorkit Settings",
    "manage_options",
    "visitorkit-analytics",
    "visitorkit_print_settings_page"
  );
  add_settings_section("default", "Visitorkit {$visitorkit_logo_html}", "__return_true", "visitorkit-analytics");

  // register options
  register_setting("visitorkit", VK_SITE_ID_OPTION_NAME, ["type" => "string"]);

  // register settings fields
  add_settings_field(
    VK_SITE_ID_OPTION_NAME,
    __("Site ID", "visitorkit-analytics"),
    "visitorkit_print_site_id_setting_field",
    "visitorkit-analytics",
    "default"
  );
}

/**
 * @since 1.0.0
 */
function visitorkit_print_settings_page()
{
  echo '<div class="wrap">';
  echo sprintf('<form method="POST" action="%s">', esc_attr(admin_url("options.php")));
  settings_fields("visitorkit");
  do_settings_sections("visitorkit-analytics");
  submit_button();
  echo "</form>";
  echo "</div>";
}

/**
 * @since 1.0.0
 */
function visitorkit_print_site_id_setting_field($args = [])
{
  $value = get_option(VK_SITE_ID_OPTION_NAME);
  $placeholder = "ABC123";
  echo sprintf(
    '<input type="text" name="%s" id="%s" class="regular-text" value="%s" placeholder="%s" />',
    VK_SITE_ID_OPTION_NAME,
    VK_SITE_ID_OPTION_NAME,
    esc_attr($value),
    esc_attr($placeholder)
  );
  echo '<p class="description">' .
    __(
      'This is the <a href="https://app.visitorkit.com" target="_blank">unique Tracking ID</a> for your site',
      "visitorkit-analytics"
    ) .
    "</p>";
}

/**
 * @since 1.0.0
 */
function visitorkit_enqueue_script()
{
  $site_id = visitorkit_get_site_id();
  wp_enqueue_script( 'visitorkit', "https://sdk.visitorkit.com/v1/$site_id?wp", null, VK_PLUGIN_VERSION, true );
  
}

// Add Visitorkit to Footer
add_action("wp_enqueue_scripts", "visitorkit_enqueue_script");

// Enable Settings
if (is_admin() && !wp_doing_ajax()) {
  add_action("admin_menu", "visitorkit_register_settings");
}
