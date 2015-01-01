

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

.. _ref-configuration:

Configuration
-------------

To set up the extension after installation and configuration these two steps are required:

- **Create a layout template**
  You need to set up a template which defines how containers shall get rendered in the BE and the FE

- **Set up the layout template folder**
  Your site needs to know where your layout templates are stored. For a single site in a TYPO3 instance
  it doesn't make quite a difference but if you have multiple sites in a single TYPO3 instance it can
  be quite important to have different layout templates for each site.

These two steps get explained in detail in the following chapter.

There is also a sub-chapter about including the static template. But this step is
not required any more with recent versions of kb\_nescefe so you can skip it.

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   CreatingLayoutTemplates/Index
   SettingUpTheLayoutTemplateFolder/Index
   IncludingTheStaticTemplate/Index

