<?php

/**
 * This file implements the Panhandler interface for eBay.
 */

if (function_exists('simplexml_load_string') === false) {
    throw new PanhandlerMissingRequirement("SimpleXML must be installed to use eBayPanhandler");
}
if (function_exists('curl_init') === false) {
    throw new PanhandlerMissingRequirement("cURL must be installed to use eBayPanhandler");
}

final class eBayPanhandler implements Panhandles {

    //// PRIVATE MEMBERS ///////////////////////////////////////

    /**
     * URL for invoking eBay's services.
     */
    private $ebay_service_url = "http://svcs.ebay.com/services/search/FindingService/v1";

    /**
     * The AppID given to us by eBay.
     */
    private $app_id;

    /**
     * The number of products that we return.  The value can be
     * changed by set_maximum_product_count().
     */
    private $maximum_product_count = 10;

    /**
     * The page of results we want to show.
     */
    private $results_page = 1;

    /**
     * An array of seller IDs whose products we want to display.  Each
     * ID in the array is a string.  This array can be empty if the
     * products do not need to come from any particular seller.
     */
    private $sellers = null;

    /**
     * An array of any keywords we may be using to narrow our product
     * search results.
     */
    private $keywords = null;

    //// CONSTRUCTOR ///////////////////////////////////////////

    /**
     * We have to pass in the AppID that eBay gives us, as we need
     * this to fetch product information.
     */
    public function __construct($app_id) {
        $this->app_id = $app_id;
    }

    //// INTERFACE METHODS /////////////////////////////////////

    /**
     * Takes the name of an eBay seller as a string and returns an
     * array of all of the products on sale by that vendor.
     */
    public function get_products_from_vendor($vendor, $options = null) {
        $this->sellers = array($vendor);
        $this->keywords = null;

        return $this->extract_products(
            $this->get_response_xml()
        );
    }

    /**
     * Options:
     *
     *   'sellers' : An array of strings containing seller IDs.
     *   Products returned will be restriced to these sellers.
     *
     */
    public function get_products_by_keywords($keywords, $options = null) {
        $this->keywords = $keywords;

        if (isset($options['sellers'])) {
            $this->sellers = $options['sellers'];
        }

        return $this->extract_products(
            $this->get_response_xml()
        );
    }

    public function set_maximum_product_count($count) {
        $this->maximum_product_count = $count;
    }

    public function set_results_page($page_number) {
        $this->results_page = $page_number;
    }

    //// PRIVATE METHODS ///////////////////////////////////////

    /**
     * Returns the URL that we need to make an HTTP GET request to in
     * order to get product information.
     */
    private function make_request_url() {
        $options = array(
            'OPERATION-NAME'       => 'findItemsAdvanced',
            'SERVICE-VERSION'      => '1.0.0',
            'SECURITY-APPNAME'     => $this->app_id,
            'RESPONSE-DATA-FORMAT' => 'XML',
            'REST-PAYLOAD'         => null,
            'paginationInput.entriesPerPage' => $this->maximum_product_count,
            'paginationInput.pageNumber' => $this->results_page
        );

        if ($this->keywords) {
            $options['keywords'] = urlencode(implode(' ', $this->keywords));
        }

        $options = $this->apply_filters($options);

        return sprintf(
            "%s?%s",
            $this->ebay_service_url,
            http_build_query($options)
        );
    }

    /**
     * Takes a hash of options used to build the request URL and adds
     * any applicable item filters.  In this context, 'item filters'
     * means the parameters that eBay looks for in the form of
     * 'itemFilter(x)'.
     */
    private function apply_filters($options) {
        if ($this->sellers) {
            $options['itemFilter(0).name'] = 'Seller';

            for ($i = 0; $i < count($this->sellers); $i++) {
                $options["itemFilter(0).value($i)"] = $this->sellers[$i];
            }
        }

        return $options;
    }

    /**
     * Makes a GET request to the given URL and returns the result as
     * a string.
     */
    private function http_get($url) {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        curl_close($handle);

        return $response;
    }

    /**
     * Returns a SimpleXML object representing the search results.
     */
    private function get_response_xml() {
        return simplexml_load_string(
            $this->http_get(
                $this->make_request_url()
            )
        );
    }

    /**
     * Takes a SimpleXML object representing an <item> node in search
     * results and returns a PanhandlerProduct object for that item.
     */
    private function convert_item($item) {
        $product            = new PanhandlerProduct();
        $product->name       = (string) $item->title;
        $product->price      = (string) $item->sellingStatus->currentPrice;
        $product->web_urls   = array((string) $item->viewItemURL);
        $product->image_urls = array((string) $item->galleryURL);
        $product->description = $this->create_description($item);
        return $product;
    }

    /**
     * Takes information out of an <item> node and returns a string
     * representing the description of the product, since we don't get
     * a full one back from eBay.
     */
    private function create_description($item) {
        return sprintf(
            '<ul>
               <li>Buy it Now: %s</li>
               <li>Number of Bids: %d</li>
             </ul>',
            ((string) $item->listingInfo->buyItNowAvailable === 'true') ? 'Yes' : 'No',
            (string) $item->listingInfo->bidCount
        );
    }

    /**
     * Takes a SimpleXML object representing all keyword search
     * results and returns an array of PanhandlerProduct objects
     * representing every item in the results.
     */
    private function extract_products($xml) {
        $products = array();

        if ($this->is_valid_xml_response($xml) === false) {
            return array();
        }

        foreach ($xml->searchResult->item as $item) {
            $products[] = $this->convert_item($item);
        }

        return $products;
    }

    /**
     * Takes a SimpleXML object representing a response from eBay and
     * returns a boolean indicating whether or not the response was
     * successful.
     */
    private function is_valid_xml_response($xml) {
        return (
            $xml && (string) $xml->ack === 'Success'
        );
    }
}

?>