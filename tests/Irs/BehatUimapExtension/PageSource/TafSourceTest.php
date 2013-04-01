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
use Irs\BehatUimapExtension\Locator;
use Symfony\Component\Yaml\Yaml;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;

class TafTest extends \PHPUnit_Framework_TestCase
{
    protected function _setUpVfs()
    {
        $oneUimap = new vfsStreamFile('one.yml');
        $oneUimap->setContent($this->_oneUimapFileContent);
        $twoUimap = new vfsStreamFile('two.yml');
        $twoUimap->setContent($this->_twoUimapFileContent);

        $vfs = vfsStream::setup('uimaps');
        $vfs->addChild($oneUimap);
        $vfs->addChild($twoUimap);
    }

    public function testShouldAcceptSinglePathAsContructorsArgument()
    {
        $this->_setUpVfs();
        $source = new TafSource(vfsStream::url('uimaps/one.yml'));
        $page = $source->getPageByUrl('http://magento-1.11.0.1.local/index.php/admin/catalog_product/new/key/cfe78d7c0700e77b09a23b65780ed3b69c192286373151c315616a01de8ee9aa/');
        $this->assertEquals('new_product_settings', $page->getKey());
    }

    /**
     * @depends testShouldAcceptSinglePathAsContructorsArgument
     */
    public function testShouldImplementPageSourceInterface()
    {
        vfsStream::setup('uimaps');

        $this->assertInstanceOf('\Irs\BehatUimapExtension\PageSource\PageSourceInterface', new TafSource(vfsStream::url('uimaps')));
    }

    public function testShouldAcceptArrayOfPathesAsConstructorsArgument()
    {
        vfsStream::setup(
            'uimaps',
            null,
            array(
                'ullaaa' => array('alala.yml' => 'ALALA'),
                'lala' => array(),
            )
        );

        new TafSource(array(
            vfsStream::url('uimaps/ullaaa'),
            vfsStream::url('uimaps/lala'),
            vfsStream::url('uimaps/ullaaa/alala.yml'),
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIfIncorrectPathPassedToContructorGetPageShouldThrowInvalidArgumentException()
    {
        vfsStream::setup(
            'uimaps',
            null,
            array(
                'ullaaa' => array('alala.yml', 'Qwmasd.yml'),
                'lala' => array(),
            )
        );

        $source = new TafSource(array(vfsStream::url('/uimaps/kdkrt/eerps'), vfsStream::url('/uimaps/shprtwk')));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorShouldThrowOutOfRangeExceptionOnEmptyArgument()
    {
        new TafSource(array());
    }

    public function testGetPageByUrlMethodShouldSearchPageByPathFromConstructorAndReturnValidUimapPage()
    {
        // prepare
        $this->_setUpVfs();

        $source = new TafSource(vfsStream::url('uimaps'));

        // act
        $onePage = $source->getPageByUrl('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/1/key/a542e1d4d739dfd20f4d71092e3b3c1a41047c4bf5b7b601ac9bd304b6b5c5d2/');
        $twoPage = $source->getPageByUrl('http://magento-1.11.0.1.local/index.php/admin/catalog_category/index/key/f29dc85d55d21356f3031a7823682da565a3625d8143726d81f94f0cbc14738b/');
        $onePage->formatOutput = true;


        // assert
        $this->assertInstanceOf('\Irs\BehatUimapExtension\Page', $onePage);
        $this->assertInstanceOf('\Irs\BehatUimapExtension\Page', $twoPage);
        $this->assertTrue(
            $onePage->isValid(),
            'First page should be valid but: '
                . (libxml_get_last_error() ? libxml_get_last_error()->message . "\n" : '')
                . $onePage->saveXML()
        );
        $this->assertTrue(
            $twoPage->isValid(),
            'Second page should be valid but: '
                . (libxml_get_last_error() ? libxml_get_last_error()->message . "\n" : '')
                . "\n" .  $twoPage->saveXML()
        );
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testGetPageByUrlNotSupportedBySourceShouldThrowOutOfRangeException()
    {
        // prepare
        $this->_setUpVfs();
        $source = new TafSource(vfsStream::url('uimaps'));

        // act
        $source->getPageByUrl('skdjnjkdnsfjknsdf');
    }

    /**
     * @depends testGetPageByUrlMethodShouldSearchPageByPathFromConstructorAndReturnValidUimapPage
     */
    public function testGetPageByUrlShouldReturnCorrectlyConvetertedUimap()
    {

        // prepare
        $this->_setUpVfs();
        $source = new TafSource(vfsStream::url('uimaps'));

        // act
        $onePage = $source->getPageByUrl('http://magento-1.11.0.1.local/index.php/admin/catalog_product/edit/id/1/key/a542e1d4d739dfd20f4d71092e3b3c1a41047c4bf5b7b601ac9bd304b6b5c5d2/');
        $twoPage = $source->getPageByUrl('http://magento-1.11.0.1.local/index.php/admin/catalog_category/index/key/f29dc85d55d21356f3031a7823682da565a3625d8143726d81f94f0cbc14738b/');
        $onePage->formatOutput = true;
        $twoPage->formatOutput = true;

        // assert
        $xp = new \DOMXPath($onePage);
        $xp->registerNamespace('p', 'urn:magento:uimap-page');
        $generalTab = $xp->query("//p:tab[@key='general']");
        $this->assertEquals(1, $generalTab->length, "There is no tab general on first page.\n" . $onePage->saveXML());
        $generalTab = $generalTab->item(0);
        $this->assertEqualXMLStructure($this->_getOnePageTabGeneralExpectedElement(), $generalTab);
        $expectedTwoPage = new Page($this->_twoPageXml);
        $this->assertEqualXMLStructure($expectedTwoPage->documentElement, $twoPage->documentElement);
    }


    public function testGetPageByKeyMethodShouldSearchPageByPathFromConstructorAndReturnValidUimapPage()
    {
        // prepare
        $this->_setUpVfs();

        $source = new TafSource(vfsStream::url('uimaps'));

        // act
        $onePage = $source->getPageByKey('edit_product');
        $twoPage = $source->getPageByKey('manage_categories');
        $onePage->formatOutput = true;


        // assert
        $this->assertInstanceOf('\Irs\BehatUimapExtension\Page', $onePage);
        $this->assertInstanceOf('\Irs\BehatUimapExtension\Page', $twoPage);
        $this->assertTrue(
            $onePage->isValid(),
            'First page should be valid but: '
                . (libxml_get_last_error() ? libxml_get_last_error()->message . "\n" : '')
                . $onePage->saveXML()
        );
        $this->assertTrue(
            $twoPage->isValid(),
            'Second page should be valid but: '
                . (libxml_get_last_error() ? libxml_get_last_error()->message . "\n" : '')
                . "\n" .  $twoPage->saveXML()
        );
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testGetPageByKeyNotSupportedBySourceShouldThrowOutOfRangeException()
    {
        // prepare
        $this->_setUpVfs();
        $source = new TafSource(vfsStream::url('uimaps'));

        // act
        $source->getPageByKey('skdjnjkdnsfjknsdf');
    }

    /**
     * @depends testGetPageByUrlMethodShouldSearchPageByPathFromConstructorAndReturnValidUimapPage
     */
    public function testGetPageByKeyShouldReturnCorrectlyConvetertedUimap()
    {
        // prepare
        $this->_setUpVfs();
        $source = new TafSource(vfsStream::url('uimaps'));

        // act
        $onePage = $source->getPageByKey('edit_product');
        $twoPage = $source->getPageByKey('manage_categories');
        $onePage->formatOutput = true;
        $twoPage->formatOutput = true;

        // assert
        $xp = new \DOMXPath($onePage);
        $xp->registerNamespace('p', 'urn:magento:uimap-page');
        $generalTab = $xp->query("//p:tab[@key='general']");
        $this->assertEquals(1, $generalTab->length, "There is no tab general on first page.\n" . $onePage->saveXML());
        $generalTab = $generalTab->item(0);
        $this->assertEqualXMLStructure($this->_getOnePageTabGeneralExpectedElement(), $generalTab);
        $expectedTwoPage = new Page($this->_twoPageXml);
        $this->assertEqualXMLStructure($expectedTwoPage->documentElement, $twoPage->documentElement);
    }

    public function testAllTafUimapFilesSouldCreateValidPage()
    {
        $path = __DIR__ . '/taf_uimaps';
        $source = new TafSource($path);

        foreach ($this->_getTafUimapPages($path) as $key => $pageArray) {
            $page = $source->getPageByKey($key);
            $this->assertInstanceOf('\Irs\BehatUimapExtension\Page', $page);
            $this->assertTrue($page->isValid());
        }
    }

    /**
     * @return array<array>
     * @throws \InvalidArgumentException from sfYaml parser
     */
    protected function _getTafUimapPages($path)
    {
        $pages = array();
        if (is_dir($path)) {
            $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            foreach ($items as $item) {
                if ($item->isFile() && $item->isReadable() && 'yml' == $item->getExtension()) {
                    foreach (Yaml::parse($item->getPathname()) as $key => $value) {
                        $pages[$key] = $value;
                    }
                }
            }
        }

        return $pages;
    }

    public function testShouldAddPrefixToUrlByKeyFromPathArray()
    {
        $prefix = 'prefix';
        $this->_setUpVfs();
        $source = new TafSource(array($prefix => vfsStream::url('uimaps')));

        foreach (array('manage_products', 'new_product_settings', 'manage_categories') as $key) {
            $this->assertEquals(
                $prefix,
                substr($source->getPageByKey($key)->getUrl(), 0, strlen($prefix))
            );
        }
    }

    private $_oneUimapFileContent = <<<UIMAP
#
# Magento
#
# NOTICE OF LICENSE
#
---
# Manage Product page
manage_products:
    mca: catalog_product/
    click_xpath: //div[@class='nav-bar']//a[span='Manage Products']
    title: Manage Products / Catalog / Magento Admin
    uimap:
        form: &searchProductGrid
            fieldsets:
                -
                    product_grid:
                        xpath: //div[@id='productGrid']
                        buttons:
                            reset_filter: //button[span='Reset Filter']
                            search: //button[span='Search']
                            submit: //button[span='Submit']
                        dropdowns:
                            product_massaction: //select[@id='productGrid_massaction-select']
                            filter_massaction: //select[@name='massaction']
                            product_type: //select[@name='type']
                            product_attribute_set: //select[@name='set_name']
                            product_price_currency: //select[@name='price[currency]']
                            product_visibility: //select[@name='visibility']
                            product_status: //select[@name='status']
                            website: //select[@name='websites']
                        fields:
                            product_id_from: //input[@name='entity_id[from]]
                            product_id_to: //input[@name='entity_id[to]']
                            product_name: //input[@name='name']
                            product_sku: //input[@name='sku']
                            product_price_from: //input[@name='price[from]']
                            product_price_to: //input[@name='price[to]']
                            product_qty_from: //input[@name='qty[from]']
                            product_qty_to: //input[@name='qty[to]']
                        pageelements:
                            product_grid_head: //div[@id='productGrid']//thead/tr[@class='headings']
        buttons:
            add_new_product: //button[span='Add Product']
        messages: &productMesssages
            success_deleted_products_massaction: //li[normalize-space(@class)='success-msg' and contains(.,'Total of %qtyDeletedProducts% record(s) have been deleted.')]
            enter_greater_than_zero: "//div[@id='advice-validate-greater-than-zero-%fieldId%' and not(contains(@style,'display: none;'))]"
            success_deleted_product: //li[normalize-space(@class)='success-msg' and contains(.,'The product has been deleted.')]
            success_duplicated_product: //li[normalize-space(@class)='success-msg' and contains(.,'The product has been duplicated.')]
            success_created_product: //li[normalize-space(@class)='success-msg' and contains(.,'The product has been created.')]
            success_saved_product: //li[normalize-space(@class)='success-msg' and contains(.,'The product has been saved.')]
            specify_url: "//div[@id='advice-validate-downloadable-url-%fieldId%' and not(contains(@style,'display: none;'))]"
            incorrect_sku_length: //div[@class='validation-advice' and text()='SKU length should be 64 characters maximum.']
            empty_required_field: "//div[@id='advice-required-entry-%fieldId%' and not(contains(@style,'display: none;'))]"
            enter_valid_number: "//div[@id='advice-validate-number-%fieldId%' and not(contains(@style,'display: none;'))]"
            enter_zero_or_greater: "//div[@id='advice-validate-zero-or-greater-%fieldId%' and not(contains(@style,'display: none;'))]"
            existing_sku: //div[@class='validation-advice' and contains(text(),'The value of attribute "SKU" must be unique')]
            select_type_of_option: //div[@id='advice-required-option-select-product_option_%optionId%_type']
            confirmation_for_delete: Are you sure?

# Settings for New Product creation
new_product_settings:
    mca: catalog_product/new/
    title: New Product / Manage Products / Catalog / Magento Admin
    uimap:
        form: &newProductSettingsForm
            tabs:
                -
                    settings: &settingsTab
                        xpath: //a[@title='Settings']/span
                        fieldsets:
                            -
                                product_settings:
                                    xpath: //div[contains(div/div/h4,'Create Product Settings')]
                                    buttons:
                                        continue: //button[span='Continue']
                                    dropdowns:
                                        product_attribute_set: //select[@id='attribute_set_id']
                                        product_type: //select[@id='product_type']
# Add New Product page
new_product:
#    mca: catalog_product/new/%productParameters%
    mca: catalog_product/new/set/%setId%/type/%productType%/
    title: New Product / Manage Products / Catalog / Magento Admin
    uimap: &newProduct
        form: &newProductCreationForm
            tabs:
                -
                    configurable_product_settings: &configurableProductsettings
                        xpath: //a[@title='Configurable Product Settings']/span
                        fieldsets:
                            -
                                select_configurable_attributes:
                                    xpath: //div[contains(div/div/h4,'Select Configurable Attributes')]
                                    buttons:
                                        continue: //button[span='Continue']
                                    checkboxes:
                                        configurable_attribute_title: //input[@type='checkbox' and @title='%attributeTitle%']
                -
                    general: &generalTab
                        xpath: //a[@title='General']/span
                        fieldsets:
                            -
                                product_general:
                                    xpath: //div[contains(div/div/h4,'General')]
                                    buttons:
                                        create_new_attribute: //button[span='Create New Attribute']
                                    dropdowns:
                                        general_sku_type: //select[@id='sku_type']
                                        general_weight_type: //select[@id='weight_type']
                                        general_status: //select[@id='status']
                                        general_visibility: //select[@id='visibility']
                                        general_in_feed: //select[@id='is_imported']
                                        general_country_manufacture: //select[@id='country_of_manufacture']
                                        general_user_attr_dropdown: //select[@id='%attibuteCodeDropdown%']
                                    fields:
                                        general_name: //input[@id='name']
                                        general_description: //textarea[@id='description']
                                        general_short_description: //textarea[@id='short_description']
                                        general_sku: //input[@id='sku']
                                        general_weight: //input[@id='weight' and not(@disabled)]
                                        general_news_from: //input[@id='news_from_date']
                                        general_news_to: //input[@id='news_to_date']
                                        general_url_key: //input[@id='url_key']
                                        general_user_attr_field: //input[@id='%attibuteCodeField%']
                                    multiselects:
                                        general_user_attr_multiselect: //input[@id='%attibuteCodeMultiselect%']
                -
                    prices: &pricesTab
                        xpath: //a[@title='Prices']/span
                        fieldsets:
                            -
                                product_prices:
                                    xpath: //div[contains(div/div/h4,'Prices')]
                                    buttons:
                                        create_new_attribute: //button[span='Create New Attribute']
                                        add_tier_price: //button[span='Add Tier']
                                        delete_tier_price: //button[@id='tier_price_row_%tierPriceId%_delete_button']
                                    dropdowns:
                                        prices_price_type: //select[@id='price_type']
                                        prices_tax_class: //select[@id='tax_class_id']
                                        prices_enable_googlecheckout: //select[@id='enable_googlecheckout']
                                        prices_price_view_bundle: //select[@id='price_view']
                                        prices_apply_map: //select[@id='msrp_enabled']
                                        prices_display_actual_price: //select[@id='msrp_display_actual_price_type']
                                    fields:
                                        prices_price: //input[@id='price' and not(@disabled)]
                                        prices_special_price: //input[@id='special_price']
                                        prices_special_price_from: //input[@id='special_from_date']
                                        prices_special_price_to: //input[@id='special_to_date']
                                        prices_manu_suggested_retail_price: //input[@id='msrp']
                            -
                                tier_price_row:
                                    xpath: //tbody[@id='tier_price_container']/tr
                                    dropdowns:
                                        prices_tier_price_website: //select[@id='tier_price_row_%tierPriceId%_website']
                                        prices_tier_price_customer_group: //select[@id='tier_price_row_%tierPriceId%_cust_group']
                                    fields:
                                        prices_tier_price_qty: //input[@id='tier_price_row_%tierPriceId%_qty']
                                        prices_tier_price_price: //input[@id='tier_price_row_%tierPriceId%_price']
                -
                    meta_information: &metaInformationTab
                        xpath: //a[@title='Meta Information']/span
                        fieldsets:
                            -
                                product_meta_information:
                                    xpath: //div[contains(div/div/h4,'Meta Information')]
                                    buttons:
                                        create_new_attribute: //button[span='Create New Attribute']
                                    fields:
                                        meta_information_meta_title: //input[@id='meta_title']
                                        meta_information_meta_keywords: //textarea[@id='meta_keyword']
                                        meta_information_meta_description: //textarea[@id='meta_description']
                -
                    images: &imagesTab
                        xpath: //a[@title='Images']/span
                        fieldsets:
                            -
                                product_images:
                                    xpath: //div[contains(div/div/h4,'Images')]
                -
                    recurring_profile: &recurringProfileTab
                        xpath: //a[@title='Recurring Profile']/span
                        fieldsets:
                            -
                                product_recurring_profile:
                                    xpath: //div[contains(div/div/h4,'Recurring Profile')]
                                    buttons:
                                        create_new_attribute: //button[span='Create New Attribute']
                                    dropdowns:
                                        recurring_profile_enable_recurring_profile: //select[@id='is_recurring']
                                        recurring_profile_customer_define_start_date: //select[@id='recurring_profilestart_date_is_editable']
                                        recurring_profile_auto_bill_next_cycle: //select[@id='recurring_profilebill_failed_later']
                                        recurring_profile_billing_period_unit: //select[@id='recurring_profileperiod_unit']
                                        recurring_profile_trial_billing_period_unit: //select[@id='recurring_profiletrial_period_unit']
                                        recurring_profile_allow_initial_fee_failure: //select[@id='recurring_profileinit_may_fail']
                                    fields:
                                        recurring_profile_schedule_description: //input[@id='recurring_profileschedule_description']
                                        recurring_profile_max_payment_failures: //input[@id='recurring_profilesuspension_threshold']
                                        recurring_profile_billing_frequency: //input[@id='recurring_profileperiod_frequency']
                                        recurring_profile_max_billing_cycles: //input[@id='recurring_profileperiod_max_cycles']
                                        recurring_profile_trial_billing_frequency: //input[@id='recurring_profiletrial_period_frequency']
                                        recurring_profile_max_trial_billing_cycles: //input[@id='recurring_profiletrial_period_max_cycles']
                                        recurring_profile_trial_billing_amount: //input[@id='recurring_profiletrial_billing_amount']
                                        recurring_profile_initial_fee: //input[@id='recurring_profileinit_amount']
                -
                    design: &designTab
                        xpath: //a[@title='Design']/span
                        fieldsets:
                            -
                                product_design:
                                    xpath: //div[contains(div/div/h4,'Design')]
                                    buttons:
                                        create_new_attribute: //button[span='Create New Attribute']
                                    dropdowns:
                                        design_custom_design: //select[@id='custom_design']
                                        design_page_layout: //select[@id='page_layout']
                                        design_display_product_options_in: //select[@id='options_container']
                                    fields:
                                        design_active_from: //input[@id='custom_design_from']
                                        design_active_to: //input[@id='custom_design_to']
                                        design_custom_layout_update: //textarea[@id='custom_layout_update']
                -
                    gift_options: &giftOptionsTab
                        xpath: //a[@title='Gift Options']/span
                        fieldsets:
                            -
                                product_gift_options:
                                    xpath: //div[contains(div/div/h4,'Gift Options')]
                                    buttons:
                                        create_new_attribute: //button[span='Create New Attribute']
                                    checkboxes:
                                        gift_options_allow_gift_message_default: //input[@id='use_config_gift_message_available']
                                    dropdowns:
                                        gift_options_allow_gift_message: //select[@id='gift_message_available']
                -
                    inventory: &inventoryTab
                        xpath: //a[@title='Inventory']/span
                        fieldsets:
                            -
                                product_inventory:
                                    xpath: //div[contains(div/div/h4,'Inventory')]
                                    checkboxes:
                                        inventory_manage_stock_default: //input[@id='inventory_use_config_manage_stock']
                                        inventory_qty_for_items_out_of_stock_default: //input[@id='inventory_use_config_min_qty']
                                        inventory_min_allowed_qty_default: //input[@id='inventory_use_config_min_sale_qty']
                                        inventory_max_allowed_qty_default: //input[@id='inventory_use_config_max_sale_qty']
                                        inventory_backorders_default: //input[@id='inventory_use_config_backorders']
                                        inventory_notify_for_qty_below_default: //input[@id='inventory_use_config_notify_stock_qty']
                                        inventory_enable_qty_increments_default: //input[@id='inventory_use_config_enable_qty_increments']
                                        inventory_qty_increments_default: //input[@id='inventory_use_config_qty_increments']
                                    dropdowns:
                                        inventory_manage_stock: //select[@id='inventory_manage_stock']
                                        inventory_qty_uses_decimals: //select[@id='inventory_is_qty_decimal']
                                        inventory_backorders: //select[@id='inventory_backorders']
                                        inventory_enable_qty_increments: //select[@id='inventory_enable_qty_increments']
                                        inventory_stock_availability: //select[@id='inventory_stock_availability']
                                    fields:
                                        inventory_qty: //input[@id='inventory_qty']
                                        inventory_qty_for_items_out_of_stock: //input[@id='inventory_min_qty']
                                        inventory_min_allowed_qty: //input[@id='inventory_min_sale_qty']
                                        inventory_max_allowed_qty: //input[@id='inventory_max_sale_qty']
                                        inventory_notify_for_qty_below: //input[@id='inventory_notify_stock_qty']
                                        inventory_qty_increments: //input[@id='inventory_qty_increments' and not(@disabled)]
                -
                    websites: &websitesTab
                        xpath: //a[@title='Websites']/span
                        fieldsets:
                            -
                                product_websites:
                                    xpath: //div[contains(div/div/h4,'Product In Websites')]
                                    checkboxes:
                                        websites: //div[@class='website-name'][.//text()='%websiteName%']/input[@type='checkbox']
                -
                    categories: &categoriesTab
                        xpath: //a[@title='Categories']/span
                        fieldsets:
                            -
                                product_categories:
                                    xpath: //div[contains(div/div/h4,'Product Categories')]
                                    links:
                                        root_category: //ul/div/li[contains(div/a/span,'%rootName%')]
                                        sub_category: //*[@id='%parentCategoryId%']/ancestor::li/ul/li[contains(div/a/span,'%subName%') and not(div/a/span/@id='%parentCategoryId%')]
                                        expand_category: //*[@id='%parentCategoryId%']/ancestor::div/img[contains(@class,'x-tree-elbow-plus') or contains(@class,'x-tree-elbow-end-plus')]
                -
                    related: &relatedProductsTab
                        xpath: //a[@title='Related Products']/span
                        fieldsets:
                            -
                                related:
                                    xpath: //div[@id='related_product_grid']
                                    buttons:
                                        reset_filter: //button[span='Reset Filter']
                                        search: //button[span='Search']
                                    dropdowns:
                                        related_search_massaction: //select[@name='in_products']
                                        related_search_type: //select[@name='type']
                                        related_search_attribute_set: //select[@name='set_name']
                                        related_search_status: //select[@name='status']
                                        related_search_visibility: //select[@name='visibility']
                                    fields:
                                        related_search_id: //input[@name='entity_id']
                                        related_search_name: //input[@name='name']
                                        related_search_sku: //input[@name='sku']
                                        related_search_price_from: //input[@name='price[from]']
                                        related_search_price_to: //input[@name='price[to]']
                                        related_search_position_from: //input[@name='position[from]']
                                        related_search_position_to: //input[@name='position[to]']
                                        related_product_position: %productXpath%//input[@name='position']
                -
                    up_sells: &upSellsProductsTab
                        xpath: //a[@title='Up-sells']/span
                        fieldsets:
                            -
                                up_sells:
                                    xpath: //div[@id='up_sell_product_grid']
                                    buttons:
                                        reset_filter: //button[span='Reset Filter']
                                        search: //button[span='Search']
                                    dropdowns:
                                        up_sells_search_massaction: //select[@name='in_products']
                                        up_sells_search_type: //select[@name='type']
                                        up_sells_search_attribute_set: //select[@name='set_name']
                                        up_sells_search_status: //select[@name='status']
                                        up_sells_search_visibility: //select[@name='visibility']
                                    fields:
                                        up_sells_search_id: //input[@name='entity_id']
                                        up_sells_search_name: //input[@name='name']
                                        up_sells_search_sku: //input[@name='sku']
                                        up_sells_search_price_from: //input[@name='price[from]']
                                        up_sells_search_price_to: //input[@name='price[to]']
                                        up_sells_search_position_from: //input[@name='position[from]']
                                        up_sells_search_position_to: //input[@name='position[to]']
                                        up_sells_product_position: %productXpath%//input[@name='position']
                -
                    cross_sells: &crossSellsProductsTab
                        xpath: //a[@title='Cross-sells']/span
                        fieldsets:
                            -
                                cross_sells:
                                    xpath: //div[@id='cross_sell_product_grid']
                                    buttons:
                                        reset_filter: //button[span='Reset Filter']
                                        search: //button[span='Search']
                                    dropdowns:
                                        cross_sells_search_massaction: //select[@name='in_products']
                                        cross_sells_search_type: //select[@name='type']
                                        cross_sells_search_attribute_set: //select[@name='set_name']
                                        cross_sells_search_status: //select[@name='status']
                                        cross_sells_search_visibility: //select[@name='visibility']
                                    fields:
                                        cross_sells_search_id: //input[@name='entity_id']
                                        cross_sells_search_name: //input[@name='name']
                                        cross_sells_search_sku: //input[@name='sku']
                                        cross_sells_search_price_from: //input[@name='price[from]']
                                        cross_sells_search_price_to: //input[@name='price[to]']
                                        cross_sells_search_position_from: //input[@name='position[from]']
                                        cross_sells_search_position_to: //input[@name='position[to]']
                                        cross_sells_product_position: %productXpath%//input[@name='position']
                -
                    custom_options: &customOptionsTab
                        xpath: //a[@title='Custom Options']/span
                        fieldsets:
                            -
                                custom_options:
                                    xpath: //div[contains(div/div/h4,'Custom Options')]
                                    buttons:
                                        add_option: //button[span='Add New Option']
                            -
                                custom_option_set:
                                    xpath: //div[@class='option-box']
                                    buttons:
                                        delete_option: //div[@id='option_%optionId%']//button[span='Delete Option']
                                        add_row: //button[span='Add New Row']
                                        delete_row: //tr[@id='product_option_%optionId%_select_%rowId%']//button[span='Delete Row']
                                    dropdowns:
                                        custom_options_general_input_type: //select[@id='product_option_%optionId%_type']
                                        custom_options_general_is_required: //select[@id='product_option_%optionId%_is_require']
                                        custom_options_price_type: //select[@id='product_option_%optionId%_price_type' or @id='product_option_%optionId%_select_%rowId%_price_type']
                                    fields:
                                        custom_options_general_title: //input[@id='product_option_%optionId%_title']
                                        custom_options_general_sort_order: //input[@name='product[options][%optionId%][sort_order]']
                                        custom_options_title: //input[@id='product_option_%optionId%_select_%rowId%_title']
                                        custom_options_price: //input[@name='product[options][%optionId%][price]' or @id='product_option_%optionId%_select_%rowId%_price']
                                        custom_options_sku: //input[@name='product[options][%optionId%][sku]' or @name='product[options][%optionId%][values][%rowId%][sku]']
                                        custom_options_max_characters: //input[@name='product[options][%optionId%][max_characters]']
                                        custom_options_allowed_file_extension: //input[@name='product[options][%optionId%][file_extension]']
                                        custom_options_image_size_x: //input[@name='product[options][%optionId%][image_size_x]']
                                        custom_options_image_size_y: //input[@name='product[options][%optionId%][image_size_y]']
                                        custom_options_sort_order: //input[@name='product[options][%optionId%][values][%rowId%][sort_order]']
                -
                    bundle_items: &bundleItemsTab
                        xpath: //a[@title='Bundle Items']/span
                        fieldsets:
                            -
                                shipment:
                                    xpath: //div[contains(div/h4,'Shipment')]
                                    dropdowns:
                                        ship_bundle_items: //select[@id='shipment_type']
                            -
                                bundle_items:
                                    xpath: //div[contains(div/h4,'Bundle Items')]
                                    buttons:
                                        add_new_option: //button[@id='add_new_option']
                            -
                                new_bundle_option:
                                    xpath: //div[@id='bundle_option_%optionId%']
                                    buttons:
                                        delete_option: //button[span='Delete Option']
                                        add_selection: //button[span='Add Selection']
                                        selection_item_delete: //tr[@class="selection" and contains(.,'%productSku%')]//button[span='Delete']
                                    dropdowns:
                                        bundle_items_input_type: //select[contains(@name,'[type]')]
                                        bundle_items_is_required: //select[contains(@name,'required')]
                                        selection_item_price_type: //tr[@class="selection" and contains(.,'%productSku%')]//select[contains(@name,'selection_price_type')]
                                        selection_item_user_defined_qty: //tr[@class="selection" and contains(.,'%productSku%')]//select[contains(@name,'selection_can_change_qty')]
                                    fields:
                                        bundle_items_default_title: //input[contains(@name,'[title]')]
                                        bundle_items_position: //input[contains(@name,'position')]
                                        selection_item_price: //tr[@class="selection" and contains(.,'%productSku%')]//input[contains(@name,'selection_price_value')]
                                        selection_item_default_qty: //tr[@class="selection" and contains(.,'%productSku%')]//input[contains(@name,'selection_qty')]
                                        selection_item_position: //tr[@class="selection" and contains(.,'%productSku%')]//input[contains(@name,'[position]')]
                                    radiobuttons:
                                        selection_item_default: //tr[@class="selection" and contains(.,'%productSku%')]//input[contains(@name,'[is_default]')]
                            -
                                select_product_to_bundle_option:
                                    xpath: //div[@id='bundle_option_%optionId%']//div[contains(div/h4,'Please Select Products to Add')]
                                    buttons:
                                        add_selected_products: //button[span='Add Selected Product(s) to Option']
                                        reset_filter: //button[span='Reset Filter']
                                        search: //button[span='Search']
                                    dropdowns:
                                        bundle_items_search_massaction: //select[@name='is_selected']
                                        bundle_items_search_attribute_set: //select[@name='set_name']
                                    fields:
                                        bundle_items_search_id: //input[@name='id']
                                        bundle_items_search_name: //input[@name='name']
                                        bundle_items_search_sku: //input[@name='sku']
                                        bundle_items_search_price_from: //input[@name='price[from]']
                                        bundle_items_search_price_to: //input[@name='price[to]']
                                        bundle_items_qty_to_add: //input[@name='qty']
                -
                    associated: &associatedProductsTab
                        xpath: //a[@title='Associated Products']/span
                        fieldsets:
                            -
                                create_simple_assosiated_product:
                                    xpath: //div[contains(div/h4,'Create Simple Associated Product')]
                                    buttons:
                                        create_empty: //button[span='Create Empty']
                            -
                                super_products_attribute_configuration: &superProductsAttributeconfiguration
                                    xpath: //li[contains(div/text(),'%attributeTitle%')]
                                    checkboxes:
                                        associated_products_attribute_name_default: //input[@class='attribute-use-default-label']
                                    dropdowns:
                                        associated_product_price_type: //li[div/strong='%attributeValue%']//select[contains(@class,'price-type')]
                                    fields:
                                        associated_product_price: //li[div/strong='%attributeValue%']//input[contains(@class,'attribute-price')]
                                        associated_products_attribute_name: //input[contains(@class,'attribute-label')]
                            -
                                associated: &associatedProductsFieldSet
                                    xpath: //div[@id='super_product_grid' or @id='super_product_links']
                                    buttons:
                                        reset_filter: //button[span='Reset Filter']
                                        search: //button[span='Search']
                                    dropdowns:
                                        associated_search_massaction: //select[@name='in_products']
                                        associated_search_attribute_set: //select[@name='set_name']
                                        associated_search_inventory: //select[@name='is_saleable']
                                        associated_search_by_attribute_value: //select[@name='%attributeCode%']
                                    fields:
                                        associated_search_id: //input[@name='entity_id']
                                        associated_search_name: //input[@name='name']
                                        associated_search_sku: //input[@name='sku']
                                        associated_search_price_from: //input[@name='price[from]']
                                        associated_search_price_to: //input[@name='price[to]']
                                        associated_search_default_qty_from: //input[@name='qty[from]']
                                        associated_search_default_qty_to: //input[@name='qty[to]']
                                        associated_search_position_from: //input[@name='position[from]']
                                        associated_search_position_to: //input[@name='position[to]']
                                        associated_product_position: %productXpath%//input[@name='position']
                                        associated_product_default_qty: %productXpath%//input[@name='qty']
                -
                    downloadable_information: &downloadableInformationTab
                        xpath: //a[@title='Downloadable Information']/span
                        fieldsets:
                            -
                                downloadable_info:
                                    xpath: //dl[@id='downloadableInfo']
                                    links:
                                        downloadable_sample: //*[@id='dt-samples']/a
                                        downloadable_link: //*[@id='dt-links']/a
                            -
                                downloadable_sample:
                                    xpath: //*[@id='dd-samples']/div
                                    buttons:
                                        downloadable_sample_add_new_row: //*[@id='dd-samples']/div//button[span='Add New Row']
                                        downloadable_sample_upload_files: //*[@id='dd-samples']/div//button[span='Upload Files']
                                    fields:
                                        downloadable_samples_title: //input[@name='product[samples_title]']
                                        downloadable_sample_row_title: //input[@name='downloadable[sample][%rowId%][title]']
                                        downloadable_sample_row_url: //input[@name='downloadable[sample][%rowId%][sample_url]']
                                        downloadable_sample_row_sort_order: //input[@name='downloadable[sample][%rowId%][sort_order]']
                                    radiobuttons:
                                        downloadable_sample_row_url_radiobutton: //input[@id='downloadable_sample_%rowId%_url_type']
                                        downloadable_sample_row_file_radiobutton: //input[@id='downloadable_sample_%rowId%_file_type']
                            -
                                downloadable_link:
                                    xpath: //*[@id='dd-links']/div
                                    buttons:
                                        downloadable_link_add_new_row: //*[@id='dd-links']/div//button[span='Add New Row']
                                        downloadable_link_upload_files: //*[@id='dd-links']/div//button[span='Upload Files']
                                    checkboxes:
                                        downloadable_link_row_unlimited_downloads: //*[@id='downloadable_link_0_is_unlimited']
                                    dropdowns:
                                        downloadable_links_purchased_separately: //select[@id='downloadable_link_purchase_type']
                                        downloadable_link_shareable: //select[@id='downloadable_link _%rowId%_shareable']
                                    fields:
                                        downloadable_links_title: //input[@name='product[links_title]']
                                        downloadable_link_row_title: //input[@name='downloadable[link][%rowId%][title]']
                                        downloadable_link_row_sample_url: //input[@name='downloadable[link][%rowId%][sample][url]']
                                        downloadable_link_row_file_url: //input[@name='downloadable[link][%rowId%][link_url]']
                                        downloadable_link_row_sort_order: //input[@name='downloadable[link][%rowId%][sort_order]']
                                        downloadable_link_row_price: //input[@name='downloadable[link][%rowId%][price]']
                                        downloadable_link_max_downloads: //input[@name='downloadable[link][%rowId%][number_of_downloads]']
                                    radiobuttons:
                                        downloadable_link_row_sample_url_radiobutton: //input[@id='downloadable_link_%rowId%_sample_url_type']
                                        downloadable_link_row_url_radiobutton: //input[@id='downloadable_link_%rowId%_url_type']
        buttons:
            back: //button[span='Back']
            reset: //button[span='Reset']
            save: //button[span='Save']
            save_and_continue_edit: //button[span='Save and Continue Edit']
        messages: *productMesssages

new_configurable_product:
    mca: catalog_product/new/attributes/%attributesUrl%/set/%setId%/type/%productType%/
    title: New Product / Manage Products / Catalog / Magento Admin
    uimap: *newProduct

duplicated_configurable_product:
    mca: catalog_product/new/attributes/%attributesUrl%/id/%id%/
    title: New Product / Manage Products / Catalog / Magento Admin
    uimap: *newProduct

# Edit Product page
edit_product:
    mca: catalog_product/edit/id/%id%/
    title: %productName% / Manage Products / Catalog / Magento Admin
    uimap:
        form:
            tabs:
                -
                    configurable_product_settings: *configurableProductsettings
                -
                    general: *generalTab
                -
                    prices: *pricesTab
                -
                    meta_information: *metaInformationTab
                -
                    images: *imagesTab
                -
                    recurring_profile: *recurringProfileTab
                -
                    design: *designTab
                -
                    gift_options: *giftOptionsTab
                -
                    inventory: *inventoryTab
                -
                    websites: *websitesTab
                -
                    categories: *categoriesTab
                -
                    related: *relatedProductsTab
                -
                    up_sells: *upSellsProductsTab
                -
                    cross_sells: *crossSellsProductsTab
                -
                    product_tags:
                        xpath: //a[@title='Product Tags']/span
                        fieldsets:
                            -
                                product_tags:
                                    xpath: //div[@id='tag_grid']
                                    buttons:
                                        reset_filter: //button[span='Reset Filter']
                                        search: //button[span='Search']
                                    dropdowns:
                                        tag_search_status: //select[@name='status']
                                    fields:
                                        tag_search_name: //input[@name='name']
                                        tag_search_num_of_use_from: //input[@name='popularity[from]']
                                        tag_search_num_of_use_to: //input[@name='popularity[to]']
                -
                    custom_options: *customOptionsTab
                -
                    bundle_items: *bundleItemsTab
                -
                    associated:
                        xpath: //a[@title='Associated Products']/span
                        fieldsets:
                            -
                                create_simple_assosiated_product:
                                    xpath: //div[contains(div/h4,'Create Simple Associated Product')]
                                    buttons:
                                        create_empty: //button[span='Create Empty']
                                        create_copy_from_configurable: //button[span='Copy From Configurable']
                            -
                                quick_simple_product_creation:
                                    xpath: //div[contains(div/h4,'Quick simple product creation')]
                                    buttons:
                                        quick_create: //button[span='Quick Create']
                                    checkboxes:
                                        quick_simple_product_name_use_default: //input[@id='simple_product_name_autogenerate']
                                        quick_simple_product_sku_use_default: //input[@id='simple_product_sku_autogenerate']
                                    dropdowns:
                                        quick_simple_product_status: //select[@id='simple_product_status']
                                        quick_simple_product_visibility: //select[@id='simple_product_visibility']
                                        quick_simple_product_attribute_value: //select[@id='simple_product_%attributeCode%']
                                        quick_simple_product_stock_availability: //select[@id='simple_product_inventory_is_in_stock']
                                    fields:
                                        quick_simple_product_name: //input[@id='simple_product_name']
                                        quick_simple_product_sku: //input[@id='simple_product_sku']
                                        quick_simple_product_weight: //input[@id='simple_product_weight']
                                        quick_simple_product_qty: //input[@id='simple_product_inventory_qty']
                            -
                                super_products_attribute_configuration: *superProductsAttributeconfiguration
                            -
                                associated: *associatedProductsFieldSet
                -
                    downloadable_information: *downloadableInformationTab
        buttons:
            back: //button[span='Back']
            reset: //button[span='Reset']
            delete: //button[span='Delete']
            duplicate: //button[span='Duplicate']
            save: //button[span='Save']
            save_and_continue_edit: //button[span='Save and Continue Edit']
        messages: *productMesssages

create_empty_simple_from_configurable:
    mca: catalog_product/new/set/%setId%/type/simple/required/%attrId%/popup/1/
    title: New Product / Manage Products / Catalog / Magento Admin
    uimap: *newProduct

create_simple_from_configurable:
    mca: catalog_product/new/set/%setId%/type/simple/required/%attrId%/popup/1/product/%id%/
    title: New Product / Manage Products / Catalog / Magento Admin
    uimap: *newProduct

created_simple_from_configurable:
    mca: catalog_product/created/id/%id%/edit/0/set/%setId%/type/simple/required/%attrId%/popup/1/product/%productId%/
    title: Magento Admin
    uimap:
        form: *newProductCreationForm
        buttons:
            close_window: //button[span='Close Window']
        messages: *productMesssages

created_empty_simple_from_configurable:
    mca: catalog_product/created/id/%id%/edit/0/set/%setId%/type/simple/required/%attrId%/popup/1/
    title: Magento Admin
    uimap:
        form: *newProductCreationForm
        buttons:
            close_window: //button[span='Close Window']
        messages: *productMesssages
UIMAP;

    private $_twoUimapFileContent = <<<UIMAP
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
# Manage categories page and related pages

manage_categories:
    mca: catalog_category/
    click_xpath: //div[@class='nav-bar']//a[span='Manage Categories']
    title: New Category / Manage Categories / Categories / Catalog / Magento Admin
    uimap: &categoryUimap
        form:
            fieldsets:
                -
                    categories_tree:
                        xpath: //*[@id='page:left']/div
                        buttons:
                            add_root_category: //button[@id='add_root_category_button']
                            add_sub_category: //button[@id='add_subcategory_button']
                        links:
                            collapse_all: //a[text()='Collapse All']
                            expand_all: //a[text()='Expand All']
                            root_category: //ul/div/li[contains(div/a/span,'%rootName%')]
                            sub_category: //*[@id='%parentCategoryId%']/ancestor::li/ul/li[contains(div/a/span,'%subName%') and not(div/a/span/@id='%parentCategoryId%')]
                            expand_category: //*[@id='%parentCategoryId%']/ancestor::div/img[contains(@class,'x-tree-elbow-plus') or contains(@class,'x-tree-elbow-end-plus')]
                        dropdowns:
                            choose_store_view: //select[@id='store_switcher']
            tabs:
                -
                    general_information:
                        xpath: //a[@title='General Information']/span
                        fieldsets:
                            -
                                general_info:
                                    xpath: //div[contains(div/div/h4,'General Information')]
                                    checkboxes:
                                        delete_thumbnail_image: //input[@name='general[thumbnail][delete]']
                                        delete_image: //*[@name='general[image][delete]']
                                    buttons:
                                        wysiwyg_editor: //button[span='WYSIWYG Editor']
                                    dropdowns:
                                        is_active: //select[@name='general[is_active]']
                                        include_in_navigation_menu: //select[@name='general[include_in_menu]']
                                    fields:
                                        name: //input[@name='general[name]']
                                        url_key: //input[@name='general[url_key]']
                                        thumbnail_image: //input[@name='thumbnail']
                                        description: //textarea[@name='general[description]']
                                        image: //input[@name='image']
                                        page_title: //input[@name='general[meta_title]']
                                        meta_keywords: //textarea[@name='general[meta_keywords]']
                                        meta_description: //textarea[@name='general[meta_description]']
                -
                    display_settings:
                        xpath: //a[@title='Display Settings']/span
                        fieldsets:
                            -
                                display_settings:
                                    xpath: "//div[contains(div/div/h4,'Display Settings')]"
                                    checkboxes:
                                        available_product_listing_config: //input[@value='available_sort_by']
                                        default_product_listing_config: //input[@value='default_sort_by']
                                        layered_navigation_price_step_config: //input[@value='filter_price_range']
                                    dropdowns:
                                        display_mode: //select[@name='general[display_mode]']
                                        cms_block: //select[@name='general[landing_page]']
                                        is_anchor: //select[@name='general[is_anchor]']
                                        default_product_listing: //select[@name='general[default_sort_by]']
                                    fields:
                                        layered_navigation_price_step: //input[@name='general[filter_price_range]']
                                    multiselects:
                                        available_product_listing: //select[@name='general[available_sort_by][]']
                -
                    custom_design:
                        xpath: //a[@title='Custom Design']/span
                        fieldsets:
                            -
                                custom_design:
                                    xpath: "//div[contains(div/div/h4,'Custom Design') and not (@style='display: none;')]"
                                    dropdowns:
                                        use_parent_category_settings: //select[@name='general[custom_use_parent_settings]']
                                        apply_to_products: //select[@name='general[custom_apply_to_products]']
                                        custom_design: //select[@name='general[custom_design]']
                                        page_layout: //select[@name='general[page_layout]']
                                    fields:
                                        active_from: //input[@name='general[custom_design_from]']
                                        active_to: //input[@name='general[custom_design_to]']
                                        custom_layout_update: //textarea[@name='general[custom_layout_update]']
                -
                     category_products:
                        xpath: //a[@title='Category Products']/span
                        fieldsets:
                            -
                                category_products:
                                    xpath: //div[@id='catalog_category_products']
                                    buttons:
                                        reset_filter: //button[span='Reset Filter']
                                        search: //button[span='Search']
                                    dropdowns:
                                        category_products_massaction: //select[@id='filter_in_category']
                                    fields:
                                        category_products_search_id: //input[@id='catalog_category_products_filter_entity_id']
                                        category_products_search_name: //input[@id='catalog_category_products_filter_name']
                                        category_products_search_sku: //input[@id='catalog_category_products_filter_sku']
                                        category_products_search_price_from: //input[@id='catalog_category_products_filter_price_from']
                                        category_products_search_price_to: //input[@id='catalog_category_products_filter_price_to']
                                        category_products_search_position_from: //input@id='catalog_category_products_filter_position_from']
                                        category_products_search_position_to: //input@id='catalog_category_products_filter_position_to']
                                        category_products_position: %productXpath%//input[@name='position']
        buttons:
            reset: //button[span='Reset']
            save_category: //button[span='Save Category']
            delete_category: //button[span='Delete Category']
        messages:
            empty_required_field: "//div[@id='advice-required-entry-%fieldId%' and not(contains(@style,'display: none;'))]"
            success_saved_category: //li[normalize-space(@class)='success-msg' and contains(.,'The category has been saved.')]
            success_deleted_category: //li[normalize-space(@class)='success-msg' and contains(.,'The category has been deleted.')]
            confirm_delete: Are you sure you want to delete this category?

edit_manage_categories:
    mca: catalog_category/edit/store/0/
    click_xpath: //div[@class='nav-bar']//a[span='Manage Categories']
    title: New Category / Manage Categories / Categories / Catalog / Magento Admin
    uimap: *categoryUimap

UIMAP;

    private $_twoPageXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<page xmlns="urn:magento:uimap-page"
    key="manage_categories"
    title="New Category / Manage Categories / Categories / Catalog / Magento Admin"
    url="catalog_category/">

    <fieldset key="categories_tree" xpath="//*[@id='page:left']/div">
        <button key="add_root_category" xpath="//button[@id='add_root_category_button']"/>
        <button key="add_sub_category" xpath="//button[@id='add_subcategory_button']"/>

        <link key="collapse_all" xpath="//a[text()='Collapse All']"/>
        <link key="expand_all" xpath="//a[text()='Expand All']"/>
        <link key="root_category" xpath="//ul/div/li[contains(div/a/span,'%rootName%')]"/>
        <link key="sub_category" xpath="//*[@id='%parentCategoryId%']/ancestor::li/ul/li[contains(div/a/span,'%subName%') and not(div/a/span/@id='%parentCategoryId%')]"/>
        <link key="expand_category" xpath="//*[@id='%parentCategoryId%']/ancestor::div/img[contains(@class,'x-tree-elbow-plus') or contains(@class,'x-tree-elbow-end-plus')]"/>

        <select key="choose_store_view" xpath="//select[@id='store_switcher']"/>
    </fieldset>

    <button key="tab_general_information" xpath="//a[@title='General Information']/span"/>

    <tab key="general_information" xpath="">
        <fieldset key="general_info" xpath="//div[contains(div/div/h4,'General Information')]">
            <checkbox key="delete_thumbnail_image" xpath="//input[@name='general[thumbnail][delete]']"/>
            <checkbox key="delete_thumbnail_image" xpath="//*[@name='general[image][delete]']"/>

            <button key="wysiwyg_editor" xpath="//button[span='WYSIWYG Editor']"/>

            <select key="is_active" xpath="//select[@name='general[is_active]']"/>
            <select key="include_in_navigation_menu" xpath="//select[@name='general[include_in_menu]']"/>

            <field key="name" xpath="//input[@name='general[name]']"/>
            <field key="url_key" xpath="//input[@name='general[url_key]']"/>
            <field key="thumbnail_image" xpath="//input[@name='thumbnail']"/>
            <field key="description" xpath="//textarea[@name='general[description]']"/>
            <field key="image" xpath="//input[@name='image']"/>
            <field key="page_title" xpath="//input[@name='general[meta_title]']"/>
            <field key="meta_keywords" xpath="//textarea[@name='general[meta_keywords]']"/>
            <field key="meta_description" xpath="//textarea[@name='general[meta_description]']"/>
        </fieldset>
    </tab>

    <button key="tab_display_settings" xpath="//a[@title='Display Settings']/span"/>

    <tab key="display_settings" xpath="">
        <fieldset key="display_settings" xpath="//div[contains(div/div/h4,'Display Settings')]">
            <checkbox key="available_product_listing_config" xpath="//input[@value='available_sort_by']"/>
            <checkbox key="default_product_listing_config" xpath="//input[@value='default_sort_by']"/>
            <checkbox key="layered_navigation_price_step_config" xpath="//input[@value='filter_price_range']"/>

            <select key="display_mode" xpath="//select[@name='general[display_mode]']"/>
            <select key="cms_block" xpath="//select[@name='general[landing_page]']"/>
            <select key="is_anchor" xpath="//select[@name='general[is_anchor]']"/>
            <select key="default_product_listing" xpath="//select[@name='general[default_sort_by]']"/>

            <field key="layered_navigation_price_step" xpath="//input[@name='general[filter_price_range]']"/>

            <select key="available_product_listing" xpath="//select[@name='general[available_sort_by][]']"/>
        </fieldset>
    </tab>

    <button key="tab_custom_design" xpath="//a[@title='Custom Design']/span"/>

    <tab key="custom_design" xpath="">
        <fieldset key="custom_design" xpath="//div[contains(div/div/h4,'Custom Design') and not (@style='display: none;')]">
            <select key="use_parent_category_settings" xpath="//select[@name='general[custom_use_parent_settings]']"/>
            <select key="apply_to_products" xpath="//select[@name='general[custom_apply_to_products]']"/>
            <select key="custom_design" xpath="//select[@name='general[custom_design]']"/>
            <select key="page_layout" xpath="//select[@name='general[page_layout]']"/>

            <field key="active_from" xpath="//input[@name='general[custom_design_from]']"/>
            <field key="active_to" xpath="//input[@name='general[custom_design_to]']"/>
            <field key="custom_layout_update" xpath="//textarea[@name='general[custom_layout_update]']"/>
        </fieldset>
    </tab>

    <button key="tab_category_products" xpath="//a[@title='Category Products']/span"/>

    <tab key="category_products" xpath="">
        <fieldset key="category_products" xpath="//div[@id='catalog_category_products']">
            <button key="reset_filter" xpath="//button[span='Reset Filter']"/>
            <button key="search" xpath="//button[span='Search']"/>

            <select key="category_products_massaction" xpath="//select[@id='filter_in_category']"/>

            <field key="category_products_search_id" xpath="//input[@id='catalog_category_products_filter_entity_id']"/>
            <field key="category_products_search_name" xpath="//input[@id='catalog_category_products_filter_name']"/>
            <field key="category_products_search_sku" xpath="//input[@id='catalog_category_products_filter_sku']"/>
            <field key="category_products_search_price_from" xpath="//input[@id='catalog_category_products_filter_price_from']"/>
            <field key="category_products_search_price_to" xpath="//input[@id='catalog_category_products_filter_price_to']"/>
            <field key="category_products_search_position_from" xpath="//input@id='catalog_category_products_filter_position_from']"/>
            <field key="category_products_search_position_to" xpath="//input@id='catalog_category_products_filter_position_to']"/>
            <field key="category_products_position" xpath="%productXpath%//input[@name='position']"/>
        </fieldset>
    </tab>

    <button key="reset" xpath="//button[span='Reset']"/>
    <button key="save_category" xpath="//button[span='Save Category']"/>
    <button key="delete_category" xpath="//button[span='Delete Category']"/>
</page>
XML;

    /**
     * Returns root element of page that corresponds one uimap page
     *
     * @return DOMElement
     */
    protected function _getOnePageTabGeneralExpectedElement()
    {
        $expected = new \DOMDocument;
        $expected->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<page xmlns="urn:magento:uimap-page"
    key="edit_product"
    title="%productName% / Manage Products / Catalog / Magento Admin"
    url="catalog_product/edit/id/([^/]*)/">

    <button key="tab_general" xpath="//a[@title='General']/span"/>

    <tab key="general" xpath="">
        <fieldset key="product_general" xpath="//div[contains(div/div/h4,'General')]">
            <button key="create_new_attribute" xpath="//button[span='Create New Attribute']"/>

            <select key="general_sku_type" xpath="//select[@id='sku_type']"/>
            <select key="general_weight_type" xpath="//select[@id='weight_type']"/>
            <select key="general_status" xpath="//select[@id='status']"/>
            <select key="general_visibility" xpath="//select[@id='visibility']"/>
            <select key="general_in_feed" xpath="//select[@id='is_imported']"/>
            <select key="general_country_manufacture" xpath="//select[@id='country_of_manufacture']"/>
            <select key="general_user_attr_dropdown" xpath="//select[@id='%attibuteCodeDropdown%']"/>

            <field key="general_name" xpath="//textarea[@id='description']"/>
            <field key="general_description" xpath="//textarea[@id='description']"/>
            <field key="general_short_description" xpath="//textarea[@id='short_description']"/>
            <field key="general_sku" xpath="//input[@id='sku']"/>
            <field key="general_weight" xpath="//input[@id='weight' and not(@disabled)]"/>
            <field key="general_news_from" xpath="//input[@id='news_from_date']"/>
            <field key="general_news_to" xpath="//input[@id='news_to_date']"/>
            <field key="general_url_key" xpath="//input[@id='url_key']"/>
            <field key="general_user_attr_field" xpath="//input[@id='%attibuteCodeField%']"/>

            <select key="general_user_attr_multiselect" xpath="//input[@id='%attibuteCodeMultiselect%']"/>
        </fieldset>
    </tab>

</page>
XML
        );

        $xp = new \DOMXPath($expected);
        $xp->registerNamespace('p', 'urn:magento:uimap-page');

        return $xp->query("//p:tab[@key='general']")->item(0);
    }
}