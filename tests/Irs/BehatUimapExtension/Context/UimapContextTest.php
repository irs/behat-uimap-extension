<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension\Context;

use Irs\BehatUimapExtension\Locator;
use Irs\BehatUimapExtension\UimapSelector;
use Irs\BehatUimapExtension\PageSource\TafSource as TafPageSource;

use Behat\Mink\Session;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Mink;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Selector\SelectorsHandler;

use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStream;

class UimapContextTest extends \PHPUnit_Framework_TestCase
{
    protected function _createContextWithDriver(DriverInterface $driver)
    {
        $uimapYaml = new vfsStreamFile('page.yml');
        $uimapYaml->setContent($this->_uimapYamlContent);
        $vfs = vfsStream::setup('vfs');
        $vfs->addChild($uimapYaml);

        $tafPageSource = new TafPageSource(vfsStream::url('vfs/page.yml'));
        $uimapSelector = new UimapSelector($tafPageSource);
        $selectorsHandler = new SelectorsHandler(array('uimap' => $uimapSelector));

        $sessionName = 'mocked';
        $mink = new Mink(array($sessionName => new Session($driver, $selectorsHandler)));
        $mink->setDefaultSessionName($sessionName);

        $context = $this->getObjectForTrait('Irs\BehatUimapExtension\Context\UimapContext');
        $context->setMink($mink);
        $context->setPageSource($tafPageSource);
        $context->loadPage('category_page_before_reindex');

        return $context;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getDriverMock()
    {
        return $this->getMock('Behat\Mink\Driver\DriverInterface');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getElementMock()
    {
        return $this->getMockBuilder('Behat\Mink\Element\NodeElement')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function _prepareContextForSelectOption($expectedValue, $expectedAdditional, $expectedXpath)
    {
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('selectOption')
            ->with($this->equalTo($expectedValue), $this->equalTo($expectedAdditional));

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('find')
            ->with($this->equalTo($expectedXpath))
            ->will($this->returnValue(array($element)));

        // act
        return $this->_createContextWithDriver($driver);
    }

    public function testAdditionallySelectOption()
    {
        // prepare
        $value = 'sdfsdf';
        $context = $this->_prepareContextForSelectOption(
            $value,
            true,
            "//html//div[@class='category-products']//dt[./label[contains(text(),'T')]]/following-sibling::dd[1]//select[@multiple='multiple']"
        );

        // act
        $context->additionallySelectOption(
            'custom_option_multiselect',
            $value,
            'category_view',
            null,
            new TableNode([1 => ['title', 'T']])
        );
    }

    public function testAdditionallySelectOptionInFiedsetWithParams()
    {
        // prepare
        $value = 'sdqpwegv';
        $context = $this->_prepareContextForSelectOption(
            $value,
            true,
            "//html//div[@class='category-products']//dt[./label[contains(text(),'R')]]/following-sibling::dd[1]//select[@multiple='multiple']"
        );

        // act
        $context->additionallySelectOptionInFiedsetWithParams(
            'custom_option_multiselect',
            $value,
            'category_view',
            new TableNode([1 => ['title', 'R']])
        );
    }

    public function testAdditionallySelectOptionWithParams()
    {
        // prepare
        $value = 'sdqpwegv';
        $context = $this->_prepareContextForSelectOption(
            $value,
            true,
            "//html//div[@class='category-products']//dt[./label[contains(text(),'F')]]/following-sibling::dd[1]//select[@multiple='multiple']"
        );

        // act
        $context->additionallySelectOptionWithParams(
            'custom_option_multiselect',
            $value,
            new TableNode([1 => ['title', 'F']])
        );
    }

    protected function _prepareContextForAttachFileToField($expectedPath, $expectedXpath)
    {
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('attachFile')
            ->with($this->equalTo($expectedPath));

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('find')
            ->with($this->equalTo($expectedXpath))
            ->will($this->returnValue(array($element)));

        return $this->_createContextWithDriver($driver);
    }

    public function testAttachFileToField()
    {
        // prepare
        $context = $this->_prepareContextForAttachFileToField(
            $path = 'passps',
            "//html//div[@class='block block-subscribe']//input[@id='newsletter_S']"
        );

        // act
        $context->attachFileToField(
            'sign_up_newsletter_param',
            $path,
            'newsletter',
            null,
            new TableNode([1 => ['suffix', 'S']])
        );
    }


    public function testAttachFileToFieldWithRequiredAttributesOnly()
    {
        // prepare
        $context = $this->_prepareContextForAttachFileToField(
            $path = 'passps',
            "//html//div[@class='block block-subscribe']//input[@id='newsletter']"
        );

        // act
        $context->attachFileToField('sign_up_newsletter', $path);
    }

    public function testAttachFileToFieldInFieldsetWithParams()
    {
        // prepare
        $context = $this->_prepareContextForAttachFileToField(
            $path = '564s6ee',
            "//html//div[@class='block block-subscribe']//input[@id='newsletter_WE']"
        );

        // act
        $context->attachFileToFieldInFieldsetWithParams(
            'sign_up_newsletter_param',
            $path,
            'newsletter',
            new TableNode([1 => ['suffix', 'WE']])
        );
    }

    public function testAttachFileToFieldWithParams()
    {
        // prepare
        $context = $this->_prepareContextForAttachFileToField(
            $path = 'ropwoee',
            "//html//div[@class='block block-subscribe']//input[@id='newsletter_QWE']"
        );

        // act
        $context->attachFileToFieldWithParams(
            'sign_up_newsletter_param',
            $path,
            new TableNode([1 => ['suffix', 'QWE']])
        );
    }

    protected function _prepareContextForCheckOption($expectedXpath)
    {
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('click');

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('find')
            ->with($this->equalTo($expectedXpath))
            ->will($this->returnValue(array($element)));

        return $this->_createContextWithDriver($driver);
    }

    public function testCheckOption()
    {
        // preapre
        $context = $this->_prepareContextForCheckOption("//html//div[@class='page-title']//input[@id='EYQ_open']");

        // act
        $context->checkOption('open_title', 'category_title', null, new TableNode([['part', 'EYQ']]));
    }

    public function testCheckOptionInFiedsetWithParams()
    {
        // prepare
        $context = $this->_prepareContextForCheckOption("//html//div[@class='page-title']//input[@id='RRQ_open']");

        // act
        $context->checkOptionInFiedsetWithParams('open_title', 'category_title', new TableNode([['part', 'RRQ']]));
    }

    public function testCheckOptionWithParams()
    {
        // prepare
        $context = $this->_prepareContextForCheckOption("//html//div[@class='page-title']//input[@id='POW_open']");

        // act
        $context->checkOptionWithParams('open_title', new TableNode([['part', 'POW']]));
    }

    protected function _prepareContextForClickLink($expectedXpath)
    {
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('click');

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('find')
            ->with($this->equalTo($expectedXpath))
            ->will($this->returnValue(array($element)));

        return $this->_createContextWithDriver($driver);
    }

    public function testClickLink()
    {
        // prepare
        $context = $this->_prepareContextForClickLink(
            "//html//div[@class='category-products']//li[h2/a='AKWQPIO']//a[@class='link-learn']"
        );

        // act
        $context->clickLink('learn_more', 'category_view', null, new TableNode([['productName', 'AKWQPIO']]));
    }

    public function testClickLinkInFiedsetWithParams()
    {
        // prepare
        $context = $this->_prepareContextForClickLink(
            "//html//div[@class[contains(.,'block-compare')] and */ol[@id='compare-items']]//a[text()='Clear All TTQUW']"
        );

        // act
        $context->clickLinkInFiedsetWithParams('compare_clear_all', 'compare_products', new TableNode([['type', 'TTQUW']]));
    }

    public function testClickLinkWithParams()
    {
        // prepare
        $context = $this->_prepareContextForClickLink(
            "//html//div[@class='category-products']//li[h2/a='asdwqqw98']//a[@class='link-compare']"
        );

        // act
        $context->clickLinkWithParams('add_to_compare', new TableNode([['productName', 'asdwqqw98']]));
    }

    protected function _prepareContextForFillField($expectedValue, $expectedXpath)
    {
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('setValue')
            ->with($this->equalTo($expectedValue));

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('find')
            ->with($this->equalTo($expectedXpath))
            ->will($this->returnValue(array($element)));

        return $this->_createContextWithDriver($driver);
    }

    public function testFillField()
    {
        // prepare
        $context = $this->_prepareContextForFillField(
            $value = 'PQJKEUJ',
            $xpath = "//html//div[@class='block block-subscribe']//input[@id='newsletter_YQTF']"
        );

        // act
        $context->fillField('sign_up_newsletter_param', $value, 'newsletter', null, new TableNode([['suffix', 'YQTF']]));
    }

    public function testFillFieldInFiedsetWithParams()
    {
        // prepare
        $context = $this->_prepareContextForFillField(
            $value = 'PQssdJKEUJ',
            $xpath = "//html//div[@class='block block-subscribe']//input[@id='newsletter_YwwwQTF']"
        );

        // act
        $context->fillFieldInFiedsetWithParams('sign_up_newsletter_param', $value, 'newsletter', new TableNode([['suffix', 'YwwwQTF']]));
    }

    public function testFillFieldWithParams()
    {
        // prepare
        $context = $this->_prepareContextForFillField(
            $value = 'OIAJSOI',
            $xpath = "//html//div[@class='block block-subscribe']//input[@id='newsletter_YQTF']"
        );

        // act
        $context->fillFieldWithParams('sign_up_newsletter_param', $value, new TableNode([['suffix', 'YQTF']]));
    }

    public function testFillFields()
    {
        // prepare
        $context = $this->_prepareContextForFillField(
            $value = 'PaasQJawiop',
            $xpath = "//html//div[@class='block block-subscribe']//input[@id='newsletter']"
        );

        // act
        $context->fillFields(new TableNode([["sign_up_newsletter", $value]]));
    }

    public function testFillFieldsIntoFieldset()
    {
        // prepare
        $context = $this->_prepareContextForFillField(
            $value = 'PQJawiop',
            $xpath = "//html//div[@class='block block-subscribe']//input[@id='newsletter']"
        );

        // act
        $context->fillFieldsIntoFieldset('newsletter', new TableNode([["sign_up_newsletter", $value]]));
    }

    public function testFillFieldsIntoFieldsetOfTab()
    {
        // prepare
        $context = $this->_prepareContextForFillField(
            $value = 'PQJKEasdUJ',
            $xpath = "//html//div[@class='block block-subscribe']//input[@id='newsletter']"
        );

        // act
        $context->fillFieldsIntoFieldsetOfTab('newsletter', null, new TableNode([["sign_up_newsletter", $value]]));
    }

    protected function _prepareContextForPressButton($expectedXpath)
    {
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('press');

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('find')
            ->with($this->equalTo($expectedXpath))
            ->will($this->returnValue(array($element)));

        return $this->_createContextWithDriver($driver);
    }

    public function testPressButton()
    {
        // prepare
        $context = $this->_prepareContextForPressButton(
            "//html//div[@class='block block-subscribe']//button[span='Subscribe for QWOPK']"
        );

        // act
        $context->pressButton('subscribe', 'newsletter', null, new TableNode([['subscription', 'QWOPK']]));
    }

    public function testPressButtonInFiedsetWithParams()
    {
        // prepare
        $context = $this->_prepareContextForPressButton(
            "//html//div[@class='block block-subscribe']//button[span='Subscribe for aoqw']"
        );

        // act
        $context->pressButtonInFiedsetWithParams('subscribe', 'newsletter', new TableNode([['subscription', 'aoqw']]));
    }

    public function testPressButtonWithParams()
    {
        // prepare
        $context = $this->_prepareContextForPressButton(
            "//html//div[@class='block block-subscribe']//button[span='Subscribe for aoqw']"
        );

        // act
        $context->pressButtonWithParams('subscribe', new TableNode([['subscription', 'aoqw']]));
    }

    public function testSelectOption()
    {
        // prepare
        $context = $this->_prepareContextForSelectOption(
            $value = 'POmjpomjsd',
            false,
            "//html//div[@class='category-products']//dt[./label[contains(text(),'TQUWYTG')]]/following-sibling::dd[1]//select[@multiple='multiple']"
        );

        // act
        $context->selectOption('custom_option_multiselect', $value, 'category_view', null, new TableNode([['title', 'TQUWYTG']]));
    }

    public function testSelectOptionInFiedsetWithParams()
    {
        // prepare
        $context = $this->_prepareContextForSelectOption(
            $value = 'Plwaoksd',
            false,
            "//html//div[@class='category-products']//dt[./label[contains(text(),'T25WYTG')]]/following-sibling::dd[1]//select[@multiple='multiple']"
        );

        // act
        $context->selectOptionInFiedsetWithParams('custom_option_multiselect', $value, 'category_view', new TableNode([['title', 'T25WYTG']]));
    }

    public function testSelectOptionWithParams()
    {
        // prepare
        $context = $this->_prepareContextForSelectOption(
            $value = '1234oksd',
            false,
            "//html//div[@class='category-products']//dt[./label[contains(text(),'TQ74YTG')]]/following-sibling::dd[1]//select[@multiple='multiple']"
        );

        // act
        $context->selectOptionWithParams('custom_option_multiselect', $value, new TableNode([['title', 'TQ74YTG']]));
    }

    protected function _prepareContextForUncheckOption($expectedXpath)
    {
        $element = $this->_getElementMock();
        $element->expects($this->once())
            ->method('uncheck');

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('find')
            ->with($this->equalTo($expectedXpath))
            ->will($this->returnValue(array($element)));

        return $this->_createContextWithDriver($driver);
    }

    public function testUncheckOption()
    {
        // preapre
        $context = $this->_prepareContextForUncheckOption("//html//div[@class='page-title']//input[@id='EYQ_open']");

        // act
        $context->uncheckOption('open_title', 'category_title', null, new TableNode([['part', 'EYQ']]));
    }

    public function testUncheckOptionInFiedsetWithParams()
    {
        // prepare
        $context = $this->_prepareContextForUncheckOption("//html//div[@class='page-title']//input[@id='RRQ_open']");

        // act
        $context->uncheckOptionInFiedsetWithParams('open_title', 'category_title', new TableNode([['part', 'RRQ']]));
    }

    public function testUncheckOptionWithParams()
    {
        // prepare
        $context = $this->_prepareContextForUncheckOption("//html//div[@class='page-title']//input[@id='POW_open']");

        // act
        $context->uncheckOptionWithParams('open_title', new TableNode([['part', 'POW']]));
    }

    public function testVisit()
    {
        $pageUrl = "/catalog/category/view/s/sample/id/25/";

        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('visit')
            ->with($pageUrl);

        $context = $this->_createContextWithDriver($driver);

        // act
        $context->visit(
            'category_page_after_reindex',
            new TableNode([
                ['categoryUrl', 'sample'],
                ['id',          '25'    ]
            ])
        );
    }

    public function testWait()
    {
        // prepare
        $time = 4984789;
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('wait')
            ->with($time, 'false');
        $context = $this->_createContextWithDriver($driver);

        // act
        $context->wait($time);
    }

    public function testWaitForSeconds()
    {
        // prepare
        $time = 4984789;
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('wait')
            ->with($time * 1000, 'false');
        $context = $this->_createContextWithDriver($driver);

        // act
        $context->waitForSeconds($time);
    }

    public function testWaitForAjax()
    {
        // prepare
        $time = 4984789;
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('wait')
            ->with(
                $time,
                'window.Ajax ? !window.Ajax.activeRequestCount : (window.jQuery ? !window.jQuery.active : false)'
            );
        $context = $this->_createContextWithDriver($driver);

        // act
        $context->waitForAjax($time);
    }

    public function testWaitForAjaxUpToSeconds()
    {
        // prepare
        $time = 4984789;
        $driver = $this->_getDriverMock();
        $driver->expects($this->once())
            ->method('wait')
            ->with(
                $time * 1000,
                'window.Ajax ? !window.Ajax.activeRequestCount : (window.jQuery ? !window.jQuery.active : false)'
            );
        $context = $this->_createContextWithDriver($driver);

        // act
        $context->waitForAjaxUpToSeconds($time);
    }

    private $_uimapYamlContent = <<<YAML
#
# Magento
#
# NOTICE OF LICENSE
#
# This source file is subject to the Academic Free License (AFL 3.0)
# that is bundled with this package in the file LICENSE_AFL.txt.
# It is also available through the world-wide-web at this URL:
# http://opensource.org/licenses/afl-3.0.php
# If you did not receive a copy of the license and are unable to
# obtain it through the world-wide-web, please send an email
# to license@magentocommerce.com so we can send you a copy immediately.
#
# DISCLAIMER
#
# Do not edit or add to this file if you wish to upgrade Magento to newer
# versions in the future. If you wish to customize Magento for your
# needs please refer to http://www.magentocommerce.com for more information.
#
# @category    tests
# @package     selenium
# @subpackage  uimaps
# @author      Magento Core Team <core@magentocommerce.com>
# @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
# @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
#
---
#
category_page_before_reindex:
    mca: %categoryUrl%.html
    title: %categoryTitle%
    uimap: &categoryUIMap
        form: &categoryPage
            fieldsets:
                -
                    category_view:
                        xpath: //div[@class='category-products']
                        dropdowns:
                            show_per_page: //div[@class='toolbar']//div[@class='limiter']/select
                            sort_by: //div[@class='toolbar']//div[@class='sorter']//select
                        links:
                            list: //a[@class='list']
                            grid: //a[@class='grid']
                            next_page: //a[@title='Next']
                            learn_more: //li[h2/a='%productName%']//a[@class='link-learn']
                            add_to_wishlist: //li[h2/a='%productName%']//a[@class='link-wishlist']
                            add_to_compare: //li[h2/a='%productName%']//a[@class='link-compare']
                            reviews: //a[contains(text(),'Review(s)')]
                        multiselects:
                            custom_option_multiselect: //dt[./label[contains(text(),'%title%')]]/following-sibling::dd[1]//select[@multiple='multiple']
                        buttons:
                            add_to_cart: //li[h2/a='%productName%']//button[span='Add to Cart']
                        pageelements:
                            product_name_header: //div[@class='category-products']//a[text()='%productName%']
                            price_regular: //li[h2/a='%productName%']//span[@class='regular-price']/span[@class='price' and normalize-space(text())='%price%']
                            price_excluding_tax: //li[h2/a='%productName%']//span[@class='price-excluding-tax']/span[@class='price' and normalize-space(text())='%price%']
                            price_including_tax: //li[h2/a='%productName%']//span[@class='price-including-tax']/span[@class='price' and normalize-space(text())='%price%']
                            price_old: //li[h2/a='%productName%']//p[@class='old-price' and //span[normalize-space(text())='%price%']]
                            price_special: //li[h2/a='%productName%']//p[@class='special-price' and //span[normalize-space(text())='%price%']]
                            price_special_excluding_tax: //li[h2/a='%productName%']//p[@class='special-price']/span[@class='price-excluding-tax' and normalize-space(text())='%price%']
                            price_special_inlcuding_tax: //li[h2/a='%productName%']//p[@class='special-price']/span[@class='price-including-tax' and normalize-space(text())='%price%']
                            as_low_as: //li[h2/a='%productName%']//a[@class='minimal-price-link']/span[@class='price' and normalize-space(text())='%price%']
                            description: //li[h2/a='%productName%']//div[@class='desc std']
                            ex_price_regular: //li[h2/a='%productName%']//span[@class='regular-price']
                            ex_price_excluding_tax: //li[h2/a='%productName%']//span[@class='price-excluding-tax']/span[@class='price']
                            ex_price_including_tax: //li[h2/a='%productName%']//span[@class='price-including-tax']/span[@class='price']
                            ex_price_old: //li[h2/a='%productName%']//p[@class='old-price']
                            ex_price_special: //li[h2/a='%productName%']//p[@class='special-price']
                            ex_price_special_excluding_tax: //li[h2/a='%productName%']//p[@class='special-price']/span[@class='price-excluding-tax']
                            ex_price_special_inlcuding_tax: //li[h2/a='%productName%']//p[@class='special-price']/span[@class='price-including-tax']
                            ex_as_low_as: //li[h2/a='%productName%']//a[@class='minimal-price-link']/span[@class='price']
                -
                    category_title:
                        xpath: //div[@class='page-title']
                        checkboxes:
                            open_title: //input[@id='%part%_open']
                        pageelements:
                            product_tagged: //div[@class='page-title']/h1[contains(.,'%tagName%')]
                -
                    compare_products:
                        xpath: //div[@class[contains(.,'block-compare')] and */ol[@id='compare-items']]
                        links:
                          compare_clear_all: //a[text()='Clear All %type%']
                          compare_delete_product: //li[p='%productName%']/a
                          compare_product_link: //ol[@id='compare-items']//a[text()='%productName%']
                        buttons:
                          compare: //button[@title='Compare']
                        pageelements:
                          compare_block_empty: //p[text()='You have no items to compare.']
                          compare_block_title: //div[@class='block-title' and contains(. /strong/span, 'Compare Products')]
                -
                    my_cart:
                        xpath: //div[@class[contains(.,'block-cart')]]
                -
                    recently_viewed:
                        xpath: //div[@class[contains(.,'block-viewed')]]
                -
                    community_poll:
                        xpath: //div[@class='block block-poll']
                        pageelements:
                          poll_title: //p[@class='block-subtitle' and contains(text(),'%pollTitle%')]
                        buttons:
                          vote: //button[span='Vote']
                        radiobuttons:
                            vote: //li[span/label='%answer%']/input
                -
                    newsletter:
                        xpath: //div[@class='block block-subscribe']
                        buttons:
                            subscribe: //button[span='Subscribe for %subscription%']
                        fields:
                            sign_up_newsletter: //input[@id='newsletter']
                            sign_up_newsletter_param: //input[@id='newsletter_%suffix%']
                -
                    popular_tags:
                        xpath: //div[@class[contains(.,'block block-tags')]]
                        links:
                            tag_name: //ul[@class='tags-list']/li/a[text()='%tagName%']
                            view_all_tags: //a[text()='View All Tags']
        messages: &categoryPageMessages
            newsletter_invalid_email: //div[@class='validation-advice' and text()='Please enter a valid email address. For example johndoe@domain.com.']
            newsletter_reqired_field: //*[@id='advice-required-entry-newsletter']
            newsletter_success_subscription: //li[normalize-space(@class)='success-msg' and contains(.,'Thank you for your subscription.')]
            newsletter_email_used: "//li[normalize-space(@class)='error-msg' and contains(.,'There was a problem with the subscription: This email address is already assigned to another user.')]"
            newsletter_long_email: "//li[normalize-space(@class)='error-msg' and contains(.,'There was a problem with the subscription: Please enter a valid email address.')]"
            product_added_to_comparison: //li[normalize-space(@class)='success-msg' and contains(.//span,'The product %productName% has been added to comparison list.')]
            confirmation_for_removing_product_from_compare: Are you sure you would like to remove this item from the compare products?
            confirmation_clear_all_from_compare: Are you sure you would like to remove all products from your comparison?
            compare_list_cleared: //li[normalize-space(@class)='success-msg' and contains(.//span,'The comparison list was cleared.')]
            product_removed_from_comparison: //li[normalize-space(@class)='success-msg' and contains(.//span,'The product %productName% has been removed from comparison list.')]

category_page_after_reindex:
    mca: catalog/category/view/s/%categoryUrl%/id/%id%/
    title: %categoryTitle%
    uimap: *categoryUIMap

sub_category_page_before_reindex:
    mca: %rotCategoryUrl%/%categoryUrl%.html
    title: %categoryTitle%
    uimap: *categoryUIMap

category_page_index:
    mca: %categoryUrl%.html%param%
    title: %categoryTitle%
    uimap:
        form: *categoryPage
        messages: *categoryPageMessages
YAML;
}
