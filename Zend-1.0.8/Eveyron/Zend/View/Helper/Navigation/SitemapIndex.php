<?php
/**
 * Eveyron PHP Library
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 *
 * @category Eveyron
 * @author eveyron@eveyron.com
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @link http://www.eveyron.com
 * @version 2010-10-16 13:33:33Z
 */
 
/**
 * @see Eveyron_Zend_View_Helper_Navigation_Sitemap
 */
require_once 'Eveyron/Zend/View/Helper/Navigation/Sitemap.php';

/**
 * @category Eveyron
 * @package Eveyron_Zend
 * @subpackage View
 * @copyright Copyright (c) 2010 eVeyron.com (http://www.eveyron.com)
 * @license http://www.opensource.org/licenses/bsd-license.php  New BSD License
 */
class Eveyron_Zend_View_Helper_Navigation_SitemapIndex
    extends Eveyron_Zend_View_Helper_Navigation_Sitemap
{
    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               operate on
     * @return Zend_View_Helper_Navigation_Sitemap   fluent interface, returns
     *                                               self
     */
    public function sitemapIndex(Zend_Navigation_Container $container = null)
    {
        return parent::sitemap($container);
    }
        
    /**
     * Returns a DOMDocument containing the Sitemap XML for the given container
     *
     * @param  Zend_Navigation_Container $container  [optional] container to get
     *                                               breadcrumbs from, defaults
     *                                               to what is registered in the
     *                                               helper
     * @return DOMDocument                           DOM representation of the
     *                                               container
     * @throws Zend_View_Exception                   if schema validation is on
     *                                               and the sitemap is invalid
     *                                               according to the sitemap
     *                                               schema, or if sitemap
     *                                               validators are used and the
     *                                               loc element fails validation
     */
    public function getDomSitemap(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        // check if we should validate using our own validators
        if ($this->getUseSitemapValidators()) {
            // require_once 'Zend/Validate/Sitemap/Changefreq.php';
            // require_once 'Zend/Validate/Sitemap/Lastmod.php';

            // create validators
            $locValidator        = new Zend_Validate_Sitemap_Loc();
            $lastmodValidator    = new Zend_Validate_Sitemap_Lastmod();
        }

        // create document
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->getFormatOutput();

        // ...and urlset (root) element
        $urlSet = $dom->createElementNS(self::SITEMAP_NS, 'sitemapindex');
        $dom->appendChild($urlSet);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
            RecursiveIteratorIterator::SELF_FIRST);

        $maxDepth = $this->getMaxDepth();
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }
        $minDepth = $this->getMinDepth();
        if (!is_int($minDepth) || $minDepth < 0) {
            $minDepth = 0;
        }

        // iterate container
        foreach ($iterator as $page) {
            if ($iterator->getDepth() < $minDepth || !$this->accept($page)) {
                // page should not be included
                continue;
            }

            // get absolute url from page
            if (!$url = $this->url($page)) {
                // skip page if it has no url (rare case)
                continue;
            }

            // create url node for this page
            $urlNode = $dom->createElementNS(self::SITEMAP_NS, 'sitemap');
            $urlSet->appendChild($urlNode);

            if ($this->getUseSitemapValidators() &&
                !$locValidator->isValid($url)) {
                // require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(sprintf(
                        'Encountered an invalid URL for Sitemap XML: "%s"',
                        $url));
                $e->setView($this->view);
                throw $e;
            }

            // put url in 'loc' element
            $urlNode->appendChild($dom->createElementNS(self::SITEMAP_NS,
                                                        'loc', $url));

            // add 'lastmod' element if a valid lastmod is set in page
            if (isset($page->lastmod)) {
                $lastmod = strtotime((string) $page->lastmod);

                // prevent 1970-01-01...
                if ($lastmod !== false) {
                    $lastmod = date('c', $lastmod);
                }

                if (!$this->getUseSitemapValidators() ||
                    $lastmodValidator->isValid($lastmod)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'lastmod',
                                              $lastmod)
                    );
                }
            }
        }

        // validate using schema if specified
        if ($this->getUseSchemaValidation()) {
            if (!@$dom->schemaValidate(self::SITEMAP_XSD)) {
                // require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(sprintf(
                        'Sitemap is invalid according to XML Schema at "%s"',
                        self::SITEMAP_XSD));
                $e->setView($this->view);
                throw $e;
            }
        }

        return $dom;
    }        
}