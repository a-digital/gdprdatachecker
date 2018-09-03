# GDPR Data Checker Changelog

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