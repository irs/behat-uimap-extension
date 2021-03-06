<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

class UimapContextInitializerTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementInitializerInterface()
    {
        $this->assertInstanceOf(
            ContextInitializer::class,
            new UimapContextInitializer($this->pageSourceMock())
        );
    }

    public function testShouldSupportContextWithHooksTrait()
    {
        $initializer = new UimapContextInitializer($this->pageSourceMock());

        $this->assertTrue($initializer->supports(new SupportedContext));
    }

    public function testShouldNotSupportContextWithoutHooksTrait()
    {
        $initializer = new UimapContextInitializer($this->pageSourceMock());

        $this->assertFalse($initializer->supports(new NotSupportedContext));
    }

    public function testShouldInitializeMagentoHooks()
    {
        // preapre
        $pageSource = $this->pageSourceMock();
        $initializer = new UimapContextInitializer($pageSource);

        $context = $this->getMock(Context::class, array('setPageSource'));
        $context->expects($this->once())
            ->method('setPageSource')
            ->with($pageSource);

        // act
        $initializer->initializeContext($context);
    }

    protected function pageSourceMock()
    {
        return $this->getMock(
            'Irs\BehatUimapExtension\PageSource\PageSourceInterface',
            array('getPageByUrl', 'getPageByKey')
        );
    }
}

class SupportedContext implements Context
{
    use UimapContext;
}

class NotSupportedContext implements Context
{}
