NOTE: This repo is a legacy Drupal 7.x module and is no longer maintained or considered relevant. It was originally hosted here:

  - https://www.drupal.org/sandbox/kpander/2042421

---

Form Tweaks module
--------------------
The intent of this module is to provide a way to simplify forms by hiding 
certain form fields. There is no UI for this module. Form tweaks are configured 
through a variable setting.

The application of the form element hiding can be controlled by form and user
roles. This can apply to all forms and/or user roles, individual ones. When
rules apply to all forms or user roles, exceptions can also be defined.

The individual configurations are set by providing the form id to affect as well 
as the user roles to affect.

A set of predefined items are available for hiding common form elements. For
form elements not in predefined list, arbitrary form fields can be specified
directly within the configuration (see 'Defining Your Own Elements' below).


How to Use:
-----------
Define the different form fields to hide within your settings.php file. The 
structure is an associative array, of arrays. For example:

  // Define the container for the configuration items.
  $conf['form_tweaks_config'] = array();

  // On all forms, for all users, hide the 'Authoring Information' fieldset and
  // the Menu Link: Weight field. However, do NOT hide these when we're showing
  // the 'article_node_form' form.
  $conf['form_tweaks_config'][] = array(
    'roles' => array('all'),
    'forms' => array('all'),
    'excluded forms' => array('article_node_form'),
    'elements' => array(
      'node-author-fieldset',
      'node-menu-link-weight',
    ),
  );

  // On the 'news_node_form', only for users with the 'authenticated user' role,
  // hide the 'Promote to front page' field and the 'Make sticky' field.
  $conf['form_tweaks_config'][] = array(
    'roles' => array('authenticated user'),
    'forms' => array('news_node_form'),
    'elements' => array(
      'node-options-promote',
      'node-options-sticky',
    ),
  );

  // On the 'news_node_form', for all users except those with the 'editor' role,
  // hide the URL alias fieldset.
  $conf['form_tweaks_config'][] = array(
    'roles' => array('all'),
    'excluded roles' => array('editor'),
    'forms' => array('news_node_form'),
    'elements' => array(
      'node-path-fieldset',
    ),
  );


You can add as many rules as needed.


Form Element Options
--------------------
These are the predefined form elements you can reference in your $conf[] array:

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

  language-fieldset           The Language fieldset

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
  file-destination-fieldset   File destination fieldset
  file-path-fieldset          URL Alias fieldset
  file-user-fieldset          Associated user fieldset


These apply to the block configuration form (form_id: block_admin_configure):

  Element                     Hides
  --------------------------  --------------------------------
  block-description           The block description (only used internally)
  block-regions               The Region Settings fieldset
  block-visibility-title      The 'Visibility Settings' title
  block-visibility-fieldset   All options within the 'Visibility Settings' group



Defining Your Own Elements
--------------------------
If you need to hide a form element that isn't defined in the above list, you
can do so by providing the form key(s) to hide directly within the Form Tweaks
configuration.

So, if we had a $form definition like this:

$form = array(
  'metatags' => array(
    'title' => 'some title',
    'advanced' => array(
      'type' => 'fieldset',
      'some-key' => array(
        etc.
      ),
    ),
  ),
);

... and we wanted to hide the 'advanced' fieldset, we could define the path
to that form element like this:

  $conf['form_tweaks_config'][] = array(
    'roles' => array('all'),
    'forms' => array('all'),
    'elements' => array(
      'DEFINED:hide-the-metatags=metatags,advanced',
    ),
  );

The expected format is:
  DEFINED:<a-unique-name-for-this-definition>=form_key1,form_key2,etc

e.g.,
  DEFINED:xmlsitemap-fieldset=xmlsitemap
  DEFINED:metatags-advanced-fieldset=metatags,advanced





