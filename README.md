# GDPR Data Checker plugin for Craft CMS

Run through the database and pull out any information associated with a specified email address

## Installation

To install GDPR Data Checker, follow these steps:

1. Download & unzip the file and place the `gdprdatachecker` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/gdprdatachecker/gdprdatachecker.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3.  -OR- install with Composer via `composer require gdprdatachecker/gdprdatachecker`
4. Install plugin in the Craft Control Panel under Settings > Plugins
5. The plugin folder should be named `gdprdatachecker` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

GDPR Data Checker works on Craft 2.4.x and Craft 2.5.x and Craft 2.6.x.

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

Brought to you by [Matt Shearing](https://adigital.agency)
