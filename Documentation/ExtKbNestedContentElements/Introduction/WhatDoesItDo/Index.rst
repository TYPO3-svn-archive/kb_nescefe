

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


What does it do?
^^^^^^^^^^^^^^^^

This extension allows you to create content elements which act as containers
for other content element. This is similar to the FlexibleContentElement (FCE)
approach of TemplaVoila only that no Flexforms or XML is involved. It can also
get compared to **gridelements** or Flux content element columns.

No need for weird TypoScript template grid syntax ...
"""""""""""""""""""""""""""""""""""""""""""""""""""""
 
Creating new frontend (or backend) templates is as easy as editing a text file.
The **templates follow the fluid syntax** and use a handful of custom kb\_nescefe
view helpers for rendering the backend column headers or the frontend column
content.

You can either choose to use any of the supplied backend templates like "TwoColumns"
or "ThreeColumns" or you can easily create your own. For the frontend there are
also examples supplied: Two old-school templates and additionally two
**Bootstrap template examples** which give you a 50:50 two column or a 33:33:33 three
column layout in the frontend.

Long term support
"""""""""""""""""

This new  **version 2.0.0** was remade for TYPO3 6.2.x almost completely from
scratch. Altough quite some code has been reused (i.e. DataHandler hook). This new
version uses extbase where it makes sense and is based on modern TYPO3 standards.
The extension will not work with TYPO3 4.5 LTS.

The extension works properly when multiple languages get used and
previous versions ran on TYPO3 3.8.0 / 3.8.1 / 4.0.0 / 4.0.1 / 4.5 LTS and
probably even on versions before 3.8.0. The extension is developed since
2007 so you can look back on almost **8 years development** of nested content elements!

Additionally there are functional unit tests for every major aspect of content
element handling and if bugs get reported additional unit tests will get written
while fixing those bugs.

