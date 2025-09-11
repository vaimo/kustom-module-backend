
11.0.14 / 2025-06-03
==================

  * PPP-2089 Updated version because of version dependency updates

11.0.13 / 2025-05-21
==================

  * PPP-2055 Compatibility with AC 2.4.8 and PHP 8.4

11.0.12 / 2025-04-23
==================

  * PPP-2060 Updated version because of new dependencies

11.0.11 / 2025-04-03
==================

  * PPP-1978 Added integration tests for checking the payload for the capture and refund request

11.0.10 / 2025-03-26
==================

  * PPP-2026 Updated dependencies

11.0.9 / 2025-02-11
==================

  * PPP-1983 Increased version because of new dependencies

11.0.8 / 2025-01-22
==================

  * PPP-1859 Simplified unit tests by using a helper which includes the mocking logic.

11.0.7 / 2024-12-03
==================

  * PPP-1917 Increased version because of new dependencies

11.0.6 / 2024-11-05
==================

  * PPP-1850 Fixed broken capture workflow when the request data is a instance of Laminas\Stdlib\Parameters

11.0.5 / 2024-10-18
==================

  * PPP-316 Added the suffix *Observer to the observer classes
  * PPP-1714 Simplify composer.json files

11.0.4 / 2024-09-26
==================

  * PPP-1637 Readded the ability to enable and disable the file logging in the settings.

11.0.3 / 2024-08-21
==================

  * PPP-1606 Refactor the Logger/Model/Logger class

11.0.2 / 2024-08-12
==================

  * PPP-1604 Updated the version because of new versions of the dependencies

11.0.1 / 2024-07-26
==================

  * PPP-1553 Make the extension compatible with Adobe Commerce app assurance program requirements

11.0.0 / 2024-06-20
==================

  * PPP-1437 Updated the admin UX and changed internally the API credentials handling

10.0.20 / 2024-05-30
==================

  * PPP-1494 PPP-1385 Increased version because of new Klarna dependencies

10.0.19 / 2024-04-24
==================

  * PPP-1391 Added support for Adobe Commerce 2.4.7 and PHP 8.3

10.0.18 / 2024-04-11
==================

  * PPP-1385 Increased version because of new Klarna dependencies

10.0.17 / 2024-03-30
==================

  * PPP-1013 Using instead of \Klarna\Base\Helper\ConfigHelper logic from other classes to get back Klarna specific configuration values.
  * PPP-1312 Adjusted call for sending the plugin version through the API header

10.0.16 / 2024-03-15
==================

  * PPP-1329 Updated the version because new dependencies are used

10.0.15 / 2024-03-04
==================

  * PPP-1298 Increased the version because of dependency updates

10.0.13 / 2024-02-01
==================

  * PPP-1010 Moved \Klarna\Backend\Plugin\PrepareCapture to \Klarna\Backend\Observer\PrepareCapture

10.0.12 / 2024-01-19
==================

  * PPP-1059 Increased version because of a dependency version change

10.0.11 / 2024-01-19
==================

  * PPP-1058 Increased version because of a dependency version change

10.0.10 / 2024-01-05
==================

  * PPP-1008 Improved error display and logging when the Klarna API request can not be sent or failed

10.0.9 / 2023-11-15
==================

  * PPP-929 Increased the version because of a new version of the Base module

10.0.8 / 2023-09-27
==================

  * PPP-772 Increased the version because of new dependency versions in the composer.json file

10.0.7 / 2023-08-25
==================

  * PPP-59 Add m2-klarna package version to User-Agent

10.0.6 / 2023-08-01
==================

  * PPP-575 Increased the version because of new dependency versions in the composer.json file

10.0.5 / 2023-07-14
==================

  * MAGE-4228 Removed the composer caret version range for Klarna dependencies

10.0.4 / 2023-05-22
==================

  * MAGE-4232 Increased the version because of new dependency versions in the composer.json file

10.0.3 / 2023-04-03
==================

  * MAGE-4164 Updated the version

10.0.2 / 2023-03-28
==================

  * MAGE-4162 Added support for PHP 8.2

10.0.1 / 2023-03-28
==================

  * MAGE-4144 Updated the versions

10.0.0 / 2023-03-09
==================

  * MAGE-3890 Removed the notification controller since its not needed anymore
  * MAGE-4037 Prevent to capture a fully captured order
  * MAGE-4068 Do not using anymore in all controllers the parent Magento\Framework\App\Action\Action class
  * MAGE-4075 Removed not needed events
  * MAGE-4077 Added "declare(strict_types=1);" to all production class files
  * MAGE-4087 Moved \Klarna\Base\Model\Api\Parameter to the orderline module and adjusted the calls

9.1.10 / 2022-09-27
==================

  * MAGE-4000 Not using the store value anymore when getting back the orderline instance classes
  * MAGE-4005 Removed not used methods from Klarna\Backend\Model\Api\Rest\Service\Ordermanagement

9.1.9 / 2022-09-14
==================

  * MAGE-3986 Updated the dependencies

9.1.8 / 2022-09-01
==================

  * MAGE-3434 Improved the execution checks in the plugins
  * MAGE-3712 Using constancts instead of magic numbers

9.1.7 / 2022-08-18
==================

  * MAGE-3961 Updated the dependencies

9.1.6 / 2022-08-12
==================

  * MAGE-3876 Reordered translations and set of missing translations
  * MAGE-3910 Updated the copyright text

9.1.5 / 2022-07-11
==================

  * MAGE-3917 Bump version because of updated dependencies

9.1.4 / 2022-06-23
==================

  * MAGE-3873 Bump version because of updated dependencies

9.1.3 / 2022-06-13
==================

  * MAGE-3785 Fix PHP requirements so that it matches the PHP requirement from Magento 2.4.4

9.1.2 / 2022-05-31
==================

  * MAGE-3855 Bump version because of updated dependencies

9.1.1 / 2022-05-09
==================

  * MAGE-3708 Updated the requirements

9.1.0 / 2022-03-01
==================

  * Move from klarna/m2-marketplace

8.2.0 / 2021-09-07
==================

  * MAGE-2956 Using the Klarna base module version 8.3.0

8.1.3 / 2021-08-02
==================

  * MAGE-3291 Fixed big size of the description field by limiting it in the refund request

8.1.2 / 2021-07-07
==================

  * MAGE-3245 Fix orderline cache issue on batch processing of orders on ordermanagement actions for customized solutions 

8.1.1 / 2021-04-22
==================

  * MAGE-2875 Fix credentials issue on batch processing of orders on ordermanagement actions for customized solutions

8.1.0 / 2021-03-09
==================

  * MAGE-2727 Add support for Logs++

8.0.1 / 2020-08-12
==================

  * MAGE-2055 Add support for PHP 7.4

8.0.0 / 2020-04-23
==================

  * MAGE-1829 Move the KCO observer logic from the Backend module to the KCO module
  * MAGE-1839 Add support for reference field in capture and refund calls

7.0.1 / 2020-04-17
==================

  * MAGE-1994 Fix logging when canceling an order for KCO

7.0.0 / 2019-11-18
==================

  * Rename module to "module-backend" and update namespaces
  * MAGE-1520 Enable PHP 7.3 support
  * MAGE-1531 Fix new Magento Coding Standards changes

6.1.0 / 2019-06-19
==================

  * MAGE-692 Completed translations for all phrases. Covering da_DK, de_AT, de_DE, fi_FI, nl_NL, nb_NO and sv_SE

6.0.1 / 2019-03-26
==================

  * MAGE-312 Add missing translations to en_US base

6.0.0 / 2019-02-22
==================

  * MAGE-245 Removed unused input parameter

6.0.0-alpha / 2019-02-05
========================

  * MAGE-251 Switch coding standard to Marketplace one
  * MAGE-308 Remove cancellation of the Magento order since its already cancelled when event is called
  * PPI-545 Refactor abstract class AbstractLine
  * PPI-546 Refactor abstract class Model\Api\Builder
  * PPI-561 Update composer requirements
  * PPI-562 Refactor: Logging

5.1.0 / 2018-10-17
==================

  * PPI-63: Adding a description to the refund call if we have no items but a description.
  * PPI-260 Changed the method visibility from "protected" to "public"
  * PPI-260 Fixed char line limit of 120.
  * PPI-260 Ported from the om module
  * PPI-500 Add support for PHP 7.2
  * PPI-560 Remove html tag in error msg
  * PPI-563 Fix incorrect Store returned when cancel klarna order which does not exists in Magento

5.0.0 / 2018-08-14
==================

  * Rename module and namespace due to Marketplace limitations
  * PI-331 Trunate shipping tracking number and company name if too long
  * PPI-419 Move functionality from DACH module
  * PPI-449 Feedback from Magento for 2.2.6 release
  * PPI-468 Fix Invoice fails with no tracking info

4.3.0 / 2018-06-26
==================

  * PI-91 Add support for passing shipping details in capture request

4.2.0 / 2018-06-08
==================

  * PPI-372 Add support for FRAUD_STOPPED

4.1.3 / 2018-05-14
==================

  * PPI-390 Change post check to return 404 instead of exception
  * PPI-394 Handle for order being a null

4.1.2 / 2018-04-26
==================

  * PPI-390 Fix getting location from header

4.1.1 / 2018-04-20
==================

  * Fix push notifications failing

4.1.0 / 2018-04-09
==================

  * Combine all CHANGELOG entries related to CBE program
  * Fix rejected orders
  * Fix payment method compare
  * Fix order capture/refund from Improove fixes
  * Add replace line to composer.json to replace module-om

3.0.7 / 2018-03-28
==================

  * Change 420 back to 500 in Notification action

3.0.6 / 2018-03-14
==================

  * Change response code back to 500 since 420 is an invalid response code

3.0.5 / 2018-03-14
==================

  * Fix missing dependency

3.0.4 / 2018-03-08
==================

  * Change 500 error responses to other codes due to Varnish bug causing redirect loop
  * Remove using getPayload for logging context

3.0.3 / 2018-02-13
==================

  * Fix code style according to Bundle Extension Program Feedback from 13FEB

3.0.2 / 2018-02-12
==================

  * Bundled Extension Program Feedback from 2018-02-12

3.0.1 / 2018-02-09
==================

  * Fix method signature

3.0.0 / 2017-10-30
==================

  * Fix handling of location during captures
  * Remove json wrapping as it is now handled in Service class
  * Update to 3.0 of klarna/module-kco-core

2.3.3 / 2017-10-04
==================

  * Bump version in modules.xml for new way of getting module versions

2.3.2 / 2017-09-28
==================

  * Remove dependencies that are handled by klarna/module-kco-core module

2.3.1 / 2017-09-18
==================

  * Exclude tests as well as Tests from composer package

2.3.0 / 2017-09-11
==================

  * Refactor code to non-standard directory structure to make Magento Marketplace happy 😢

2.2.3 / 2017-08-30
==================

  * Update code with fixes from MEQP2 in preparation for Marketplace release

2.2.2 / 2017-08-22
==================

  * Remove require-dev section as it is handled in core module

2.2.1 / 2017-08-08
==================

  * Add canceling of Magento order, resetting of quote, and additional logging to cancel observer
  * Add reason message to observer events

2.2.0 / 2017-08-04
==================

  * Inspect response from acknowledge call

2.1.0 / 2017-08-03
==================

  * Change error messaging around order not found
  * Add version to composer.json file
  * Add 'Update Payment Status' button to orders in 'payment_review' status

2.0.5 / 2017-07-31
==================

  * Change caching strategy to better handle for batch invoicing

2.0.4 / 2017-06-27
==================

  * Update name from Klarna AB to Klarna Bank AB (publ)

2.0.3 / 2017-06-08
==================

  * Change to pass correct store to order line collector to ensure correct classes are used
  * Add reference to KP module to suggest list now that it is released

2.0.2 / 2017-05-23
==================

  * Don't add OM related URLs to v2 API calls as they are already added
  * Add additional logging to cancel observer

2.0.1 / 2017-05-15
==================

  * Change notifcation controller to always return JSON
  * Properly handle notifications for each payment method

2.0.0 / 2017-05-01
==================

  * Move OM references to OM module
  * Move initialize method to Kco module
  * Add support for cancel after invoice (release-auth)
  * Adjust error message to be more concise when 'order not found' occurs
  * Ensure correct logger is injected
  * Fix cancel observer to better handle for pushqueues in Kred
  * Fix tests directory in composer.json
  * Update license header
  * Add method_code to calls to get correct Builder
  * Add update from M1 module
  * Allow overriding response code for push notifications
  * Add Magento Edition to version string
  * Changes to support KP
  * Change OM to dynamically create builder class
  * Add setBuilderType method
  * Update dependency requirements to 2.0
  * Move setting of correct OM to calling function
  * Change code to pull composer package version for UserAgent
  * Change event prefix from kco to klarna
  * Refactor to allow reading order_id from request body when it isn't provided as query parameter
  * Update copyright years
  * Remove references to unused class
  * Change to allow KP for payment method
  * Change user-agent to report as OM instead of KCO_OM
  * Change route URL from kco to klarna to make more generic
  * Fix call to getReservationId()
  * Relocate quote to kco module
  * Remove unneeded preference as it is handled in core module
  * Remove dependencies on kco module
  * Change logic for cancel observer to handle for Kred vs Kasper
  * Add call to set user-agent.  Bump required version of core
  * Add CHANGELOG.md

1.0.2 / 2017-01-13
==================

  * Code cleanup
  * Change StoreInterface to StoreManagerInterface in constructor to solve for 2.1.3 issues
  * Add gitattributes file to exclude items from composer packages
  * Fix cancel request to use reservation ID

1.0.1 / 2016-11-07
==================

  * Bug fix for order not found in Magento issue
  * Reduce number of packages included in dependencies as some are already required in KCO module

1.0.0 / 2016-10-31
==================

  * Initial Commit
