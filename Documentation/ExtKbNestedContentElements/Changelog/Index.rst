
.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Changelog
---------

+---------+----------------------+-------------------------------------------------------+
| Version | Date                 | Changelog                                             |
+=========+======================+=======================================================+
| 0.0.1   | 2007-10-02 14:00 CET | - Initial release                                     |
+---------+----------------------+-------------------------------------------------------+
| 0.0.2   | 2008-02-28 22:45 CET | - Removed a debug "print\_r"                          |
+---------+----------------------+-------------------------------------------------------+
| 0.0.3   | 2009-01-23 12:30 CET | - Fixed a bug with content-slide (THX to Georg Ringer |
|         |                      |   for reporting this)                                 |
+---------+----------------------+-------------------------------------------------------+
| 0.0.4   | 2009-01-23 13:45 CET | - Added icon for "Container Element" in TCEforms      |
|         |                      |   CType dropdown.                                     |
|         |                      | - Added Icon in "New content element wizard"          |
+---------+----------------------+-------------------------------------------------------+
| 0.0.5   | 2009-03-06 15:30 CET | - Fixed an issue: Extension was not compatible to     |
|         |                      |   PHP4 because of one "public" and one "protected"    |
|         |                      |   keyword. Thanks to Paul Klimek for reporting this.  |
+---------+----------------------+-------------------------------------------------------+
| 1.0.0   | 2010-02-09 15:00 CET | - Raised extension state to "stable"                  |
|         |                      | - Change of major version to 1.0.0                    |
|         |                      | - The position of an element in the container is now  |
|         |                      |   stored in a separated field "parentPosition"        |
|         |                      |   instead of misusing the "colPos" field              |
|         |                      | - Now works with TYPO3 version >= 4.3.0               |
+---------+----------------------+-------------------------------------------------------+
| 1.0.1   | 2010-02-11 9:45 CET  | - Created an update wizard which allows to update     |
|         |                      |   systems using kb\_nescefe < 1.0.0 to the current    |
|         |                      |   version (See documentation)                         |
|         |                      | - Fixed a bug concerning creation of translated       |
|         |                      |   containers                                          |
|         |                      | - The colPos value being set for elements inside      |
|         |                      |   containers is now configurable from within the      |
|         |                      |   extension manager                                   |
+---------+----------------------+-------------------------------------------------------+
| 1.0.2   | 2010-02-15 12:00 CET | - Removed static templates and included minimal       |
|         |                      |   required TypoScript by default.                     |
|         |                      | - Added TypoScript top level object "tt\_content" as  |
|         |                      |   default renderObj                                   |
+---------+----------------------+-------------------------------------------------------+
| 1.0.3   | 2010-05-10 12:00 CET | - Fixed a bug: Double container element header output |
|         |                      |   in FE                                               |
|         |                      | - Fixed a bug: When a page containing content         |
|         |                      |   elements got copied and recursiveCopy is active,    |
|         |                      |   sub elements were not referenced correctly (THX to  |
|         |                      |   Erich Reitmeier and Igor Reitmair for reporting     |
|         |                      |   this bug and Igor Reitmeir for sponsoring the       |
|         |                      |   extension)                                          |
+---------+----------------------+-------------------------------------------------------+
| 1.1.1   | 2010-07-23 10:30 CET | - kb\_nescefe containers can now get properly         |
|         |                      |   exported / imported along with all content elements |
|         |                      |   inside them (Sponsored by bluemars.net)             |
|         |                      | - Major rewrite of tcemain hook class and clipboard   |
|         |                      |   handling                                            |
|         |                      | - Updated to work properly with TYPO3 version 4.4 and |
|         |                      |   later and fixed an issue with backwards             |
|         |                      |   compatibility to branch 4.2                         |
+---------+----------------------+-------------------------------------------------------+
| 1.1.2   | 2012-07-13 21:00 CET | - Fixed issue with clipboard XCLASS:                  |
+---------+----------------------+-------------------------------------------------------+
| 2.0.0   | 2015-01-01 18:00 CET | - Updated for TYPO3 6.2 compatibility                 |
|         |                      | - Major rewrite of many classes using extbase         |
|         |                      | - Use fluid templating engine                         |
|         |                      | - Whole change-of-concept for template handling       |
|         |                      | - Creation of functional unit tests for content       |
|         |                      |   element operations (DataHandler actions)            |
+---------+----------------------+-------------------------------------------------------+
| 2.0.1   | 2015-01-07 12:00 CET | - [BUGFIX] Frontend didn't render properly            |
+---------+----------------------+-------------------------------------------------------+
| 2.0.2   | 2015-01-12 21:45 CET | - [BUGFIX] Error when not layout template selected    |
+---------+----------------------+-------------------------------------------------------+

.. note:: If you find any bug please report them to office@think-open.at or at the
   issue tracker on TYPO3 forge: https://forge.typo3.org/projects/extension-kb\_nescefe/issues

