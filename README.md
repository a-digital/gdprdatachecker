# GDPR Data Checker plugin for Craft CMS 3.x

Run through the database and pull out any information associated with a specified email address

<img src="src/icon.svg" width="250px" alt="Logo" title="Logo">

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require adigital/gdprdatachecker

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for GDPR Data Checker.

## GDPR Data Checker Overview

When someone requests all of the data that a company holds on them, we don't want to have to run through the database and create custom queries for each request. Due to the introduction of GDPR, we have thought about the process of getting all of the data that relates to an email address. We have looped through all of the user tables, authored entries, form submissions, and commerce orders.

## Configuring GDPR Data Checker

No configuration is necessary after installation has been completed.

## Using GDPR Data Checker

Go to GDPR Checker from the sidebar, and type in an email address. Once the check is completed you can download a PDF report or email the information directly to the email address entered.

Currently this plugin is compatible with the following:

+ Craft Commerce
+ Charge
+ Freeform
+ Formbuilder

Brought to you by [A Digital](https://adigital.agency)
