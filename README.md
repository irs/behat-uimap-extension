Behat UI map extension
======================

[![Build Status](https://travis-ci.org/irs/behat-uimap-extension.png?branch=master)](https://travis-ci.org/irs/behat-uimap-extension)

This extension allows to use UI map files of [Magento test automation framework] (https://github.com/magento/taf) 
in Behat's features.

Installation
============

To install extension with Composer you need to add following lines into `composer.json:`

```javascript
{
    "require": {
        "irs/behat-uimap-extension": "dev-master"
    }
}
```

After that run `composer install` and add following into `behat.yml:`

```
default:
  extensions:
    Irs\BehatUimapExtension\Extension:
      uimaps: 
        "admin/": features\uimaps\admin
        "/": features\uimaps\frontend
```

Keys from `uimap` node define URL prefixes for UI map files from corresponding directories.

Usage
-----

To use "language" from UI map files you need to use `Irs\BehatUimapExtension\Context\UimapContext` trait in your 
features' context. This trait contains definitions for following steps:

```
Then /^the (?i)url(?-i) should match (?P<pattern>"([^"]|\\")*")$/
Then /^the response status code should be (?P<code>\d+)$/
Then /^the response status code should not be (?P<code>\d+)$/
Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)"$/
Then /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)"$/
Then /^(?:|I )should see text matching (?P<pattern>"(?:[^"]|\\")*")$/
Then /^(?:|I )should not see text matching (?P<pattern>"(?:[^"]|\\")*")$/
Then /^the response should contain "(?P<text>(?:[^"]|\\")*)"$/
Then /^the response should not contain "(?P<text>(?:[^"]|\\")*)"$/
Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in the "(?P<element>[^"]*)" element$/
hen /^(?:|I )should not see "(?P<text>(?:[^"]|\\")*)" in the "(?P<element>[^"]*)" element$/
Then /^the "(?P<element>[^"]*)" element should contain "(?P<value>(?:[^"]|\\")*)"$/
Then /^the "(?P<element>[^"]*)" element should not contain "(?P<value>(?:[^"]|\\")*)"$/
Then /^(?:|I )should see an? "(?P<element>[^"]*)" element$/
Then /^(?:|I )should not see an? "(?P<element>[^"]*)" element$/
Then /^print last response$/
Then /^show last response$/
When /^(?:|I )attach the file "(?P[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )attach the file "(?P[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )attach the file "(?P[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )attach the file "(?P[^"]*)" to "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )attach the file "(?P[^"]*)" to "(?P<field>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )follow "(?P<link>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )check "(?P<option>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )uncheck "(?P<option>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in the following:$/
When /^(?:|I )select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )switch to tab "([^"]*)"$/
When /^(?:|I )switch to tab "([^"]*)" with following parameters:$/
When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )additionally select "(?P<option>(?:[^"]|\\")*)" from "(?P<select>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )wait for (\d+) milliseconds?$/
When /^(?:|I )wait for (\d+) seconds?$/
When /^(?:|I )wait for AJAX$/
When /^(?:|I )wait for AJAX up to (\d+) milliseconds?$/
When /^(?:|I )wait for AJAX up to (\d+) seconds?$/
When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )press "(?P<button>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)"$/
When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)"$/
When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" in fieldset "(?P<fieldset>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)" with following parameters:$/
When /^(?:|I )fill in the following into fieldset "(?P<fieldset>(?:[^"]|\\")*)":$/
When /^(?:|I )fill in the following into fieldset "(?P<fieldset>(?:[^"]|\\")*)" of tab "(?P<tab>(?:[^"]|\\")*)":$/
Given /^(?:|I )am on "(?P<page>[^"]+)"$/
Given /^(?:|I )am on "(?P<page>[^"]+)" with next parameters:$/
When /^(?:|I )go to "(?P<page>[^"]+)" with next parameters:$/
When /^(?:|I )believe I am on "(?P<page>[^"]+)"$/
```
