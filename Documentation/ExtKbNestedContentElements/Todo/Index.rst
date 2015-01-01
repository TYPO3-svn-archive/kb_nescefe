

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

.. _ref-todo:

Todo's
------

- Reimplement sections in 2.0.0 as they were possible with older
  versions of kb\_nescefe

- Eventually alter the DataHandler hook to use the ContentRepository
  instead of BackendUtility::getRecord

- Split the "Context" objects into a "pageContext" (renderContext),
  containerContext and elementContext and eventually nest them. The
  context objects could also inherit from a common AbstractContext
  object as some method defined in the interface are/will-be most
  likely the same in frontend/backend. Indeed the context objects
  are somehow some kind of domain model objects (DTO, Data transfer
  objects). Only that they do not need to, or even must not, get persisted.
  But they should really get split up according to the application
  context usage model. Compared to other kind of DTO's a context object
  will/should never get persistet. Their intention is to be just valid
  during a single execution.

- Does drag&drop for non-container/non-contained elements not work any more?
  No: This is a TYPO3 issue. Drag&Drop seems to be disabled in language mode. Why???

