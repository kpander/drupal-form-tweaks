<?php
/**
 * @file
 */

class FormTweaks {

  protected $defs;
  protected $config;
  protected $user_roles;

  /**
   * Constructor.
   */
  public function __construct() {
    // Get the form element definitions we can affect.
    static $defs = null;
    if (is_null($defs)) {
      $defs = module_invoke_all('form_tweaks_definitions');
    }
    $this->defs = $defs;
    dpm($defs, 'defs');

    // Get the current defined Form Tweaks configuration.
    $this->config = variable_get(FORM_TWEAKS_CONFIG, array());

    // Record the current user's roles.
    global $user;
    $this->user_roles = $user->roles;
  }

  /**
   * Process Form Tweaks for the given form.
   *
   * We determine which configurations apply to this form, for the current user,
   * and apply them where we find matches.
   *
   * @param assoc array $form
   *   A Drupal form array.
   * @param string $form_id
   *   The form_id of the form we're checking.
   */
  public function process(&$form, $form_id) {
    if (!count($this->config)) {
      // A Form Tweaks configuration wasn't found.
      return;
    }

    foreach ($this->config as $config) {
      if ($this->shouldAffect($form_id, $config)) {
        $this->applyConfig($form, $config['elements']);
      }
    }
  }

  /**
   * Should the given form id be affected by the given configuration?
   *
   * Meaning, does the given configuration apply to the given form id, given
   * the current user's roles?
   *
   * @param string $form_id
   *   The form_id of the form we're checking.
   * @param assoc array $config
   *   A single configuration element for Form Tweaks.
   *
   * @return boolean
   *   TRUE if this configuration applies to this form/user
   *   FALSE if it doesn't
   */
  public function shouldAffect($form_id, $config) {
    // Check Roles.

    // If we've defined roles this applies to, and we haven't applied it to
    // *all* roles, and the current user doesn't have any of the roles defined
    // for this config, then this config doesn't apply to this form.
    if (isset($config['roles'])) {
      if (!in_array('all', $config['roles'])) {
        $matches = array_intersect($this->user_roles, $config['roles']);
        if (count($matches) < 1) {
          return FALSE;
        }
      }
    }

    // If we've explicitly excluded this user role for this element, then this
    // config doesn't apply.
    if (isset($config['excluded roles'])) {
      $matches = array_intersect($this->user_roles, $config['excluded roles']);
      if (count($matches) > 0) {
        return FALSE;
      }
    }
    

    // Check Form IDs.

    // If we've defined forms this applies to, and we haven't applied it to
    // *all* forms, and the current form isn't in the list, then this config
    // doesn't apply to this form.
    if (isset($config['forms'])) {
      if (!in_array('all', $config['forms'])) {
        if (!in_array($form_id, $config['forms'])) {
          return FALSE;
        }
      }
    }

    // If we've explicitly excluded this form for this element, then this
    // config doesn't apply.
    //
    // Note that if we already hid this element in a different rule, this
    // exclusion will NOT override it.
    if (isset($config['excluded forms'])) {
      if (in_array($form_id, $config['excluded forms'])) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Apply the given configuration to the given form.
   *
   * We've already established that this configuration applies to this form,
   * so we just need to alter the defined elements.
   */
  public function applyConfig(&$form, $elements) {
    foreach ($elements as $element) {
      // Ensure we have a field definition for the item we're trying to hide.
      if (!$definition = $this->getDefinitionForElement($element)) {
        drupal_set_message("ERROR: no definition for item: $element");
        continue;
      }
      $hide_via_css = preg_match("/-csshide$/", $element) ? TRUE : FALSE;
      self::hideElement($form, $definition, $hide_via_css);
    }
  }
  
  /**
   * Get the configuration for the given element name.
   *
   * @param string $element
   *   A Form Tweaks element name, that defines a form element to hide.
   *   These elements are provided by hook_form_tweaks_definitions().
   *   e.g., "node-revisions-fieldset", or "node-options-promote"
   *
   * @return array definition for the given element
   * @return boolean FALSE if no definition was found
   */
  public function getDefinitionForElement($element) {
    // Check if we're providing a custom element definition on-the-fly.
    if (preg_match("/^defined:/i", $element)) {
      if ($this->addCustomElement($element)) {
        $element = preg_replace("/^defined:/i", '', $element);
        $element = array_shift(explode('=', $element));
      }
      else {
        // Invalid custom element definition.
        return FALSE;
      }
    }

    foreach ($this->defs as $group => $elements) {
      if (isset($elements[$element])) {
        return $elements[$element];
      }
    }

    return FALSE;
  }

  /**
   * Parse a custom element definition and add it to our library of definitions.
   *
   * The expected format is:
   *   DEFINED:<unique_key>=form_key1,form_key2,etc
   *
   * e.g.,
   *   DEFINED:xmlsitemap-fieldset=xmlsitemap
   *   DEFINED:metatags-advanced-fieldset=metatags,advanced
   */
  public function addCustomElement($element) {
    $element = preg_replace("/^defined:/i", '', $element);
    $items = explode('=', $element);
    if (count($items) != 2) {
      return FALSE;
    }

    if (!isset($this->defs['defined'])) {
      $this->defs['defined'] = array();
    }

    $this->defs['defined'][$items[0]] = explode(',', $items[1]); 
    return TRUE;
  }


  /**
   * Hide the given items in the given form, with the definitions of *how* to
   * hide each item. The definitions may be either form element paths (to which
   * we append an #access = FALSE) or css rules (which we add inline).
   */
  public function hideElement(&$form, $definition, $hide_via_css = FALSE) {
    // There are three possible methods of hiding form items:
    // 1: by setting a field's #access value to FALSE (the default)
    // 2: by applying CSS to hide the element
    // 3: by wrapping display:none code around the element
    //
    // If the definition array has a 'css' key, we use the css method.
    // Otherwise we assume we're disabling #access to a form element.
    
    if (isset($definition['css'])) {
      // We're adding inline css to hide a form element.
      drupal_add_css($definition['css'], array('type' => 'inline'));
    }
    else if (isset($definition['wrap'])) {
    }
    else {
      // We're setting the #access property of a form element to FALSE.
      $key_exists = TRUE;

      // We're going to add an '#access' => FALSE key to an element defined
      // by the field definition. This could be an arbitrary number of levels
      // deep within the form array. 
      //
      // To get to the target key in the form array, sequentially walk deeper 
      // using the keys given in the definition.
      $value = &$form;
      foreach ($definition as $field) {
        if (isset($value[$field])) {
          $value = &$value[$field];
        }
        else {
          // This configured key didn't exist in the form.
          $key_exists = FALSE;
          break;
        }
      }

      if ($key_exists) {
        if ($hide_via_css) {
          // Wrap the element with css 'display:none', because we still need
          // to be able to set/change the element's value, but don't want the
          // user to be able to.
          $value['#prefix'] = '<div style="display:none;">' . (isset($value['prefix']) ? $value['#prefix'] : '');
          $value['#suffix'] = (isset($value['#suffix']) ? $value['#suffix'] : '') . '</div>';
        }
        else {
          // Disable access to the element.
          $value['#access'] = FALSE;
        }
      }
    }
  }

}
