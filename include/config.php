<?php

/**
 * We need the generic WPCSL plugin class, since that is the
 * foundation of much of our plugin.  So here we make sure that it has
 * not already been loaded by another plugin that may also be
 * installed, and if not then we load it.
 */
if (class_exists('wpCSL_plugin') === false) {
    require_once(MP_EBAY_PLUGINDIR.'WPCSL-generic/CSL-plugin.php');
}

//// SETTINGS ////////////////////////////////////////////////////////

/**
 * This section defines the settings for the admin menu.
 */

$MP_ebay_plugin = new wpCSL_plugin(
    array(
        'prefix'                 => 'csl-mp-ebay',
        'name'                   => 'Moneypress eBay Edition',
        'url'                    => 'http://cybersprocket.com/products/moneypress-ebay/',
        'paypal_button_id'       => 'LJHLF4BHYMZMQ',
        'cache_path'             => MP_EBAY_PLUGINDIR,
        'plugin_url'             => MP_EBAY_PLUGINDIR,
        'notifications_obj_name' => 'default',
        'settings_obj_name'      => 'default',
        'license_obj_name'       => 'default'
    )
);

$MP_ebay_plugin->settings->add_section(
    array(
        'name' => 'How to Use',
        'description' =>
        '<p>To use the Moneypress eBay plugin you only need to add a simple '                   .
        'shortcode to any page where you want to show eBay products.  An example '              .
        'of the shortcode is <code>[ebay_show_items keywords="kitchen furniture"]</code>. '     .
        'Putting this code on a page would show ten products from eBay matching those '         .
        'keywords, along with links to each item and their current price.  If you want '        .
        'to change how many products are shown, you can either change the default value below ' .
        'or you can change it in the shortcode itself, e.g. <code>[ebay_show_items '            .
        'keywords="kitchen furniture" number_of_products=5]</code>, which would only show '       .
        'five items.</p>' .
        '<p>If you are an eBay merchant then you can enter your seller ID below, which will '        .
        'make the plugin only list the items you are selling.  You can do this in conjunction with ' .
        'keywords, or you can simply enter your seller ID below and use the shortcode '              .
        '<code>[ebay_show_items]</code> to list every item you are selling.</p>'
    )
);

$MP_ebay_plugin->settings->add_section(
    array(
        'name'        => 'Primary Settings',
        'description' => ''
    )
);

$MP_ebay_plugin->settings->add_item('Primary Settings', 'eBay Seller ID', 'csl-mp-ebay-seller-id', 'text', false,
                                  'Your eBay seller ID.  If provided, the plugin will only shows products from you, ' .
                                  'or from whichever seller whose ID you enter.');

$MP_ebay_plugin->settings->add_item('Primary Settings', 'Number of Products', 'csl-mp-ebay-product-count', 'text', false,
                           'The number of products to show on your site.');

?>