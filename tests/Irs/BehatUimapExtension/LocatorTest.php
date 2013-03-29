<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension;

class LocatorTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultValuesForGettersShouldBeNull()
    {
        // prepare
        $locator = new Locator;

        // assert
        foreach (array('PageUrl', 'PageKey', 'Key', 'Type', 'Fieldset', 'Tab') as $property) {
            $getter = "get$property";
            $this->assertNull($locator->$getter(), "Default value for $property should be null.");
        }
    }

    public function testDefaultValueForParametersShouldBeEmptyArray()
    {
        // prepare
        $locator = new Locator;

        // assert
        $this->assertEquals(array(), $locator->getParameters());
    }

    /**
     * @dataProvider providerLocatorContructorsArguments
     */
    public function testGettersShouldReturnValuesPassedToConstructor($url, $key, $type, $fieldset, $tab, $parameters, $pageKey)
    {
        $locator = new Locator($url, $key, $type, $fieldset, $tab, $parameters, $pageKey);

        $this->assertEquals($url, $locator->getPageUrl());
        $this->assertEquals($key, $locator->getKey());
        $this->assertEquals($type, $locator->getType());
        $this->assertEquals($fieldset, $locator->getFieldset());
        $this->assertEquals($tab, $locator->getTab());
        $this->assertEquals($parameters, $locator->getParameters());
        $this->assertEquals($pageKey, $locator->getPageKey());
    }

    /**
     * @dataProvider providerLocatorContructorsArguments
     */
    public function testHasMethodsShouldReturnFalseForNullValuesAndTrueForNotNull($url, $key, $type, $fieldset, $tab, $parameters, $pageKey)
    {
        $locator = new Locator($url, $key, $type, $fieldset, $tab, $parameters, $pageKey);

        if (null === $url) {
            $this->assertFalse($locator->hasPageUrl());
        } else {
            $this->assertTrue($locator->hasPageUrl());
        }
        if (null === $key) {
            $this->assertFalse($locator->hasKey());
        } else {
            $this->assertTrue($locator->hasKey());
        }
        if (null === $type) {
            $this->assertFalse($locator->hasType());
        } else {
            $this->assertTrue($locator->hasType());
        }
        if (null === $fieldset) {
            $this->assertFalse($locator->hasFieldset());
        } else {
            $this->assertTrue($locator->hasFieldset());
        }
        if (null === $tab) {
            $this->assertFalse($locator->hasTab());
        } else {
            $this->assertTrue($locator->hasTab());
        }
        if ($parameters) {
            $this->assertTrue($locator->hasParameters());
        } else {
            $this->assertFalse($locator->hasParameters());
        }
        if (null === $pageKey) {
            $this->assertFalse($locator->hasPageKey());
        } else {
            $this->assertTrue($locator->hasPageKey());
        }
    }

    public function providerLocatorContructorsArguments()
    {
        return array(
            array('sdfdsfsdfsdf', 'asdasd', 'asdasd', null, null, array('abc' => 'cde', 'asdasd'), 'saqwekd'),
            array(null, 'opwejnf', null, null, null, array(), 'awedasd'),
            array('aasdasd', 'kqwekl', 'asdasdq', 'sdfsdf', 'asdasdasd', array('askqoij'), null),
        );
    }

    public function providerLocatorContructorsArgumentsWithStringRepresentation()
    {
        return array(
            array(
                '<page_url:sdfdsfsdfsdf;key:asdasd;type:asdasd;parameters:<abc:cde;0:asdasd>>',
                'sdfdsfsdfsdf', 'asdasd', 'asdasd', null, null, array('abc' => 'cde', 'asdasd'), null
            ),
            array(
                '<key:opwejnf>',
                null, 'opwejnf', null, null, null, array(), null
            ),
            array(
                '<page_url:aasdasd;key:kqwekl;type:asdasdq;fieldset:sdfsdf;tab:asdasdasd;parameters:<keya:askqoij>>',
                'aasdasd', 'kqwekl', 'asdasdq', 'sdfsdf', 'asdasdasd', array('keya' => 'askqoij'), null
            ),
            array(
                '<page_key:ssdqwer;page_url:aasdasd;key:kqwekl;type:asdasdq;fieldset:sdfsdf;tab:asdasdasd;parameters:<keya:askqoij>>',
                'aasdasd', 'kqwekl', 'asdasdq', 'sdfsdf', 'asdasdasd', array('keya' => 'askqoij'), 'ssdqwer'
            ),
        );
    }

    /**
     * @dataProvider providerLocatorContructorsArgumentsWithStringRepresentation
     */
    public function testUimapLocatorShouldBeConvertibleToHumanReadableString($expected, $url, $key, $type, $fieldset, $tab, $parameters, $pageKey)
    {
        // prepare
        $locator = new Locator($url, $key, $type, $fieldset, $tab, $parameters, $pageKey);

        // act & assert
        $this->assertEquals($expected, (string)$locator);
    }
}
