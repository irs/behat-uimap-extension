<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension\Context;

use Irs\BehatUimapExtension\PageSource\PageSourceInterface;
use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;

/**
 * Context initializer for UimapContext
 *
 */
class UimapContextInitializer implements InitializerInterface
{
    /**
     * Page source
     *
     * @var PageSourceInterface
     */
    private $pageSource;

    /**
     * Page source for injection into context
     *
     * @param PageSourceInterface $pageSource
     */
    public function __construct(PageSourceInterface $pageSource)
    {
        $this->pageSource = $pageSource;
    }

    /**
     * Checks if initializer supports provided context.
     *
     * @param ContextInterface $context
     * @return Boolean
     */
    public function supports(ContextInterface $context)
    {
        $reflection = new \ReflectionObject($context);
        if (!method_exists($reflection, 'getTraitNames')) {
            return false;
        }

        $traits = $reflection->getTraitNames();
        if (null === $traits) {
            throw new \RuntimeException('Error on retrieving traits from context.');
        }

        return in_array('Irs\BehatUimapExtension\Context\UimapContext', $traits);
    }

    /**
     * Sets page source into context
     *
     * @param ContextInterface $context
     */
    public function initialize(ContextInterface $context)
    {
        $context->setPageSource($this->pageSource);
    }
}
