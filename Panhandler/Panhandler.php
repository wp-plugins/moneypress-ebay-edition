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
    public $name;        // String
    public $description; // String
    public $currency;    // String
    public $price;       // String
    public $web_urls;    // Array of strings
    public $image_urls;  // Array of strings
}

/**
 * This is an extremely simple class that basically serves as an easy
 * way of detecting returned errors by using is_a(). Constructor takes
 * a single param which should be a string containting the error
 * message.
 */
class PanhandlerError {
  public $message;

  public function __construct($message) {
    $this->message = $message;
  }
}

/**
 * Any driver which does not implement a method of the Panhandles
 * interface should throw this exception with an appropriate error
 * message.
 */
class PanhandlerNotSupported extends PanhandlerError {}

/**
 * If a driver requires functionality that is not a standard part of
 * PHP, then it should throw this exception after trying to load that
 * functionality and failing.  For example, if a driver requires cURL:
 *
 *     if (function_exists('curl_init') === false) {
 *         throw new PanhandlerMissingRequirement("cURL must be installed");
 *     }
 */
class PanhandlerMissingRequirement extends PanhandlerError {}

/**
 * All drivers need to implement this.
 */
interface Panhandles {

    /**
     * Returns an array of PanhandlerProduct objects.  The $options
     * argument is a hash of any driver-specific parameters to help
     * narrow down the product search.
     *
     * The drivers need to be aware of the options they are receiving
     * and they should throw a PanhandlerNotSupported exception for
     * any options that they do not understand.
     */
    public function get_products($options = null);

    /**
     * This should return an array list of all the options that the
     * driver understands. This function will be used to filter out
     * erroneous options before sending them to get_products().
     */
    public function get_supported_options();

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