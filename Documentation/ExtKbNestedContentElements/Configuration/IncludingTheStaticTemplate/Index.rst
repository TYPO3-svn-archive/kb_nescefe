

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


Including the static template
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

**NOTICE** : This is neither required nor possible any more in
versions above and including 1.0.2. Instead the default TypoScript
configuration is directly set from within ext\_localconf.php. In version
2.0.0 the TypoScript configuration (and so the extbase configuration)
gets automatically included from the ext\_typoscript\_setup.txt file.
So just skip this step if you use a current version of kb\_nescefe.

In previous versions of the extension (<1.0.2) you had to edit the
TS-Template where you have included i.e. the static-template of CSS
styled content. There you have to also include the
static template coming with kb\_nescefe. This is similar like you have
to include the static template of tt\_news.


