<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension;

/**
 * Page model
 *
 * It's a tree that consists of fields, selcts, buttons, etc.
 * Each element has corresponding xpath query.
 */
class Page extends \DOMDocument implements \Serializable
{
    /**
     * Page XML scheme namespace ID
     *
     * @var string
     */
    const NAMESPACE_URI = 'urn:magento:uimap-page';

    /**
     * XPath processor associated with page
     *
     * @var DOMXPath
     */
    private $xpath;

    /**
     * Regular expression that can be used for URL matching
     * @var unknown
     */
    private $urlRegexp;

    /**
     * Contructs page
     *
     * @param string $pageXml XML representation of page
     * @throws \InvalidArgumentException
     */
    public function __construct($pageXml = null)
    {
        parent::__construct('1.0', 'utf-8');
        $pageXml = $pageXml ?: '<page xmlns="' . self::NAMESPACE_URI . '" url=""/>';

        if (!@$this->loadXML($pageXml)) {
            $error = libxml_get_last_error() ? libxml_get_last_error()->message : '';
            throw new \InvalidArgumentException("XML code of page is not well-formed: $error\n$pageXml");
        }
        if (!$this->isValid()) {
            $this->formatOutput = true;
            $error = libxml_get_last_error() ? libxml_get_last_error()->message : '';
            throw new \InvalidArgumentException("Invalid XML code of page: $error\n{$this->saveXML()}");
        }
        $this->xpath = new \DOMXPath($this);
        $this->xpath->registerNamespace('p', self::NAMESPACE_URI);
    }

    /**
     * Returns true if page valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return @$this->schemaValidate(__DIR__ . DIRECTORY_SEPARATOR . 'page.xsd');
    }

    /**
     * Returns true if page corresponds to the URL
     *
     * @param string $url URL
     * @return boolean
     */
    public function isPageCorrespondsToUrl($url)
    {
        return $this->getUrl() && (bool)preg_match($this->getUrlRegexp(), $this->normalizeUrl($url));
    }

    /**
     * Returns regular expression that can be used for matchign page to URL
     *
     * @throws \RuntimeException On regulag expression buildign error
     * @return string
     */
    protected function getUrlRegexp()
    {
        if (!$this->urlRegexp) {
            $dlm = '|';
            $regexp = preg_replace('#%[^%]+%#', '(.*)', $url = preg_quote(rtrim($this->getUrl(), '/'), $dlm));
            if (null === $regexp) {
                $this->formatOutput = true;
                throw new \RuntimeException("Error on calculation URL's regular expression for URL: $url of page:\n" . $this->saveXML());
            }
            $this->urlRegexp = $dlm . $regexp . '$' . $dlm . 'U';
        }

        return $this->urlRegexp;
    }

    /**
     * Removes trash from URL (key/, filters/, etc) also removes trailing /
     *
     * @param string $url
     * @return string Normalized URL
     */
    protected function normalizeUrl($url)
    {
        return
            rtrim(
                preg_replace(
                    array('#/key/[^/]+/#iU', '#/filter/[^/]+/#iU', '#/form_key/[^/]+#iU', '#/index/$#iU'),
                    '/',
                    $url
                ),
                '/'
            );
    }

    /**
     * Serializes page
     *
     * @return string XML representation of page
     * @see Serializable::serialize()
     */
    public function serialize()
    {
        return $this->saveXML();
    }

    /**
     * Unserializes page from XML
     *
     * @see Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        $this->__construct($serialized);
    }

    /**
     * Sets page key
     *
     * @param string $value
     */
    public function setKey($value)
    {
        $this->documentElement->setAttribute('key', $value);
    }

    /**
     * Sets page title
     *
     * @param string $value
     */
    public function setTitle($value)
    {
        $this->documentElement->setAttribute('title', $value);
    }

    /**
     * Sets page URL
     *
     * @param string $value
     */
    public function setUrl($value)
    {
        $this->documentElement->setAttribute('url', $value);
    }

    /**
     * Returns page key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->documentElement->getAttribute('key');
    }

    /**
     * Returns page title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->documentElement->getAttribute('title');
    }

    /**
     * Return URL with replaced params by pattern.
     *
     * For example:
     *	URL: http://host/%aaa%
     *	Params array('aaa' => 'BBB')
     *  Result: http://host/BBB
     *
     * @return array
     */
    public function getUrl(array $params = array())
    {
        $values = array_values($params);
        $pattern = array_map(function ($key) {return "%$key%";}, array_keys($params));

        return str_replace($pattern, $values, $this->documentElement->getAttribute('url'));
    }

    /**
     * Returns XPath query of page element that correstonds to given locator
     *
     * @return string XPath query
     * @throws \OutOfRangeException If cannot find path by locator
     */
    public function getXpath(Locator $locator)
    {
        // prepare query
        $query = '';
        if ($locator->hasTab()) {
            $query .= "p:tab[@key='{$locator->getTab()}']";
        }
        if ($locator->hasFieldset()) {
            $query .= "//p:fieldset[@key='{$locator->getFieldset()}']";
        }
        if ($locator->hasKey()) {
            $query .= '//p:' . ($locator->getType() ?: '*') . "[@key='{$locator->getKey()}']";
        }
        $query .= '/ancestor-or-self::*/@xpath';

        // execute query
        $list = $this->query($query);

        // precess result
        if (!$list->length) {
            throw new \OutOfRangeException("Cannot find XPath by $locator on page '{$this->getKey()}'.");
        }

        $result = '';
        foreach ($list as $item) {
            $result .= $item->value;
        }

        if ($locator->hasParameters()) {
            $patterns = array_map(function ($p) {return "%$p%";}, array_keys($locator->getParameters()));
            $values = array_values($locator->getParameters());
            $result = str_replace($patterns, $values, $result);
        }

        return $result;
    }

    /**
     * Execute XPath query on root of document
     *
     * @param string $xpathExpression
     * @return \DOMNodeList
     */
    protected function query($xpathExpression)
    {
        return $this->xpath->query($xpathExpression, $this->documentElement);
    }

    /**
     * Creates page element
     *
     * @param string $type Type
     * @param string $key Key
     * @param string $xpath XPath
     * @throws \InvalidArgumentException If type, key or XPath query is not string
     * @return \DOMElement Page element
     */
    protected function createElementOfType($type, $key, $xpath)
    {
        if (!is_string($type)) {
            throw new \InvalidArgumentException('Type should be string ' . gettype($type) . " given (key: $key, xpath: $xpath).");
        }
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Key should be string ' . gettype($key) . " given (type: $type, xpath: $xpath).");
        }
        if (!is_string($xpath)) {
            throw new \InvalidArgumentException('XPath should be string ' . gettype($xpath) . " given (type: $type, key: $key).");
        }

        $element = $this->createElementNS(self::NAMESPACE_URI, $type);
        $element->setAttribute('key', $key);
        $element->setAttribute('xpath', $xpath);

        return $element;

    }

    /**
     * Creates tab element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Tab element
     */
    public function createTab($key, $xpath)
    {
        return $this->createElementOfType('tab', $key, $xpath);
    }

    /**
     * Creates fieldset element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Fieldset element
     */
    public function createFieldset($key, $xpath)
    {
        return $this->createElementOfType('fieldset', $key, $xpath);
    }

    /**
     * Creates field element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Field element
     */
    public function createField($key, $xpath)
    {
        return $this->createElementOfType('field', $key, $xpath);
    }

    /**
     * Creates select element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Select element
     */
    public function createSelect($key, $xpath)
    {
        return $this->createElementOfType('select', $key, $xpath);
    }

    /**
     * Creates button element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Button element
     */
    public function createButton($key, $xpath)
    {
        return $this->createElementOfType('button', $key, $xpath);
    }

    /**
     * Creates checkbox element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Checkbox element
     */
    public function createCheckbox($key, $xpath)
    {
        return $this->createElementOfType('checkbox', $key, $xpath);
    }

    /**
     * Creates link element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Link element
     */
    public function createLink($key, $xpath)
    {
        return $this->createElementOfType('link', $key, $xpath);
    }

    /**
     * Creates page element
     *
     * @param string $key Key
     * @param string $xpath XPath query
     * @return \DOMElement Page element
     */
    public function createPageElement($key, $xpath)
    {
        return $this->createElementOfType('element', $key, $xpath);
    }
}
