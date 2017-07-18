<?php
  class ht_expressly_trigger {
    var $code = 'ht_expressly_trigger';
    var $group = 'footer_scripts';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function ht_expressly_trigger() {
      $this->title = MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_TITLE;
      $this->description = MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_DESCRIPTION;

      if ( defined('MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_STATUS') ) {
        $this->sort_order = MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_SORT_ORDER;
        $this->enabled = (MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_STATUS == 'True');
      }
    }

    function execute() {
      global $oscTemplate;

      if (MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_JS_PLACEMENT != 'Footer') {
        $this->group = 'header_tags';
      }

      $output = '<script type=\'text/javascript\' src=\'https://assets01.buyexpressly.com/lightbox/trigger-v2.min.js\' async></script>';
      $oscTemplate->addBlock($output, $this->group);
    }

    function isEnabled() {
      return $this->enabled;
    }

    function check() {
      return defined('MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_STATUS');
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Expressly Trigger Module', 'MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_STATUS', 'True', 'Do you want to enable the Expressly migration trigger?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Javascript Placement', 'MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_JS_PLACEMENT', 'Header', 'Should the Expressly migration trigger javascript be loaded in the header or footer?', '6', '1', 'tep_cfg_select_option(array(\'Header\', \'Footer\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_STATUS', 'MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_JS_PLACEMENT', 'MODULE_HEADER_TAGS_EXPRESSLY_TRIGGER_SORT_ORDER');
    }
  }
?>
