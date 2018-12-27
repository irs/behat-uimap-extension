<?php
/**
 * This file is part of the Behat UI map extension.
 * (c) 2013 Vadim Kusakin <vadim.irbis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Irs\BehatUimapExtension\Context;

use Irs\BehatUimapExtension\PageSource\PageSourceInterface,
    Irs\BehatUimapExtension\PageSource\Factory,
    Irs\BehatUimapExtension\Locator,
    Irs\BehatUimapExtension\UimapSelector,
    Irs\BehatUimapExtension\Driver\Selenium2Driver;

use Behat\Gherkin\Node\TableNode,
    Behat\Mink\Driver\DriverInterface,
    Behat\Mink\Mink,
    Behat\Mink\Element\NodeElement,
    Behat\Mink\Element\DocumentElement,
    Behat\Mink\Exception\ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException,
    Behat\Mink\Selector\SelectorsHandler,
    Behat\Mink\Session;

/**
 * Base context for feature that uses TAF UI maps
 *
 */
trait UimapContext
{
    /**
     * Page source
     *
     * @var PageSourceInterface
     */
    private $pageSource;

    /**
     * Current page
     *
     * @var \Irs\BehatUimapExtension\Page
     */
    private $currentPage;

    /**
     * @var Mink
     */
    private $mink;

    /**
     * @var array
     */
    private $minkParameters = array();

    public function setPageSource(PageSourceInterface $pageSource)
    {
        $this->pageSource = $pageSource;
    }

    /**
     * Returns page source
     *
     * @return PageSourceInterface
     * @throws \RuntimeException If page source is undefined
     */
    protected function getPageSource()
    {
        if (!$this->pageSource instanceof PageSourceInterface) {
            throw new \RuntimeException('Page source is not defined; context was not properly initialized.');
        }

        return $this->pageSource;
    }

    /**
     * Sets Mink instance
     */
    public function setMink(Mink $mink)
    {
        $this->mink = $mink;
    }

    /**
     * Returns Mink instance
     *
     * @return Mink
     * @throws \RuntimeException is Mink is undefined
     */
    public function getMink()
    {
        if (!$this->mink instanceof Mink) {
            throw new \RuntimeException('Mink is not defined; context was not properly initialized.');
        }

        return $this->mink;
    }

    /**
     * Sets parameters provided for Mink.
     *
     * @param array $parameters
     */
    public function setMinkParameters(array $parameters)
    {
        $this->minkParameters = $parameters;
    }

    /**
     * Returns specific mink parameter.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getMinkParameter($name)
    {
        return isset($this->minkParameters[$name]) ? $this->minkParameters[$name] : null;
    }

    /**
     * Returns Mink session assertion tool.
     *
     * @param string|null $name name of the session OR active session will be used
     * @return WebAssert
     */
    public function assertSession($name = null)
    {
        return $this->getMink()->assertSession($name);
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }

    /**
     * Checks, that current page PATH matches regular expression.
     *
     * @Then /^the (?i)url(?-i) should match (?P<pattern>"([^"]|\\")*")$/
     */
    public function assertUrlRegExp($pattern)
    {
        $this->assertSession()->addressMatches($pattern);
    }

    /**
     * Checks, that current page response status is equal to specified.
     *
     * @Then /^the response status code should be (?P<code>\d+)$/
     */
    public function assertResponseStatus($code)
    {
        $this->assertSession()->statusCodeEquals($code);
    }

    /**
     * Checks, that current page response status is not equal to specified.
     *
     * @Then /^the response status code should not be (?P<code>\d+)$/
     */
    public function assertResponseStatusIsNot($code)
    {
        $this->assertSession()->statusCodeNotEquals($code);
    }

    /**
     * Checks, that page contains specified text.
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageContainsText($text)
    {
        $this->assertSession()->pageTextContains($this->fixStepArgument($text));
    }

    /**
     * Checks, that page doesn't contain specified text.
     *
     * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageNotContainsText($text)
    {
        $this->assertSession()->pageTextNotContains($this->fixStepArgument($text));
    }

    /**
     * Checks, that page contains text matching specified pattern.
     *
     * @Then /^(?:|I )should see text matching (?P<pattern>"(?:[^"]|\\")*")$/
     */
    public function assertPageMatchesText($pattern)
    {
        $this->assertSession()->pageTextMatches($this->fixStepArgument($pattern));
    }

    /**
     * Checks, that page doesn't contain text matching specified pattern.
     *
     * @Then /^(?:|I )should not see text matching (?P<pattern>"(?:[^"]|\\")*")$/
     */
    public function assertPageNotMatchesText($pattern)
    {
        $this->assertSession()->pageTextNotMatches($this->fixStepArgument($pattern));
    }

    /**
     * Checks, that HTML response contains specified string.
     *
     * @Then /^the response should contain "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertResponseContains($text)
    {
        $this->assertSession()->responseContains($this->fixStepArgument($text));
    }

    /**
     * Checks, that HTML response doesn't contain specified string.
     *
     * @Then /^the response should not contain "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertResponseNotContains($text)
    {
        $this->assertSession()->responseNotContains($this->fixStepArgument($text));
    }

    /**
     * Checks, that element with specified uimap key contains specified text.
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in the "(?P<element>[^"]*)" element$/
     */
    public function assertElementContainsText($element, $text)
    {
        $this->assertSession()
            ->elementTextContains(
                'uimap',
                $this->createLocatorForCurrentPage($element),
                $this->fixStepArgument($text)
            );
    }

    /**
     * Checks, that element with specified uimap key doesn't contain specified text.
     *
     * @Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)" in the "(?P<element>[^"]*)" element$/
     */
    public function assertElementNotContainsText($element, $text)
    {
        $this->assertSession()
            ->elementTextNotContains(
                'uimap',
                $this->createLocatorForCurrentPage($element),
                $this->fixStepArgument($text)
            );
    }

    /**
     * Checks, that element with specified uimaps key contains specified HTML.
     *
     * @Then /^the "(?P<element>[^"]*)" element should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertElementContains($element, $value)
    {
        $this->assertSession()
            ->elementContains(
                'uimap',
                $this->createLocatorForCurrentPage($element),
                $this->fixStepArgument($value)
            );
    }

    /**
     * Checks, that element with specified uimaps key doesn't contain specified HTML.
     *
     * @Then /^the "(?P<element>[^"]*)" element should not contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertElementNotContains($element, $value)
    {
        $this->assertSession()
            ->elementNotContains(
                'uimap',
                $this->createLocatorForCurrentPage($element),
                $this->fixStepArgument($value)
            );
    }

    /**
     * Checks, that element with specified uimpa key exists on page.
     *
     * @Then /^(?:|I )should see an? "(?P<element>[^"]*)" element$/
     */
    public function assertElementOnPage($element)
    {
        $this->assertSession()->elementExists('uimap', $this->createLocatorForCurrentPage($element));
    }

    /**
     * Checks, that element with specified uimap key doesn't exist on page.
     *
     * @Then /^(?:|I )should not see an? "(?P<element>[^"]*)" element$/
     */
    public function assertElementNotOnPage($element)
    {
        $this->assertSession()->elementNotExists('uimap', $this->createLocatorForCurrentPage($element));
    }

    /**
     * Prints last response to console.
     *
     * @Then /^print last response$/
     */
    public function printLastResponse()
    {
        $this->printDebug(
            $this->getSession()->getCurrentUrl()."\n\n".
            $this->getSession()->getPage()->getContent()
        );
    }

    /**
     * Opens last response content in browser.
     *
     * @Then /^show last response$/
     */
    public function showLastResponse()
    {
        if (null === $this->getMinkParameter('show_cmd')) {
            throw new \RuntimeException('Set "show_cmd" parameter in behat.yml to be able to open page in browser (ex.: "show_cmd: firefox %s")');
        }

        $filename = rtrim($this->getMinkParameter('show_tmp_dir'), DIRECTORY_SEPARATOR)
                  . DIRECTORY_SEPARATOR . uniqid() . '.html';
        file_put_contents($filename, $this->getSession()->getPage()->getContent());
        system(sprintf($this->getMinkParameter('show_cmd'), escapeshellarg($filename)));
    }

   /**
     * Finds field (input, textarea, select) with specified locator.
     *
     * @param string $locator input id, name or label
     *
     * @return NodeElement|null
     */
    public function findField($key = null, $type = null, $fieldset = null, $tab = null, array $params = array())
    {
        $field = $this->getSession()->getPage()->find(
            'uimap',
            $locator = $this->createLocatorForCurrentPage($key, $type, $fieldset, $tab, $params)
        );

        if (null === $field) {
            throw new ElementNotFoundException($this->getSession(), $type, 'uimap', $locator);
        }

        return $field;
    }

    /**
     * Returns Session
     *
     * @return Session
     */
    public function getSession($name = null)
    {
        return $this->getMink()->getSession($name);
    }

    protected function createLocatorForCurrentPage($key = null, $type = null,
        $fieldset = null, $tab = null, array $params = array())
    {
        return new Locator(
            $this->getSession()->getCurrentUrl(),
            $key, $type, $fieldset, $tab, $params,
            $this->currentPage ? $this->currentPage->getKey() : null
        );
    }

    public function assertPageAddress($page)
    {
        $actualKey = $this->getPageSource()
            ->getPageByUrl($this->getSession()->getCurrentUrl())
            ->getKey();

        if ($page != $actualKey) {
            throw new ExpectationException(
                "Current page is '$actualKey', but '$page' expected.",
                $this->getSession()
            );
        }
    }

    /**
     * Attaches file to field
     *
     * @When /^(?:|I )attach the file "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )attach the file "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )attach the file "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function attachFileToField($field, $path, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($field, 'field', $fieldset, $tab, $params)
            ->attachFile($this->getFullpathForUpload($path));
    }

    protected function getFullpathForUpload($relativePath)
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR)
                      . DIRECTORY_SEPARATOR . $relativePath;
            if (is_file($fullPath)) {
                return $fullPath;
            }
        }

        return $relativePath;
    }

    /**
     * Attaches file to field with parameters of xpath query
     *
     * @When /^(?:|I )attach the file "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function attachFileToFieldInFieldsetWithParams($field, $path, $fieldset, TableNode $params)
    {
        $this->attachFileToField($field, $path, $fieldset, null, $params);
    }

    /**
     * Attaches file to field
     *
     * @When /^(?:|I )attach the file "(?P<path>[^"]*)" to "(?P<field>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function attachFileToFieldWithParams($field, $path, TableNode $params)
    {
        $this->attachFileToField($field, $path, null, null, $params);
    }

    /**
     * Clicks link
     *
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function clickLink($link, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($link, 'link', $fieldset, $tab, $params)
            ->click();
        $this->currentPage = null;
    }

    /**
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function clickLinkInFiedsetWithParams($link, $fieldset, TableNode $params)
    {
        $this->clickLink($link, $fieldset, null, $params);
    }

    /**
     * @When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function clickLinkWithParams($link, TableNode $params)
    {
        $this->clickLink($link, null, null, $params);
    }

    /**
     * Checks checkbox
     *
     * @When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function checkOption($option, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($option, 'checkbox', $fieldset, $tab, $params)
            ->click(); // click because some JS handler listen click on checkbox
    }

    /**
     * Checks checkbox with specified id|name|label|value.
     *
     * @When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function checkOptionInFiedsetWithParams($option, $fieldset, TableNode $params)
    {
        $this->checkOption($option, $fieldset, null, $params);
    }

    /**
     * Checks checkbox with specified id|name|label|value.
     *
     * @When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function checkOptionWithParams($option, TableNode $params)
    {
        $this->checkOption($option, null, null, $params);
    }

    /**
     * Unchecks checkbox
     *
     * @When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function uncheckOption($option, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($option, 'checkbox', $fieldset, $tab, $params)
            ->uncheck();
    }

    /**
     * Unchecks checkbox
     *
     * @When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function uncheckOptionInFiedsetWithParams($option, $fieldset, TableNode $params)
    {
        $this->uncheckOption($option, $fieldset, null, $params);
    }

    /**
     * Unchecks checkbox
     *
     * @When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function uncheckOptionWithParams($option, TableNode $params)
    {
        $this->uncheckOption($option, null, null, $params);
    }

    /**
     * Selects option in select field with specified id|name|label|value.
     *
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function selectOption($select, $option, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($select, 'select', $fieldset, $tab, $params)
            ->selectOption($option);
    }

    /**
     * Selects option in select field with specified id|name|label|value.
     *
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function selectOptionInFiedsetWithParams($select, $option, $fieldset, TableNode $params)
    {
        $this->selectOption($select, $option, $fieldset, null, $params);
    }

    /**
     * Fills in form fields with provided table.
     *
     * @When /^(?:|I )fill in the following:$/
     */
    public function fillFields(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
            $this->fillField($field, $value);
        }
    }

    /**
     * Selects option in select field with specified id|name|label|value.
     *
     * @When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function selectOptionWithParams($select, $option, TableNode $params = null)
    {
        $this->selectOption($select, $option, null, null, $params);
    }

    /**
     * Switchs to tab with specified name.
     *
     * @When /^(?:|I )switch to tab "([^"]*)"$/
     * @When /^(?:|I )switch to tab "([^"]*)" with following parameters:$/
     */
    public function selectTab($tab, TableNode $params = null)
    {
        $this->pressButton("tab_$tab", null, null, $params);
    }

    /**
     * Selects additional option in select field with specified id|name|label|value.
     *
     * @When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function additionallySelectOption($select, $option, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($select, 'select', $fieldset, $tab, $params)
            ->selectOption($option, true);
    }

    /**
     * Selects additional option in select field with specified id|name|label|value.
     *
     * @When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function additionallySelectOptionInFiedsetWithParams($select, $option, $fieldset, TableNode $params)
    {
        $this->additionallySelectOption($select, $option, $fieldset, null, $params);
    }

    /**
     * Selects additional option in select field with specified id|name|label|value.
     *
     * @When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function additionallySelectOptionWithParams($select, $option, TableNode $params = null)
    {
        $this->additionallySelectOption($select, $option, null, null, $params);
    }

    /**
     * @When /^(?:|I )wait for (\d+) milliseconds?$/
     */
    public function wait($milliseconds)
    {
        $this->getSession()->wait($milliseconds);
    }

    /**
     * @When /^(?:|I )wait for (\d+) seconds?$/
     */
    public function waitForSeconds($seconds)
    {
        $this->wait($seconds * 1000);
    }

    /**
     * @When /^(?:|I )wait for AJAX$/
     * @When /^(?:|I )wait for AJAX up to (\d+) milliseconds?$/
     */
    public function waitForAjax($milliseconds = 10000)
    {
        $this->getSession()->wait(
            $milliseconds,
            'window.Ajax ? !window.Ajax.activeRequestCount : (window.jQuery ? !window.jQuery.active : false)'
        );
    }

    /**
     * @When /^(?:|I )wait for AJAX up to (\d+) seconds?$/
     */
    public function waitForAjaxUpToSeconds($seconds)
    {
        $this->waitForAjax($seconds * 1000);
    }

    /**
     * Presses button with specified id|name|title|alt|value.
     *
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function pressButton($button, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($button, 'button', $fieldset, $tab, $params)
            ->press();
        $this->currentPage = null;
    }

    /**
     * Presses button with specified id|name|title|alt|value.
     *
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function pressButtonInFiedsetWithParams($button, $fieldset, TableNode $params)
    {
        $this->pressButton($button, $fieldset, null, $params);
    }

    /**
     * Presses button with specified id|name|title|alt|value.
     *
     * @When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function pressButtonWithParams($button, TableNode $params)
    {
        $this->pressButton($button, null, null, $params);
    }

    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function fillField($field, $value, $fieldset = null, $tab = null, TableNode $params = null)
    {
        $params = ($params) ? $params->getRowsHash() : array();
        $this->findField($field, 'field', $fieldset, $tab, $params)
            ->setValue($value);
    }

    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function fillFieldInFiedsetWithParams($field, $value, $fieldset, TableNode $params)
    {
        $this->fillField($field, $value, $fieldset, null, $params);
    }

    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" with following parameters:$/
     * @When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" with following parameters:$/
     */
    public function fillFieldWithParams($field, $value, TableNode $params)
    {
        $this->fillField($field, $value, null, null, $params);
    }

    /**
     * Fills in form fields with provided table.
     *
     * @When /^(?:|I )fill in the following into fieldset "(?P<fieldset>(?:[^"]|\\")*)":$/
     */
    public function fillFieldsIntoFieldset($fieldset, TableNode $tableNode)
    {
        $this->fillFieldsIntoFieldsetOfTab($fieldset, null, $tableNode);
    }


    /**
     * Fills in form fields with provided table.
     *
     * @When /^(?:|I )fill in the following into fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)":$/
     */
    public function fillFieldsIntoFieldsetOfTab($fieldset, $tab, TableNode $tableNode)
    {
        foreach ($tableNode->getRowsHash() as $key => $value) {
            $this->fillField($key, $value, $fieldset, $tab);
        }
    }

    /**
     * Opens specified page with params
     *
     * @Given /^(?:|I )am on "(?P<page>[^"]+)"$/
     * @Given /^(?:|I )am on "(?P<page>[^"]+)" with next parameters:$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)" with next parameters:$/
     */
    public function visit($page, TableNode $params = null)
    {
        $params = $params ? $params->getRowsHash() : array();

        $pageUrl = $this->getPageSource()
            ->getPageByKey($page)
            ->getUrl($params);

        $this->getSession()->visit($this->locatePath($pageUrl));
    }

    /**
     * Locates url, based on provided path.
     * Override to provide custom routing mechanism.
     *
     * @param string $path
     * @return string
     */
    public function locatePath($path)
    {
        $startUrl = rtrim($this->getMinkParameter('base_url'), '/') . '/';

        return 0 !== strpos($path, 'http') ? $startUrl . ltrim($path, '/') : $path;
    }

    /**
     * Loads UI map page by key
     *
     * @When /^(?:|I )believe I am on "(?P<page>[^"]+)"$/
     */
    public function loadPage($page)
    {
        $this->currentPage = $this->getPageSource()->getPageByKey($page);
    }
}
