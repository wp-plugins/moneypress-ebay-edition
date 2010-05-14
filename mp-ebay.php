<?php
/*
  Plugin Name: Moneypress eBay Edition
  Plugin URI: http://www.cybersprocket.com/products/moneypress-ebay/
  Description: Our Moneypress eBay plugin allows you to display products from eBay on your web site.
  Version: 1.0
  Author: Cyber Sprocket Labs
  Author URI: http://www.cybersprocket.com
  License: GPL3
*/

/* mp-ebay.php --- Moneypress eBay Edition                              */

/* Copyright (C) 2010 Cyber Sprocket Labs <info@cybersprocket.com>      */

/* Authors: Eric James Michael Ritz <Eric@cybersprocket.com>            */

/* This program is free software; you can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation; either version 3       */
/* of the License, or (at your option) any later version.               */

/* This program is distributed in the hope that it will be useful,      */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of       */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        */
/* GNU General Public License for more details.                         */

/* You should have received a copy of the GNU General Public License    */
/* along with this program. If not, see <http://www.gnu.org/licenses/>. */

if (defined('MP_EBAY_PLUGINDIR') === false) {
    define('MP_EBAY_PLUGINDIR', plugin_dir_path(__FILE__));
}

if (defined('MP_EBAY_PLUGINURL') === false) {
    define('MP_EBAY_PLUGINURL', plugins_url('',__FILE__));
}

require_once('include/config.php');

if (class_exists('eBayPanhandler') === false) {
    try {
        require_once('Panhandler/Panhandler.php');
        require_once('Panhandler/Drivers/eBay.php');
    }
    catch (PanhandlerMissingRequirement $exception) {
        add_action('admin_notices', array($exception, 'getMessage'));
        exit(1);
    }
}

add_filter('wp_print_styles', 'MP_ebay_user_css');

/**
 * Add the [ebay_show_items] short code.  The code requires the
 * attribute 'keywords', which is a list of product keywords to search
 * for.  The keywords should be separated by white-space.
 *
 * The shortcode optionally accepts an attribute 'number_of_products'
 * which takes a number and controls how many products should be
 * displayed on the page.
 */
add_shortcode('ebay_show_items', 'MP_ebay_show_items');

//// FUNCTIONS ///////////////////////////////////////////////////////

/**
 * Adds our user CSS to the page.
 */
function MP_ebay_user_css() {
    wp_enqueue_style('mp_ebay_css', plugins_url('css/mp-ebay.css', __FILE__));
}

/**
 * Processes our short code.
 */
function MP_ebay_show_items($attributes, $content = null) {
    global $current_user;
    get_currentuserinfo();

    // The key we use for making API requests.
    $ebay_app_id = "CyberSpr-e973-4a45-ad8b-430a8ee3b190";

    // Make sure the user is either an admin, in which case he
    // gets to view the results of the plugin, or otherwise
    // make sure the license has been purchased.
    if (($current_user->wp_capabilities['administrator'] == false) &&
        ($current_user->user_level != '10') &&
        (get_option('csl-mp-ebay-purchased') == false)) {
        return;
    }

    $ebay = new eBayPanhandler($ebay_app_id);
    $seller_id = get_option('csl-mp-ebay-seller-id');

    extract(
        shortcode_atts(
            array(
                'keywords' => null,
                'number_of_products' => null
            ),
            $attributes
        )
    );

    // See if we are setting a limit on how many items to show.
    if (isset($attributes['number_of_products'])) {
        $product_count = (integer) $attributes['number_of_products'];
    }
    else {
        $product_count = (integer) get_option('csl-mp-ebay-product-count');
    }

    // Even after the above two checks for places to get a product count, we may
    // still end up with a count of zero.  So we still have to make sure
    // $product_count is non-zero before calling set_maximum_product_count(),
    // otherwise we will display nothing.
    if ($product_count) {
        $ebay->set_maximum_product_count($product_count);
    }

    // If we have no keywords then we show everything associated
    // with the seller ID from the options.
    if ($keywords === null) {
        return MP_ebay_format_all_products(
            $ebay->get_products_from_vendor($seller_id)
        );
    }
    else {
        // Even if we are searching by keywords, we may still be restricting
        // our search to a specific vendor.
        if ($seller_id) {
            $products = $ebay->get_products_by_keywords(
                array($keywords),
                array('sellers' => array($seller_id))
            );
        }
        else {
            $products = $ebay->get_products_by_keywords(array($keywords));
        }

        return MP_ebay_format_all_products($products);
    }
}

/**
 * This is our HTML template for display products, which we use as an
 * argument to sprintf() in the MB_ebay_format_product() function just
 * below.  Eventually this will get factored out elsewhere.  Or that's
 * on the todo list anyways.  We'll see.  For all I know, a ravaging
 * yeti could attack the office and kill us all before we have a
 * chance to get around to it.
 */
$MB_ebay_product_template = '<div class="csl-ebay-product">
  <!-- Product Name -->
  <h3><a href="%s">%s</a></h3>
  <div class="csl-ebay-product-image">
    <!-- Image URL and Link -->
    <a href="%s" target="_new">
      <img src="%s" alt="%s"/>
    </a>
  </div>
  <!-- Description -->
  <p>%s</p>
  <!-- Price and Purchase URL -->
  <p>
    <a href="%s" target="_new">
      Purchase for %s
    </a>
  </p>
  <div style="clear: both;"></div>
</div>';

/**
 * Takes an PanhandlerProduct object and returns a string of HTML
 * suitabale for displaying that product.
 */
function MB_ebay_format_product($product) {
    global $MB_ebay_product_template;
    return sprintf(
        $MB_ebay_product_template,
        $product->web_urls[0],
        $product->name,
        $product->web_urls[0],
        $product->image_urls[0],
        $product->name,
        $product->description,
        $product->web_urls[0],
        money_format('$%i', $product->price)
    );
}

/**
 * Takes an array of PanhandlerProduct objects and returns all of the
 * HTML for displaying them on the page.
 */
function MP_ebay_format_all_products($products) {
    return implode('', array_map('MB_ebay_format_product', $products));
}

?>
