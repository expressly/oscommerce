<?php

/**
 *
 *
 * Activation steps:
 * 1) Go to Admin Panel > Sidebar Menu > Modules > Content
 * 2) Set enabled "Expressly Banner"
 */
class cm_cs_expressly_banner
{
    var $code;
    var $group;
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    /**
     *
     */
    function cm_cs_expressly_banner()
    {
        $this->code  = get_class($this);
        $this->group = basename(dirname(__FILE__));

        $this->title       = MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_TITLE;
        $this->description = MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_DESCRIPTION;

        if ( defined('MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_STATUS') ) {
            $this->sort_order = MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_SORT_ORDER;
            $this->enabled    = (MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_STATUS == 'True');
        }
    }

    /**
     *
     */
    function execute()
    {
        global $oscTemplate, $customer_id;

        /**
         * @var $merchant
         * @var $dispatcher
         * @var $logger
         */
        require_once DIR_FS_CATALOG . 'includes/apps/expressly/expressly.php';
        $email = '';

        if ( tep_session_is_registered('customer_id') ) {

            $email_query = tep_db_query("select customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . intval($customer_id) . "'");

            $email = tep_db_fetch_array($email_query);
            $email = $email['customers_email_address'];

        }

        $event = new Expressly\Event\BannerEvent($merchant, $email);
        try {

            $dispatcher->dispatch(Expressly\Subscriber\BannerSubscriber::BANNER_REQUEST, $event);

            $content = $event->getContent();

            if (!$event->isSuccessful()) {
                throw new Expressly\Exception\GenericException($content['message']);
            }
        } catch (Buzz\Exception\RequestException $e) {
            $logger->error(Expressly\Exception\ExceptionFormatter::format($e));
        } catch (\Exception $e) {
            $logger->error(Expressly\Exception\ExceptionFormatter::format($e));
        }

        ob_start();
        echo Expressly\Helper\BannerHelper::toHtml($event);
        $template = ob_get_clean();

        $oscTemplate->addContent(($template ? '<div class="contentText">'.$template.'</div>' : ''), $this->group);
    }

    /**
     * @return bool
     */
    function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return bool
     */
    function check()
    {
        return defined('MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_STATUS');
    }

    /**
     *
     */
    function install()
    {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Expressly Banner Module', 'MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_STATUS', 'True', 'Should the expressly banner block be shown on the checkout success page?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    /**
     *
     */
    function remove()
    {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    /**
     * @return array
     */
    function keys()
    {
        return array('MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_STATUS', 'MODULE_CONTENT_CHECKOUT_SUCCESS_EXPRESSLY_BANNER_SORT_ORDER');
    }
}
