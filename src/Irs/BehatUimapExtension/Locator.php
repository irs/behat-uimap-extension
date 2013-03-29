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
 * Locator
 *
 * Tuple that consits of page URL, key, type, fieldset, tab and parameters.
 * Is used to locate elements in page.
 */
class Locator
{
    private $pageKey;
    private $pageUrl;
    private $key;
    private $type;
    private $fieldset;
    private $tab;
    private $parameters;

    public function __construct($pageUrl = null, $key = null, $type = null, $fieldset = null, $tab = null, array $parameters = array(), $pageKey = null)
    {
        $this->pageKey = $pageKey;
        $this->pageUrl = $pageUrl;
        $this->key = $key;
        $this->type = $type;
        $this->fieldset = $fieldset;
        $this->tab = $tab;
        $this->parameters = $parameters;
    }

    public function getPageUrl()
    {
        return $this->pageUrl;
    }

    public function getPageKey()
    {
        return $this->pageKey;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFieldset()
    {
        return $this->fieldset;
    }

    public function getTab()
    {
        return $this->tab;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function hasPageUrl()
    {
        return $this->pageUrl !== null;
    }

    public function hasPageKey()
    {
        return $this->pageKey !== null;
    }

    public function hasKey()
    {
        return $this->key !== null;
    }

    public function hasType()
    {
        return $this->type !== null;
    }

    public function hasFieldset()
    {
        return $this->fieldset !== null;
    }

    public function hasTab()
    {
        return $this->tab !== null;
    }

    public function hasParameters()
    {
        return (bool)$this->parameters;
    }

    /**
     * Returns string representation of locator
     *
     * @return string
     */
    public function __toString()
    {
        $s = '';
        if ($this->pageKey) {
            $s .= "page_key:$this->pageKey;";
        }
        if ($this->pageUrl) {
            $s .= "page_url:$this->pageUrl;";
        }
        if ($this->key) {
            $s .= "key:$this->key;";
        }
        if ($this->type) {
            $s .= "type:$this->type;";
        }
        if ($this->fieldset) {
            $s .= "fieldset:$this->fieldset;";
        }
        if ($this->tab) {
            $s .= "tab:$this->tab;";
        }
        if ($this->parameters) {
            $p = '';
            foreach ($this->parameters as $key => $value) {
                $p .= "$key:$value;";
            }
            $s .= 'parameters:<' . rtrim($p, ';') . '>;';
        }

        return '<' . rtrim($s, ';') . '>';
    }
}
