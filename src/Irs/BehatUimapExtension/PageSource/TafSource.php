<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension\PageSource;

use Irs\BehatUimapExtension\Page;
use Zend\Cache\Storage\StorageInterface;
use Symfony\Component\Yaml\Yaml;

class TafSource implements PageSourceInterface, CachingInterface
{
    /**
     * Map regexp -> Page
     *
     * @var array<URL regexp => \Irs\BehatUimapExtension\Page>
     */
    private $pages;

    private $strictMode;

    /**
     * Cache storage
     *
     * @var StorageInterface
     */
    private $cache;

    /**
     * Pathes of dir that contain uimap files
     *
     * @var array
     */
    private $paths = array();

    public function __construct($path, $strictMode = true)
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('Cannot create page source with empty path');
        }

        $this->paths = is_array($path) ? $path : array($path);
        $this->strictMode = $strictMode;

        // check is readable
        foreach ($this->paths as $path) {
            if (!is_dir($path) && !is_file($path) || !is_readable($path)) {
                throw new \InvalidArgumentException("Resource '$path' is not readable .");
            }
        }
    }

    /**
     * Sets cache to page source
     *
     * @see \Irs\BehatUimapExtension\PageSource\CachingInterface::setCache()
     */
    public function setCache(StorageInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Returns UI map page by URL
     *
     * @param string $url URL
     * @return \Irs\BehatUimapExtension\Page
     * @throws \OutOfRangeException If page for URL was not found
     * @throws \InvalidArgumentException On YAML file parsing errors; in strict mode
     * 								     is thrown on bad YAML structure.
     */
    public function getPageByUrl($url)
    {
        foreach ($this->getPages() as $page) {
            if ($page->isPageCorrespondsToUrl($url)) {
                return $page;
            }
        }

        throw new \OutOfRangeException("Page not found by URL '$url'.");
    }

    /**
     * Returns UI map page by key
     *
     * @param string $key Key
     * @return \Irs\BehatUimapExtension\Page
     * @throws \OutOfRangeException If page for URL was not found
     * @throws \InvalidArgumentException On YAML file parsing errors; in strict mode
     * 								     is thrown on bad YAML structure.
     */
    public function getPageByKey($key)
    {
        foreach ($this->getPages() as $page) {
            if ($page->getKey() == $key) {
                return $page;
            }
        }

        throw new \OutOfRangeException("Page with key '$key' is not found.");
    }

    /**
     * @return array<\Irs\BehatUimapExtension\Page>
     * @throws \InvalidArgumentException from sfYaml parser
     */
    protected function getPages()
    {
        if (!$this->pages) {
            $this->pages = array();
            foreach ($this->paths as $prefix => $path) {
                $prefix = is_string($prefix) ? $prefix : '';
                if (!is_array($path)) {
                    $this->addPagesFromPath($path, $prefix);
                } else {
                    foreach ($path as $p) {
                        $this->addPagesFromPath($p, $prefix);
                    }
                }
            }
        }

        return $this->pages;
    }

    /**
     * Adds pages from path
     *
     * @param string $path Path
     * @param string $prefix URL prefix (Page URL = this prefix + mca)
     */
    protected function addPagesFromPath($path, $prefix)
    {
        if (is_file($path) && is_readable($path) && 'yml' == pathinfo($path, PATHINFO_EXTENSION)) {
            $this->addPagesFromFile($path, $prefix);
        } else if (is_dir($path)) {
            $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach ($items as $item) {
                if ($item->isFile() && $item->isReadable() && 'yml' == $item->getExtension()) {
                    $this->addPagesFromFile($item->getPathname(), $prefix);
                }
            }
        }
    }

    /**
     * Tries to get file content from cache
     *
     * @param string $fileName
     * @return false|array Content or false
     */
    protected function loadFromCache($fileName)
    {
        if ($this->cache && $item = $this->cache->getItem($this->getCacheId($fileName))) {
            list ($modTime, $content) = unserialize($item);
            $fileModTime = $this->getMtime($fileName);

            if ($modTime == $fileModTime) {
                return $content;
            } else {
                $this->cache->removeItem($this->getCacheId($fileName));
            }
        }

        return false;
    }

    /**
     * Adds page to cache storage
     *
     * @param string $fileName File name
     * @param mixed $content File content
     */
    protected function saveToCache($fileName, $content)
    {
        if (!$this->cache) {
            return;
        }
        $this->cache->addItem(
            $this->getCacheId($fileName),
            serialize(array($this->getMtime($fileName), $content))
        );
    }

    /**
     * Calculates and returns cache ID by file name
     *
     * @param string $fileName File name
     * @return string Cache ID
     */
    private function getCacheId($fileName)
    {
        return md5(realpath($fileName));
    }

    /**
     * Returns file modification time
     *
     * @param string $fileName File name
     * @throws \InvalidArgumentException If file does not exist
     * @throws \RuntimeException If Cannot retrieve file modification time
     * @return integer Unix timestamp
     */
    private function getMtime($fileName)
    {
        if (!file_exists($fileName)) {
            throw new \InvalidArgumentException("File '$fileName' doesn't exist.");
        }
        $info = stat($fileName);
        if (!isset($info['mtime'])) {
            throw new \RuntimeException("Cannot figure out modification time for '$fileName'.");
        }

        return $info['mtime'];
    }

    /**
     * Parse file and add to pages array
     *
     * @param string $fileName File name
     * @param string $prefix URL prefix (Page URL = this prefix + mca)
     */
    protected function addPagesFromFile($fileName, $prefix)
    {
        $pages = $this->loadFromCache($fileName) ?: array();

        if (!$pages) {
            $content = Yaml::parse(file_get_contents($fileName));
            try {
                foreach ($content as $key => $description) {
                    $page = $this->convertArrayToPage($key, $description);
                    $page->setUrl($prefix . $page->getUrl());
                    $pages[$key] = $page;
                }
                $this->saveToCache($fileName, $pages);
            } catch (\LogicException $e) {
                throw new \InvalidArgumentException($e->getMessage() . " ($fileName)", null, $e);
            }
        }

        $this->pages = array_merge($this->pages, $pages);
    }

    /**
     * Converts array with page description to \Irs\BehatUimapExtension\Page
     *
     * @param string $key Page's key
     * @param array $pageArray Structure of page
     */
    protected function convertArrayToPage($key, array $pageArray)
    {
        $page = new Page;
        $page->setKey($key);

        if (isset($pageArray['mca'])) {
            $page->setUrl($pageArray['mca']);
        }
        if (isset($pageArray['title'])) {
            $page->setTitle($pageArray['title']);
        }
        if (isset($pageArray['uimap'])) {
            foreach ($pageArray['uimap'] as $type => $descriprion) {
                if (!is_array($descriprion)) {
                    continue;
                }
                $this->appendChild($page, $page->documentElement, $type, $descriprion);
            }
        }

        return $page;
    }

    protected function appendChild(Page $page, \DOMElement $parent, $type, array $childrenDescription)
    {
        if (!in_array($parent->tagName, array('page', 'fieldset', 'tab'))) {
            throw new \InvalidArgumentException("Adding <$type> to <$parent->tagName> is not allowed.");
        }

        foreach ($childrenDescription as $name => $description) {
            if (null === $description) {
                continue; // Because some elements xpath empty with flag TODO
            }
            switch ($type) {
                case 'form':
                    $this->appendChild($page, $parent, $name, $description);
                    continue 2; // go to next iteration of foreach because form doesn't create element

                case 'tabs':
                    if (is_integer($name)) {
                        // so it's wrapped with stupid array
                        $this->appendChild($page, $parent, 'tabs', $description);
                        continue 2;  // go to next iteration of foreach
                    } else {
                        $child = $page->createTab($name, ''); // XPath is empty because tabs have
                                                              // click XPath, but not domain.
                        if (isset($description['xpath'])) {
                            // Adding button for selecting tab
                            $parent->appendChild($page->createButton("tab_$name", $description['xpath']));
                        }
                        foreach ($description as $subType => $subDescription) {
                            if (is_array($subDescription)) {
                                $this->appendChild($page, $child, $subType, $subDescription);
                            }
                        }
                    }
                    break;

                case 'fieldsets':
                    if (is_integer($name)) {
                        // so it's wrapped with stupid array
                        $this->appendChild($page, $parent, 'fieldsets', $description);
                        continue 2;  // go to next iteration of foreach
                    } else {
                        $child = $page->createFieldset($name, isset($description['xpath']) ? $description['xpath'] : '');
                        if (!is_array($description)) {
                            continue 2;  // go to next iteration of foreach
                        }
                        foreach ($description as $subType => $subDescription) {
                            if (is_array($subDescription)) {
                                $this->appendChild($page, $child, $subType, $subDescription);
                            }
                        }
                    }
                    break;

                case 'fields':
                    $child = $page->createField($name, $description);
                    break;

                case 'dropdowns':
                case 'multiselects':
                    $child = $page->createSelect($name, $description);
                    break;

                case 'buttons':
                    $child = $page->createButton($name, $description);
                    break;

                case 'checkboxes':
                case 'radiobuttons':
                    $child = $page->createCheckbox($name, $description);
                    break;

                case 'pageelements':
                    $child = $page->createPageElement($name, $description);
                    break;

                case 'links':
                    $child = $page->createLink($name, $description);
                    break;

                case 'messages':
                    continue 2; // go to next iteration of foreach

                case 'required':
                    continue 2; // go to next iteration of foreach

                default:
                    if ($this->strictMode) {
                        throw new \DomainException("'$type' element is not supported.");
                    } else {
                        continue 2;
                    }
            }

            $parent->appendChild($child);
        }
    }
}
