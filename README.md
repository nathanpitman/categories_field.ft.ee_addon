categories_field.ft.ee_addon
============================

Categories Field is an ExpressionEngine 2.x fieldtype which can be used to save category IDs to a custom channel entries field either in addition to those stored by native categories or as a replacement with a more flexibile interface.

*Available settings:*

**Category Groups:** You can either use one field to manage all categories across all groups or split the groups between seperate custom fields.

**Category Filter:** Useful when you have very large category groups or multiple category groups selected. Provides a live as you type filter based on category name.

**Category filter placeholder text:** You can customise the placeholder text which appears inside the filter.

**Exclude Parents from Filter:** If enabled excludes top level parents from the filter, i.e. they remain visible at all times.

**Field Delimiter:** The character which is used to delimit the string of category IDs that are stored in ths custom field. i.e. "|"

**Field Wrapper:** If specified will wrap the string of delimited values in the specified character. i.e. "1|2|3" would become "|1|2|3|" if a wrapper of "|" was specified.

**Auto Assign Parents:** Will search out parents of selected categories and automatically assign their parents also.

**Primary Category Assignment:** If enabled allows you to record a "Primary Category" for a field.

**Sync with Native Categories:** If enabled will mirror native category selections (for the specified category group only - assuming that the category group is assigned to the channel where this field appears).

**Highlight Assigned Native Categories:** Useful if you want to be able to easily refer to the last saved native category assignments for an entry in a field which is not set to be synced.

This add-on was inspired by Matrix Cat Col (https://github.com/jkoivisto/matrix_cat_col), guided by the work Low has done on Low Freeform Field (https://github.com/low/low_freeform_field) and cribs some UI elements from Pixel & Tonic Matrix (http://devot-ee.com/add-ons/matrix).
