

Step 1.
-------
Create an "Layout template" record in any page or sys-folder.

Fill out the form with the relative paths "/fileadmin/..." to your BE and
FE templates. You can use the "EXT:" prefix to specify templates in your
custom extension.

The default example templates are:

FE-Template
EXT:kb_nescefe/Resources/Private/Templates/Frontend/TwoColumns.html
or
EXT:kb_nescefe/Resources/Private/Templates/Bootstrap/TwoColumns.html

BE-Template
EXT:kb_nescefe/Resources/Private/Templates/Backend/TwoColumns.html



Step 2.
-------
Insert the following Page TS-Config on your root-page:

TCEFORM.tt_content.kbnescefe_layout {
  PAGE_TSCONFIG_ID = UID_OF_CREATED_FOLDER_WITH_TEMPLATES_GOES_HERE
}

of course replace the UID...GOES_HERE marker with the UID of the page in which you
created the "Layout template" records ....


Step 3.
-------
Then just open up the Web>Page module with some page and create a content element.
From the pulldown box where you can select the type (Text, Text w. Image, etc.)
select "Plugin" and then choose "Content elements container" as plugin type.

Of course you can use the "New content element" wizard which should be enabled
by default. Just select "Nested content element container" from the
"Typical page content" tab.

In the Tab "Plugin" of the content element choose a layout you wish to use from
the field "Layout template" and then save and exit the record.


Step 4.
-------
In the Web>Page module you will be able to fill the container with content
elements.


Step 5.
-------
View the results in the FE.


------------------------------------------------
See the example templates in the extensions folder "Resources/Private/Templates"
on how to create a template.

Sections are not supported currently so the "HorizontalColumns" template will
not work. Feel free to sponsor the reimplementation of this feature.


REQUIRED TS-Config:
-------------------

TCEFORM.tt_content.kbnescefe_layout {
  PAGE_TSCONFIG_ID = ###UID_OF_CREATED_FOLDER_WITH_TEMPLATES_GOES_HERE###
}

