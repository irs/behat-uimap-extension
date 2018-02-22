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

class UimapSelectorTest extends \PHPUnit_Framework_TestCase
{
    protected function _getSourceMock()
    {
        // mocking page source
        return $this->getMock(
            'Irs\BehatUimapExtension\PageSource\PageSourceInterface',
            array('getPageByKey', 'getPageByUrl')
        );
    }

    public function testShouldImplementSelectorInterface()
    {
        $this->assertInstanceOf('Behat\Mink\Selector\SelectorInterface', new UimapSelector($this->_getSourceMock()));
    }

    public function testShouldAcceptPageSourceAsContructorsArgument()
    {
        new UimapSelector($this->_getSourceMock());
    }

    /**
     * @dataProvider providerCorrectLocator
     */
    public function testShouldTranslateLocatorToXpathUsingPageFromSource(Locator $locator)
    {
        $this->markTestSkipped('Skipped due to mocker bug');
        
        // prepare
        $xpath = rand();

        $page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->getMock();
        $page->expects($this->once())
            ->method('getXpath')
            ->with($this->equalTo($locator))
            ->will($this->returnValue($xpath));

        $source = $this->_getSourceMock();

        if ($locator->hasPageKey()) {
            $source->expects($this->once())
                ->method('getPageByKey')
                ->with($this->equalTo($locator->getPageKey()))
                ->will($this->returnValue($page));
        } else {
            $source->expects($this->once())
                ->method('getPageByUrl')
                ->with($this->equalTo($locator->getPageUrl()))
                ->will($this->returnValue($page));
        }

        $selector = new UimapSelector($source);

        // act & assert
        $this->assertEquals($xpath, $selector->translateToXPath($locator));
    }

    /**
     * @expectedException InvalidArgumentException
     * @dataProvider providerIncorrectLocator
     */
    public function testTranslateToXpathShouldThrowInvalidArgumentExceptionOnNonLocatorArguments($locator)
    {
        // prepare
        $source = $this->_getSourceMock();
        $source->expects($this->never())
            ->method('getPageByUrl');

        $selector = new UimapSelector($source);

        // act
        $selector->translateToXPath($locator);
    }

    public function providerCorrectLocator()
    {
        return array(
            array(new Locator('asdas', 'dawdas')),
            array(new Locator('asdqw', 'dqwoe', null, '498dsf9')),
            array(new Locator('fjioqw', 'dmq', 'fqoiuNHI')),
            array(new Locator('QPCIVJ', 'DF[PQ', null, null, 25)),
            array(new Locator('aassssSQWQQ', 'sd8s')),
            array(new Locator('sdsdf', 'rertert', 'vdsf', null, null, array('asdqw'), 'asdqwoi')),
            array(new Locator(null, 'rertert', 'vdsf', null, null, array('asdqw', 'asqw'), 'asdqwoi')),
        );
    }

    public function providerIncorrectLocator()
    {
        return array(
            array('kjqweijoisdjfoijsdf'),
            array(156849),
            array(-18994),
            array(-18.56),
            array(48.45657),
            array('OiIJD'),
            array(new \stdClass()),
            array(array('asd')),
            array(array()),
        );
    }
}


