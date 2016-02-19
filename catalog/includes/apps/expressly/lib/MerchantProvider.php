<?php

namespace Expressly\Lib;

use Expressly\Entity\Merchant;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Provider\MerchantProviderInterface;
use Pimple\Container;
use Silex\Application;

class MerchantProvider implements MerchantProviderInterface
{
    private $app;
    private $merchant;

    const APIKEY = 'OSCOM_APP_EXPRESSLY_APIKEY';
    const PATH = 'OSCOM_APP_EXPRESSLY_PATH';
    const HOST = 'OSCOM_APP_EXPRESSLY_HOST';

    public function __construct(Container $app)
    {
        $this->app = $app;

        $merchant = new Merchant();
        $merchant
            ->setApiKey($this->getParameter(self::APIKEY))
            ->setPath($this->getParameter(self::PATH))
            ->setHost($this->getParameter(self::HOST));

        $this->merchant = $merchant;
    }

    private function getParameter($key, $count = false)
    {
        $query = tep_db_query(
            sprintf(
                'SELECT COALESCE(`configuration_value`, "") AS `configuration_value` FROM %s WHERE `configuration_key`="%s" LIMIT 1;',
                TABLE_CONFIGURATION,
                $key
            )
        );

        if ($count) {
            return tep_db_num_rows($query);
        }

        $results = tep_db_fetch_array($query);

        return $results['configuration_value'];
    }

    public function getMerchant()
    {
        return $this->merchant;
    }

    public function setMerchant(Merchant $merchant)
    {
        $values = array(
            self::APIKEY => $merchant->getApiKey(),
            self::PATH => $merchant->getPath(),
            self::HOST => $merchant->getHost()
        );

        foreach ($values as $key => $value) {
            $this->saveParameter($key, $value);
        }

        $this->merchant = $merchant;

        return $this;
    }

    // TODO: use application wide define() statements instead

    private function saveParameter($key, $value)
    {
        try {
            $result = $this->getParameter($key, true);
            if (!empty($result)) {
                tep_db_query(
                    sprintf(
                        'UPDATE %s SET `configuration_value`="%s" WHERE `configuration_key`="%s";',
                        TABLE_CONFIGURATION,
                        tep_db_input($value),
                        tep_db_input($key)
                    )
                );
            } else {
                tep_db_query(
                    sprintf(
                        'INSERT INTO %s (`configuration_title`, `configuration_value`, `configuration_key`, `configuration_description`, `configuration_group_id`, `sort_order`) VALUES ("%s", "%s", "%s", "", 0, 6);',
                        TABLE_CONFIGURATION,
                        'Expressly Preferences',
                        tep_db_input($value),
                        tep_db_input($key)
                    )
                );
            }
        } catch (\Exception $e) {
            $this->app['logger']->error(ExceptionFormatter::format($e));

            return false;
        }

        return true;
    }
}