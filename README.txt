Form Tweaks module
--------------------
Intent is to simplify forms by hiding certain form fields. There is no UI for
this module. Form tweaks are configured through a variable setting.

The individual configurations are set by providing the form id to affect as well 
as the user roles to affect.


How to Use:
-----------
Define the different form fields to hide within your settings.php file. The 
structure is an associative array, of arrays. The key is the role name to 
affect, the value is an array of form elements to hide. If the form element
is an array, the first value is the form element, the second is the specific
form_id to hide it on. 

e.g.,

$conf['form_tweaks_config'] = array(
  'all' => array(
    'node-author-fieldset',
    'node-menu-link-weight',
  ),
  'authenticated user' => array(
    'node-options-promote',
    array('node-options-sticky', 'news_node_form'),
  ),
);

What the above example will do is:
- Hide the 'Authoring Information' fieldset for all users, on all node forms
- Hide the 'Menu settings/Weight' field for all users, on all node forms
- Hide the 'Publishing options/Promote to front page' field for users with the
  'authenticated user' role, on all forms
- Hide the 'Publishing options/Sticky at top of lists' field for users with the
  'authenticated user' role, only on forms with the form_id 'news_node_form'


Form Element Options
--------------------
These are the available form elements you can reference in your $conf[] array:

These apply to all node forms (form_id: *_node_form):

  Element                     Hides
  --------------------------  --------------------------------
  node-title                  Node title field
  node-button-preview         Node 'preview' button

  node-revisions-fieldset     Revision information fieldset
  node-revisions-add-new      Revision information/Create new revision checkbox
  node-revisions-log-message  Revision information/Revision log message textarea

  node-options-fieldset       Publishing options fieldset
  node-options-published      Publishing options/Published checkbox
  node-options-promote        Publishing options/Promote to front page checkbox
  node-options-sticky         Publishing options/Sticky at top of lists checkbox

  node-author-fieldset        Authoring information fieldset
  node-author-by              Authoring information/Authored by textfield
  node-author-date            Authoring information/Authored on textfield

  node-path-fieldset          URL path settings fieldset

  node-menu-fieldset          Menu settings fieldset
  node-menu-link-description  Menu settings/Description textarea
  node-menu-link-weight       Menu settings/Weight select box
  node-menu-link-parent       Menu settings/Parent item select box

  The following options are hidden by using css:
  node-input-filter-csshide   Node body Text Format select box and help text
  node-edit-summary-csshide   Node body 'Edit summary' link and teaser textarea
  node-menu-link-csshide      Menu settings/Provide a menu link checkbox
  node-menu-title-csshide     Menu settings/Menu link title textfield
  node-menu-parent-csshide    Menu settings/Parent item select box

  node-revisions-fieldset-csshide Menu settings fieldset
  node-options-fieldset-csshide   Menu settings fieldset
  node-author-fieldset-csshide    Menu settings fieldset
  node-path-fieldset-csshide      Menu settings fieldset
  node-menu-fieldset-csshide      Menu settings fieldset


The 'hide by css' options (*-csshide) are useful because they still allow
programmatic configuration of the form elements while (visually) hiding them
from the user. This is necessary in some cases. E.g., if you want to define a
menu setting programmatically but don't want the user to see the fieldset, you
need to use 'node-menu-fieldset-csshide' for the programmatic configuration to
take effect.


These apply to the file edit form (form_id: file_entity_edit):

  Element                     Hides
  --------------------------  --------------------------------
  file-filename               Name (the file's filename)
  file-preview                Preview (a link to the filename)
  file-submit                 Save the file (button)
  file-delete                 Delete the file (button)
  file-cancel                 Cancel the file edit form submission (link)




