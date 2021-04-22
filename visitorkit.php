<?php
/*
Plugin Name: Visitorkit
Description: A simple plugin to add the Visitorkit tracking snippet to your WordPress site.
Author: Visitorkit
Author URI: https://visitorkit.com
Version: 1.0.0
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

const VK_URL_OPTION_NAME = "visitorkit_url";
const VK_SITE_ID_OPTION_NAME = "visitorkit_site_id";
const VK_ADMIN_TRACKING_OPTION_NAME = "visitorkit_track_admin";

/**
 * @since 1.0.0
 */
function visitorkit_get_url()
{
  $visitorkit_url = get_option(VK_URL_OPTION_NAME, "");

  // don't print snippet if visitorkit URL is empty
  if (empty($visitorkit_url)) {
    return "cdn.usevisitorkit.com";
  }

  // trim trailing slash
  $visitorkit_url = rtrim($visitorkit_url, "/");

  // make relative
  $visitorkit_url = str_replace(["https:", "http:"], "", $visitorkit_url);

  return $visitorkit_url;
}

/**
 * @since 1.0.1
 */
function visitorkit_get_site_id()
{
  return get_option(VK_SITE_ID_OPTION_NAME, "");
}

/**
 * @since 1.0.1
 */
function visitorkit_get_admin_tracking()
{
  return get_option(VK_ADMIN_TRACKING_OPTION_NAME, "");
}

/**
 * @since 1.0.0
 */
function visitorkit_print_js_snippet()
{
  $url = visitorkit_get_url();
  $exclude_admin = visitorkit_get_admin_tracking();

  // don't print snippet if visitorkit URL is empty
  if (empty($url)) {
    return;
  }

  if (empty($exclude_admin) && current_user_can("manage_options")) {
    return;
  }

  $site_id = visitorkit_get_site_id();

  if (empty($site_id)) {
    return;
  }
  ?>
    
    
   <!-- Visitorkit - beautiful, simple website analytics -->
   <script src="https://sdk.visitorkit.com/v1/<?php echo esc_attr($site_id); ?>"></script>
   <!-- / Visitorkit -->
   <?php
}

/**
 * @since 2.0.0
 */
function visitorkit_stats_page()
{
  add_menu_page("Visitorkit", "edit_pages", "analytics", "visitorkit_print_stats_page", "dashicons-chart-bar", 6);
}

/**
 * @since 1.0.0
 */
function visitorkit_register_settings()
{
  $visitorkit_logo_html = sprintf(
    '<a href="https://usevisitorkit.com/" style="margin-left: 6px;"><img src="%s" width=20 height=20 style="vertical-align: bottom;"></a>',
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
  register_setting("visitorkit", VK_ADMIN_TRACKING_OPTION_NAME, ["type" => "string"]);

  // register settings fields
  add_settings_field(
    VK_SITE_ID_OPTION_NAME,
    __("Site ID", "visitorkit-analytics"),
    "visitorkit_print_site_id_setting_field",
    "visitorkit-analytics",
    "default"
  );
  add_settings_field(
    VK_ADMIN_TRACKING_OPTION_NAME,
    __("Track Administrators", "visitorkit-analytics"),
    "visitorkit_print_admin_tracking_setting_field",
    "visitorkit-analytics",
    "default"
  );
}

/**
 * @since 1.0.1
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
 * @since 1.0.1
 */
function visitorkit_print_site_id_setting_field($args = [])
{
  $value = get_option(VK_SITE_ID_OPTION_NAME);
  $placeholder = "ABCDEF";
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
 * @since 1.0.1
 */
function visitorkit_print_admin_tracking_setting_field($args = [])
{
  $value = get_option(VK_ADMIN_TRACKING_OPTION_NAME);
  echo sprintf(
    '<input type="checkbox" name="%s" id="%s" value="1" %s />',
    VK_ADMIN_TRACKING_OPTION_NAME,
    VK_ADMIN_TRACKING_OPTION_NAME,
    checked(1, $value, false)
  );
  echo '<p class="description">' .
    __("Check if you want to track visits by administrators", "visitorkit-analytics") .
    "</p>";
}

add_action("wp_footer", "visitorkit_print_js_snippet", 50);

if (is_admin() && !wp_doing_ajax()) {
  add_action("admin_menu", "visitorkit_register_settings");
}
