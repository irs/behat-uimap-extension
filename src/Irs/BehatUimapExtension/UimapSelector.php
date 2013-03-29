<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension;

use Irs\BehatUimapExtension\Locator;
use Irs\BehatUimapExtension\PageSource\PageSourceInterface;
use Behat\Mink\Selector\SelectorInterface;

/**
 * UI map selector
 *
 * Translates locator to XPath query usign UI maps.
 */
class UimapSelector implements SelectorInterface
{
    /**
     * Page source
     *
     * @var PageSourceInterface
     */
    private $pageSource;

    /**
     * Creates selector
     *
     * @param PageSourceInterface $pageSource Page source
     */
    public function __construct(PageSourceInterface $pageSource)
    {
        $this->pageSource = $pageSource;
    }

    /**
     * Translates locator to XPath expression
     *
     * @param Irs\BehatUimapExtension\Locator $locator
     * @throws \InvalidArgumentException On incorrect locator type
     * @return string XPath expression
     */
    public function translateToXPath($locator)
    {
        if (!$locator instanceof Locator) {
            throw new \InvalidArgumentException(
                'Irs\BehatUimapExtension\UimapSelector accepts only Irs\BehatUimapExtension\Locator.'
            );
        }

        $page = ($locator->hasPageKey())
            ? $this->pageSource->getPageByKey($locator->getPageKey($locator->getParameters()))
            : $this->pageSource->getPageByUrl($locator->getPageUrl($locator->getParameters()));

        return $page->getXpath($locator);
    }
}
