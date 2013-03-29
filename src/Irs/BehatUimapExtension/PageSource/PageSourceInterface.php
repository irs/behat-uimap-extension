<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension\PageSource;

/**
 * Interface of page source
 *
 */
interface PageSourceInterface
{
    /**
     * Returns UI map page by URL
     *
     * @param string $url URL
     * @return \Irs\BehatUimapExtension\Page
     * @throws \OutOfRangeException If page for URL was not found
     * @throws \InvalidArgumentException On parsing errors
     */
    public function getPageByUrl($url);

    /**
     * Returns UI map page by URL
     *
     * @param string $key Page key
     * @return \Irs\BehatUimapExtension\Page
     * @throws \OutOfRangeException If page for URL was not found
     * @throws \InvalidArgumentException On parsing errors
     */
    public function getPageByKey($key);
}
