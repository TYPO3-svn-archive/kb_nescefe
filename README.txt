

Step 1.
-------
Create an "Container template" record in any page or sys-folder ... this will
store your template config.

Fill out the formular with the relative paths "/fileadmin/..." to your BE and
FE templates.

The values for the example templates are:

2columns:

FE-Template
EXT:kb_nescefe/res/2cols.html

BE-Template
EXT:kb_nescefe/res/be_2cols.html



variable amount of horizontal columns:

FE-Template
EXT:kb_nescefe/res/horiz_cols.html

BE-Template
EXT:kb_nescefe/res/be_horiz_cols.html


Step 2.
-------
Then insert the following Page TS-Config on your root-page:

TCEFORM.tt_content.container {
  PAGE_TSCONFIG_ID = ###UID_OF_CREATED_FOLDER_WITH_TEMPLATES_GOES_HERE###
}

of course replace the ###UID... marker with the UID of the page in which you
created the "Container template" records ....


Step 3.
-------
Then just go to some content-page and create a content-element. From the
pulldown box where you can select the type (Text, Text w. Image, etc.) select:
"Content elements container".

Choose a template you wish to use and then save and exit the record.


Step 4.
-------
In the Web>Page module you will be able to fill the container with content
elements.


Step 5.
-------
View the results in the FE.



------------------------------------------------
See the example templates in the extensions res/ folder on how to create a
template.

<!-- ###SECTION_x### --> markers can get nested ...




REQUIRED TS-Config:
-------------------

TCEFORM.tt_content.container {
  PAGE_TSCONFIG_ID = ###UID_OF_CREATED_FOLDER_WITH_TEMPLATES_GOES_HERE###
}


Example TS-Config:
------------------

mod.tx_kbnescefe {
	labels {	

			# x = UID of Template
		x.y = Bla

			# 0 = ###CONTENT_0### / ###HEADER_0###
		1.0 = Testspalte Links

			# 1 = ###CONTENT_1### / ###HEADER_1###
		1.1 = Testspalte Rechts

			# __0_0 = __x_y = ###HEADER_0###/###CONTENT_0###(y) of Section 0 (x)
		2.__0_0 = Tabellenspalte ###IDX###

			# 0 = ###SECTION_0###
		2.0 = Hauptsektion
		}
}

If _0 or _1 gets used for a section you must not use the same index for a content
column on the same level. Else the labels would get messed up.



