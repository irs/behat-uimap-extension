<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension\PageSource;

use Zend\Cache\Storage\StorageInterface;

/**
 * Interface of page source that supports cacheing
 *
 */
interface CachingInterface
{
    /**
     * Sets cache storage
     *
     * @param StorageInterface $cache Cache storage
     */
    public function setCache(StorageInterface $cache);
}
