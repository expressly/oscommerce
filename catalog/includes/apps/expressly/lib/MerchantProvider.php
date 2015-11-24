<?php

namespace Expressly\Lib;

use Expressly\Entity\Merchant;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Provider\MerchantProviderInterface;

/**
 *
 */
class MerchantProvider implements MerchantProviderInterface
{
    const APIKEY = 'OSCOM_APP_EXPRESSLY_APIKEY';
    const PATH   = 'OSCOM_APP_EXPRESSLY_PATH';
    const HOST   = 'OSCOM_APP_EXPRESSLY_HOST';

    /**
     * @var \Expressly\Entity\Merchant
     */
    private $merchant;

    /**
     *
     */
    public function __construct()
    {
        if (defined(self::APIKEY)) {
            $this->updateMerchant();
        }
    }

    /**
     *
     */
    private function updateMerchant()
    {
        $merchant = new Merchant();
        $merchant
            ->setApiKey(constant(self::APIKEY))
            ->setPath(constant(self::PATH))
            ->setHost(constant(self::HOST));

        $this->merchant = $merchant;
    }

    /**
     *
     */
    public function setMerchant(Merchant $merchant)
    {
        $this->saveParameter(self::APIKEY, $merchant->getApiKey());
        $this->saveParameter(self::PATH,   $merchant->getPath());
        $this->saveParameter(self::HOST,   $merchant->getHost());

        $this->merchant = $merchant;

        return $this;
    }

    /**
     *
     */
    public function getMerchant($update = false)
    {
        if (!$this->merchant instanceof Merchant || $update) {
            $this->updateMerchant();
        }

        return $this->merchant;
    }

    /**
     * @param string $key
     * @param string $value
     */
    private function saveParameter($key, $value)
    {
        if ( !defined($key) ) {
            tep_db_query(sprintf(
                'INSERT INTO %s (`configuration_title`, `configuration_value`, `configuration_key`, `configuration_description`, `configuration_group_id`, `sort_order`) VALUES ("%s", "%s", "%s", "", 0, 6);',
                TABLE_CONFIGURATION,
                'Expressly Preferences',
                tep_db_input($value),
                tep_db_input($key)
            ));
            define($key, $value);
        } else {
            tep_db_query(sprintf(
                'UPDATE %s SET `configuration_value`="%s" WHERE `configuration_key`="%s";',
                TABLE_CONFIGURATION,
                tep_db_input($value),
                tep_db_input($key)
            ));
        }
    }
}
