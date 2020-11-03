# GDPR Data Checker Changelog

## 2.0.7 - 2020-11-03
### Fixed
- Composer 2 compatibility - Asset bundle PSR-4 autoloading standard

## 2.0.6 - 2019-01-18
### Fixed
- Fixed error with commerce customer queries

## 2.0.5 - 2018-10-01
### Fixed
- Fixed error where freeform submissions were sometimes missing

## 2.0.4 - 2018-09-05
### Changed
- Updated `Order::getOrderLocale()` to `Order::orderLanguage` after seeing a deprecation error

## 2.0.3 - 2018-09-05
### Fixed
- Fixed pdf issue around shipping methods

## 2.0.2 - 2018-09-03
### Fixed
- Fixed various errors where commerce order data is included in the report
- Fixed pdf generation errors where commerce order data is included
- Fixed address handling where only the reference id was included instead of the full address model

## 2.0.1 - 2018-08-29
### Fixed
- Fixed an error where the CustomerRecord class was not found when querying commerce data

## 2.0.0 - 2018-08-06
### Changed
- Updated for Craft 3

## 1.0.4 -- 2018-08-06
### Changed
* Updated from master branch to craft2 branch to allow for craft3 version to be published

## 1.0.3 -- 2018-06-04
### Changed
* Fixed scenarios when no results are returned to display a user friendly message

## 1.0.2 -- 2018-06-04
### Changed
* Fixed count() warning shown only on PHP 7.2

## 1.0.1 -- 2018-06-04
### Changed
* Fixed an issue with foreach loops if no results were returned by the queries
* Fixed github urls for documentation

## 1.0.0 -- 2018-05-04
* Initial release

Brought to you by [A Digital](https://adigital.agency)