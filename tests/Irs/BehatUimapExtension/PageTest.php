<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension;

class PageTest extends \PHPUnit_Framework_TestCase
{
    private $_correctPageXml = array(
        array(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<page xmlns="urn:magento:uimap-page"
    key="catalog-product-edit" title='Catalog product edit page' url='admin/catalog_product/edit/id/(\d+)'>
    <tab key="general" xpath="//div=[@id='product_info_tabs_group_7_content']">
        <field key="name" xpath="//input[@id='name']"/>
        <field key="description" xpath="//input[@id='description']"/>
        <select key="status" xpath="//input[@id='status']"/>
        <select key="status_id" xpath="//input[@id='%id%']"/>
    </tab>
    <button key="save" xpath="//div[@class='content-buttons-placeholder']//button[contains(@class,'save')]"/>
    <button key="back" xpath="//div[@class='content-buttons-placeholder']//button[contains(@class,'back')]"/>
    <button key="back_to" xpath="//div[@class='content-buttons-placeholder']//button[contains(@class,'%back%')]"/>
</page>
XML
        ),
        array(
<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<page xmlns="urn:magento:uimap-page"
    key="customer-edit"
    title="Cutomer edit page"
    url="admin/customer/edit/id/(\d+)">

    <tab key="account-information" xpath="//div[@id='_accountbase_fieldset']">
        <fieldset key="account-information" xpath="//div[@id='_accountbase_fieldset']">
            <field key="firstname" xpath="//input[@id='_accountfirstname']"/>
            <field key="lastname" xpath="//input[@id='_accountlastname']"/>
            <select key="gender" xpath="//input[@id='_accountgender']"/>
        </fieldset>
    </tab>
    <fieldset key="form-buttons" xpath="//p[@class='form-buttons']">
        <button key="save" xpath="//div[@class='content-buttons-placeholder']//button[contains(@class,'save')]"/>
        <button key="back" xpath="//div[@class='content-buttons-placeholder']//button[contains(@class,'back')]"/>
    </fieldset>
</page>
XML
        ),
    );

    protected function _getCorrectPageXml($index)
    {
        if (isset($this->_correctPageXml[$index])) {
            return current($this->_correctPageXml[$index]);
        }
    }

    public function testShouldImplementSerializeableInterface()
    {
        $this->assertInstanceOf('Serializable', new Page);
    }

    /**
     * @dataProvider providerCorrectPage
     * @expectedException OutOfRangeException
     */
    public function testSerializationShouldBeInversible(Page $page)
    {
        $processedPage = unserialize(serialize($page));

        $this->assertEquals($page, $processedPage);
        $this->assertTrue($processedPage->isValid());
        $processedPage->getXpath(new Locator('id', 'field'));
    }

    public function testEmptyPageShouldBeValid()
    {
        $page = new Page;
        $this->assertTrue($page->isValid());
    }

    public function testShouldExtendDomDocument()
    {
        $page = new Page;
        $this->assertInstanceOf('DomDocument', $page);
    }

    /**
     * @dataProvider providerIncorrectPageXml
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionOnIncorrectXmlInConstructor($incorrectPageXml)
    {
        new Page($incorrectPageXml);
    }

    public function testShouldCreateValidPageWitoutArgumentsInConstructor()
    {
        $page = new Page;
        $this->assertTrue($page->isValid());
    }

    /**
     * @dataProvider providerCorrectPage
     */
    public function testisValidShouldReturnTrueForCorrectPages(Page $correctPage)
    {
        $this->assertTrue($correctPage->isValid());
    }

    /**
     * @dataProvider providerIncorrectPage
     */
    public function testisValidShouldReturnFalseForIncorrectPages(Page $incorrectPage)
    {
        $this->assertFalse($incorrectPage->isValid());
    }

    /**
     * @dataProvider providerCorrectPageXml
     */
    public function testOnCorrectXmlShouldCreatePageWithoutExceptions($correctPageXml)
    {
        new Page($correctPageXml);
    }

    /**
     * @dataProvider providerCorrectLocator
     */
    public function testGetXpathMethodShouldReturnXpathForCorrectLocator(Locator $locator, $expectedXpath)
    {
        $page = new Page($this->_getCorrectPageXml(0));
        $this->assertEquals($expectedXpath, $page->getXpath($locator));
    }

    /**
     * @dataProvider providerIncorrectLocator
     * @expectedException OutOfRangeException
     */
    public function testGetXpathMethodShouldReturnThrowPutOfRangeExceptionForIncorrectLocator(Locator $locator)
    {
        $page = new Page($this->_getCorrectPageXml(1));
        $page->getXpath($locator);
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreateTabShouldReturnTabDomElementWithKeyAnXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('tab', $key, $xpath, $page->createTab($key, $xpath));
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreateFieldsetShouldReturnFieldsetDomElementWithKeyAndXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('fieldset', $key, $xpath, $page->createFieldset($key, $xpath));
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreateFieldShouldReturnFieldDomElementWithKeyAndXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('field', $key, $xpath, $page->createField($key, $xpath));
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreateSelectShouldReturnSelectDomElementWithKeyAndXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('select', $key, $xpath, $page->createSelect($key, $xpath));
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreateButtonShouldReturnButtonDomElementWithKeyAndXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('button', $key, $xpath, $page->createButton($key, $xpath));
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreateCheckboxShouldReturnCheckboxDomElementWithKeyAndXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('checkbox', $key, $xpath, $page->createCheckbox($key, $xpath));
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreatePageElementShouldReturnElementDomElementWithKeyAndXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('element', $key, $xpath, $page->createPageElement($key, $xpath));
    }

    /**
     * @dataProvider providerKeyXpath
     */
    public function testCreateLinkShouldReturnLinkDomElementWithKeyAndXpathAttributes($key, $xpath)
    {
        $page = new Page();
        $this->_assertElement('link', $key, $xpath, $page->createLink($key, $xpath));
    }

    protected function _assertElement($expectedTagName, $expectedKey, $expectedXpath, \DOMElement $actual)
    {
        $this->assertEquals($expectedTagName, $actual->tagName, "Element's tag name should be '$expectedTagName'.");
        $this->assertTrue($actual->hasAttribute('key'), 'Element should have key attribute.');
        $this->assertTrue($actual->hasAttribute('xpath'), 'Element should have xpath attribute.');
        $this->assertEquals($expectedKey, $actual->getAttribute('key'), "Key attribute should be equal to '$expectedKey'.");
        $this->assertEquals($expectedXpath, $actual->getAttribute('xpath'), "Xpath attribute sohuld be equal to '$expectedXpath'.");
    }

    public function testSetAndGetUrlShouldProduceValidPage()
    {
        $page = new Page();
        $url = 'asddfgjknhkajsdbsdnsbdifbs';
        $page->setUrl($url);

        $this->assertEquals($url, $page->getUrl());
        $this->assertTrue($page->isValid(), "Page should be valid but following error occured:\n" . libxml_get_last_error()->message);
    }

    public function testSetAndGetTitleShouldProduceValidPage()
    {
        $page = new Page();
        $title = 'asdjkASDFSnsbdifbs';
        $page->setTitle($title);

        $this->assertEquals($title, $page->getTitle());
        $this->assertTrue($page->isValid());
    }

    public function testSetAndGetKeyShouldProduceValidPage()
    {
        $page = new Page();
        $key = 'asdjknhskajsdbSSFDnsbdifbs';
        $page->setKey($key);

        $this->assertEquals($key, $page->getKey());
        $this->assertTrue($page->isValid());
    }

    public function testGetUrlShouldAcceptArrayOfParametersForSubstitusion()
    {
        // prepare
        $page = new Page;
        $page->setUrl('http://host/admin/user/%name%/type/%type%/id/%id%');
        $params = array('name' => 'oasidqwe', 'type' => 'dumb', 'id' => 546984);

        // act & assert
        $this->assertEquals('http://host/admin/user/oasidqwe/type/dumb/id/546984', $page->getUrl($params));
    }

    public function testGetUrlShouldNotDoSubstitutionWithEmptyParameters()
    {
        // prepare
        $page = new Page;
        $page->setUrl($url = 'http://host/admin/user/%name%/type/%type%/id/%id%');

        // act & assert
        $this->assertEquals($url, $page->getUrl());
    }

    /**
     * @dataProvider providerUrlCorrespondsToPage
     */
    public function testIsPageCorrespondsToUrlShouldReturnTrueForUrlsCorrespondsToPage($pageUrl, $realUrl)
    {
        $page = new Page;
        $page->setUrl($pageUrl);

        $this->assertTrue($page->isPageCorrespondsToUrl($realUrl));
    }

    /**
     * @dataProvider providerUrlNotCorrespondsToPage
     */
    public function testIsPageCorrespondsToUrlShouldReturnFalseForUrlsDoesNotCorrespondToPage($pageUrl, $realUrl)
    {
        $page = new Page;
        $page->setUrl($pageUrl);

        $this->assertFalse($page->isPageCorrespondsToUrl($realUrl));

    }

    public function providerUrlCorrespondsToPage()
    {
        return array(
            array('catalog_product/new/set/%setId%/type/%productType%/', 'http://magento-1.11.0.1.local/index.php/admin/catalog_product/new/set/4/type/simple/key/0a7509c5cd813114f278465fc7d0c729b5ab76391ef97470cb448985c5befe72/'),
            array('catalog_product/new/%setId%/type/%productType%/', 'http://magento-1.11.0.1.local/index.php/admin/catalog_product/new/set/4/type/configurable/key/0a7509c5cd813114f278465fc7d0c729b5ab76391ef97470cb448985c5befe72/'),
            array('catalog_product/edit/store/%storeId%/id/%id%/', 'http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/store/0/id/5/key/e365cb6c7def89c9350167a8b4f29ed770216c2d766fa35c205426e3c0987f0a/'),
            array('catalog_category/', 'http://magento-1.11.0.1.local/index.php/admin/catalog_category/index/key/f29dc85d55d21356f3031a7823682da565a3625d8143726d81f94f0cbc14738b/'),
        );
    }

    public function providerUrlNotCorrespondsToPage()
    {
        return array(
            array('catalog_product/new/set/%setId%/type/%productType%/', 'http://magento-1.11.0.1.local/index.php/admin/customer/index/key/cef0d2d077b3c8c4f57ac483f6d3e16988ef966bdae1433ade11828a733e1a10/'),
            array('catalog_product/new/attributes/%attributesUrl%/set/%setId%/type/%productType%/', 'http://magento-1.11.0.1.local/index.php/admin/customer/edit/id/1/key/3577644a34c42ec329931ea9f1216133d2fb99a525e6dd40e52c98183e1461b5/'),
            array('catalog_product/new/attributes/%attributesUrl%/set/%setId%/type/%productType%/', 'http://magento-1.11.0.1.local/index.php/admin/catalog_product/new/set/4/type/configurable/key/0a7509c5cd813114f278465fc7d0c729b5ab76391ef97470cb448985c5befe72/'),
            array('catalog_product/edit/store/%storeId%/id/%id%/', 'http://magento-1.11.0.1.local/index.php/admin/customer/edit/id/1/key/3577644a34c42ec329931ea9f1216133d2fb99a525e6dd40e52c98183e1461b5/'),
            array('catalog_product/', 'http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/1/key/a542e1d4d739dfd20f4d71092e3b3c1a41047c4bf5b7b601ac9bd304b6b5c5d2/'),
        );
    }

    public function providerKeyXpath()
    {
        return array(
            array('sadasda', 'sdfgfhrt'),
            array('sdiowej', '4as5465w'),
            array('asd8748w9da', 'sqw89fhrt'),
            array('грфшывda', '4987qwert'),
            array('asdlkjlwqIUHIAuhn', 'asdOIijkmnas'),
        );
    }

    public function providerCorrectPage()
    {
        $page1 = new Page();
        $page1->setKey('catalog-product-edit');
        $page1->setTitle('Catalog product edit page');
        $page1->setUrl('admin/catalog_product/edit/id/(\d+)');

        $tabGeneral = $page1->createTab('general', "//div=[@id='product_info_tabs_group_7_content']");
        $tabGeneral->appendChild($page1->createField('name', "//input[@id='name']"));
        $tabGeneral->appendChild($page1->createField('description', "//input[@id='description']"));
        $tabGeneral->appendChild($page1->createSelect('status', "//input[@id='status']"));
        $page1->documentElement->appendChild($tabGeneral);
        $page1->documentElement->appendChild($page1->createButton('save', "//div[@class='content-buttons-placeholder']//button[contains(@class,'save')]"));
        $page1->documentElement->appendChild($page1->createButton('back', "//div[@class='content-buttons-placeholder']//button[contains(@class,'back')]"));

        $page2 = new Page();
        $page2->setKey('customer-edit');
        $page2->setTitle('Cutomer edit page');
        $page2->setUrl('admin/customer/edit/id/(\d+)');

        $fieldsetAccountInformation = $page2->createFieldset('account-information', "//div[@id='_accountbase_fieldset']");
        $fieldsetAccountInformation->appendChild($page2->createField('firstname', "//input[@id='_accountfirstname']"));
        $fieldsetAccountInformation->appendChild($page2->createField('lastname', "//input[@id='_accountlastname']"));
        $fieldsetAccountInformation->appendChild($page2->createSelect('gender', "//input[@id='_accountgender']"));

        $fieldsetFormButtons = $page2->createFieldset('form-buttons', "//p[@class='form-buttons']");
        $fieldsetFormButtons->appendChild($page2->createButton('save', "//div[@class='content-buttons-placeholder']//button[contains(@class,'save')]"));
        $fieldsetFormButtons->appendChild($page2->createButton('back', "//div[@class='content-buttons-placeholder']//button[contains(@class,'back')]"));

        $tabAccountInformation = $page2->createTab('account-information', "//div[@id='_accountbase_fieldset']");
        $tabAccountInformation->appendChild($fieldsetAccountInformation);

        $page2->documentElement->appendChild($tabAccountInformation);
        $page2->documentElement->appendChild($fieldsetFormButtons);

        return array(array($page1), array($page2));
    }

    public function providerIncorrectPage()
    {
        $pages = array();

        // Tab is not allowed in next cases
        foreach (array('fieldset', 'field', 'element', 'link', 'checkbox', 'button', 'select') as $tagName) {
            $pageKey = "Tab inside $tagName";
            $pages[$pageKey] = new Page();

            $creator = 'create' . ucfirst($tagName);
            $parent = $pages[$pageKey]->$creator('asdasdasd', 'dfgeoirpjpqiwj');
            $parent->appendChild($pages[$pageKey]->createTab('asdkjnewqkdn', 'asklqwmn'));
            $pages[$pageKey]->documentElement->appendChild($parent);
        }

        // Fieldset is not allowed in next cases
        foreach (array('field', 'element', 'link', 'checkbox', 'button', 'select') as $tagName) {
            $pageKey = "Fieldset inside $tagName";
            $pages[$pageKey] = new Page();

            $creator = 'create' . ucfirst($tagName);
            $parent = $pages[$pageKey]->$creator('asdasdasd', 'dfgeoirpjpqiwj');
            $parent->appendChild($pages[$pageKey]->createTab('asdkjnewqkdn', 'asklqwmn'));
            $pages[$pageKey]->documentElement->appendChild($parent);
        }

        // Elements should not have children
        foreach (array('field', 'element', 'link', 'checkbox', 'button', 'select') as $parentTagName) {
            foreach (array('field', 'element', 'link', 'checkbox', 'button', 'select') as $childTagName) {
                $pageKey = "$parentTagName inside $childTagName";
                $pages[$pageKey] = new Page();

                $parentCreator = 'create' . ucfirst($parentTagName);
                $childCreator = 'create' . ucfirst($childTagName);
                $parent = $pages[$pageKey]->$parentCreator('asdasdasd', 'dfgeoirpjpqiwj');
                $parent->appendChild($pages[$pageKey]->$childCreator('asdkjnewqkdn', 'asklqwmn'));
                $pages[$pageKey]->documentElement->appendChild($parent);
            }
        }

        $wrappedPages = array();
        foreach ($pages as $key => $page) {
            $wrappedPages[$key] = array($page);
        }

        return $wrappedPages;
    }

    /**
     * For _correctPageXml[1]
     */
    public function providerCorrectLocator()
    {
        return array(
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'name', 'field', null, 'general'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='name']"
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'name', 'field', null, 'general'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='name']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'name', 'field'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='name']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'name'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='name']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'status', 'select', null, 'general'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='status']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'status', null, null, 'general'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='status']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'status'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='status']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'save'),
                "//div[@class='content-buttons-placeholder']//button[contains(@class,'save')]",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'back', 'button'),
                "//div[@class='content-buttons-placeholder']//button[contains(@class,'back')]",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'status_id', 'select', null, 'general', array('id' => 'five')),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='five']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'status_id', 'select', null, 'general'),
                "//div=[@id='product_info_tabs_group_7_content']//input[@id='%id%']",
            ),
            array(
                new Locator('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/5/', 'back_to', 'button', null, null, array('back' => 'black')),
                "//div[@class='content-buttons-placeholder']//button[contains(@class,'black')]",
            ),
        );
    }

    /**
     * For _correctPageXml[0]
     */
    public function providerIncorrectLocator()
    {
        return array(
            array(new Locator('asdasd', null, null, null, 'general')),
            array(new Locator('gthtyut', 'save', null, 'account-information')),
            array(new Locator(';lsdweopk', 'back', 'button', 'account-information', 'general')),
            array(new Locator('sdpoqwij','gender', 'select', 'account-information', 'general')),
        );
    }

    public function providerCorrectPageXml()
    {
        return $this->_correctPageXml;
    }

    public function providerIncorrectPageXml()
    {
        return array(
            array("<?xml version='1.0' encoding='utf-8'?><page>asdasdgea/</ss>"), // not well formed XML
            array("asdjjdwqew"),  // not well formed XML
            array("<olls></olls>"), // not well formed XML
            array("<?xml version='1.0' encoding='utf-8'?><olls></olls>"), // incorrect root element
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' title='asdasdasd'><field key='asda' xpath='b/a'/></page>"),  // page without key
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf' title='asdasdasd'><field key='asda' xpath='b/a'/></page>"),  // page without URL
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><field key='asdasd'/></page>"),
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><field key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></field></page>"),                  // tab can't be inside field
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><fieldset key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></fieldset></page>"),            // tab can't be inside fieldset
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><link key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></link></page>"),                    // tab can't be inside links
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><select key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></select></page>"),                // tab can't be inside selects
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><button key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></button></page>"),                // tab can't be inside buttons
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><radiobutton key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></radiobutton></page>"),      // tab can't be inside radiobuttons
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><item key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></item></page>"),                    // tab can't be inside items
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><checkbox key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></checkbox></page>"),            // tab can't be inside checkboxs
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><tab key='asdasd' xpath='a/b/c'><tab key='ertre' xpath='df/gowkl'></tab></page>"),                      // tab can't be inside checkboxs
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><field key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></field></page>"),             // fieldset can't be inside field
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><fieldset key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></fieldset></page>"),       // fieldset can't be inside fieldset
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><link key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></link></page>"),               // fieldset can't be inside links
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><select key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></select></page>"),           // fieldset can't be inside selects
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><button key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></button></page>"),           // fieldset can't be inside buttons
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><radiobutton key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></radiobutton></page>"), // tab can't be inside radiobuttons
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><item key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></item></page>"),               // fieldset can't be inside items
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><checkbox key='asdasd' xpath='a/b/c'><fieldset key='ertre' xpath='df/gowkl'></checkbox></page>"),       // fieldset can't be inside checkboxs
            array("<?xml version='1.0' encoding='utf-8'?><page xmlns='urn:magento:uimap-page' key='sdfsdf'  title='Olaosdsad' url='asdhj/asdasd'><checkbox key='asdasd' xpath='a/b/c'><item key='ertre' xpath='df/gowkl'></checkbox></page>"),           // item can't be inside checkboxs
        );
    }
}
