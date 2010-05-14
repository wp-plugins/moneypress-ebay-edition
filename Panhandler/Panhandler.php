<?php

 /* Panhandler.php --- An interface for fetching product information online */

 /* Copyright (C) 2010 Cyber Sprocket Labs <info@cybersprocket.com>         */

 /* Authors: Eric James Michael Ritz <Eric@cybersprocket.com>               */

 /* This program is free software; you can redistribute it and/or           */
 /* modify it under the terms of the GNU General Public License             */
 /* as published by the Free Software Foundation; either version 3          */
 /* of the License, or (at your option) any later version.                  */

 /* This program is distributed in the hope that it will be useful,         */
 /* but WITHOUT ANY WARRANTY; without even the implied warranty of          */
 /* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           */
 /* GNU General Public License for more details.                            */

 /* You should have received a copy of the GNU General Public License       */
 /* along with this program. If not, see <http://www.gnu.org/licenses/>.    */

/**
 * Overview
 *
 * This file provides a common interface for getting product
 * information from popular online sources such as eBay, Amazon,
 * Comission Junction, and so on.  Classes which implement this
 * interface are referred to as 'drivers' in the comments below.
 */

/**
 * The Panhandles interface represents products as objects of this
 * class, which is nothing more than a simple container for common
 * pieces of data that we run across.
 */
final class PanhandlerProduct {
    public $name;
    public $description;
    public $price;
    public $web_urls;
    public $image_urls;
}

/**
 * Any driver which does not implement a method of the Panhandles
 * interface should throw this exception with an appropriate error
 * message.
 */
class PanhandlerNotSupported extends Exception {}

/**
 * If a driver requires functionality that is not a standard part of
 * PHP, then it should throw this exception after trying to load that
 * functionality and failing.  For example, if a driver requires cURL:
 *
 *     if (function_exists('curl_init') === false) {
 *         throw new PanhandlerMissingRequirement("cURL must be installed");
 *     }
 */
class PanhandlerMissingRequirement extends Exception {}

/**
 * All drivers need to implement this.
 */
interface Panhandles {

    /**
     * Accepts the identifier of a vendor as a string, and returns an
     * array of PanhandlerProduct objects representing all of the
     * items that vendor is selling.
     *
     * The $options array is a named array providing any driver
     * specific settings.  Drivers which do not use the $options given
     * are required to ignore them.
     */
    public function get_products_from_vendor($vendor, $options = null);

    /**
     * Accepts $keywords as an array of strings, and returns an array
     * of PanhandlerProduct objects representing all of the products
     * matching those keywords.
     *
     * The $options array is a named array providing any driver
     * specific settings.  Drivers which do not use the $options given
     * are required to ignore them.
     */
    public function get_products_by_keywords($keywords, $options = null);

    /**
     * Sets the maximum number of products to return from any method.
     * Any method which returns a collection of PanhandlerProduct
     * objects must not return more than the value given by this
     * method.  Preferably they should not fetch more than that value
     * either, to save on I/O.
     *
     * Drivers which do not or cannot control the number of returned
     * products should throw a PanhandlerNotSupported exception.
     */
    public function set_maximum_product_count($count);

    /**
     * Sets the page of results to return.  Some product retailers
     * allow pagination features with their search results.
     * Therefore, it is possible to get product results in chunks of N
     * products at a time.  The method takes an integer indicating
     * which 'page' products to return, where the first page is
     * numbered at one.
     *
     * This method should return no value.
     */
    public function set_results_page($page_number);

}

?>