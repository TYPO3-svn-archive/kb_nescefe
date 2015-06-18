

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


Adminstration
-------------

To install the extension just install it as usual using the Extension
Manager. While you are installing the extension you have some options
by which you can configure the behaviour of the extension.

- **Copy recursive [copyRecursive]** This option determines wheter you
  want that the content elements inside a containers to get copied
  together with their container – if this option is not set a container
  will always be empty after copy. Please take notice that this is different
  if a complete page gets copied. In this case the elements inside a container
  will get copied along regardless of this setting.

- **Localize recursive [localizeRecursive]** This option determines wheter you
  want that the content elements inside a containers to get localized
  together with their container. If this option is not set a container
  will always be empty after localization.

- **Templates on normal pages [templatesOnPages]** When this option is
  set you can create kb\_nescefe layout templates not only on Sys-Folder
  pages but also on normal content pages. This is a nice feature when
  you are making imports/exports of content elements as you can include
  the template records in the export without having to add the template
  records container page. After having made an import on the target
  system you can simply move the included template records to a Sys-
  Folder on the target system.

- **Template record as soft reference [templateSoftReference]** This
  option will let the layout template select box of content elements be a soft-reference.
  This means when you are doing export/imports of kb\_nescefe
  content elements the select box which you use for selecting the
  template will be a soft-reference which allows you to make it editable
  on export. So when you are doing the import you can fill in the uid of
  the corresponding template record on the target system in the import
  form.

- **Container Element colPos [containerElementColPos]** This option
  allows you to set the colPos number which will get used for the
  container elements. This value is preset to a value of 10. You should
  set this number to a column number which is not shown in the page
  module. Thus the elements inside the containers will not get shown in
  another page-module column again, but only inside the containers. You have the
  option to change this as there might be people which use more than 10
  columns (altough this would be a very rare case).

.. warning:: This extension does currently not work properly for multilingual
   sites. For getting this feature to work a patch of the TYPO3 core is required.
   The patch has already been submitted to review.typo3.org for review. If you
   need this feature help developing kb_nescefe, try out the patch and give your comments:
   https://review.typo3.org/#/c/33485/

.. toctree::
   :maxdepth: 5
   :titlesonly:
   :glob:

   UpdateFromPreviousVersions/Index

