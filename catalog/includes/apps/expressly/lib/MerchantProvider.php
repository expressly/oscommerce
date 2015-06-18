<?php

namespace Expressly\Lib;

use Expressly\Entity\Merchant;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Provider\MerchantProviderInterface;
use Silex\Application;

class MerchantProvider implements MerchantProviderInterface
{
    private $app;
    private $merchant;

    const DESTINATION = 'OSCOM_APP_EXPRESSLY_DESTINATION';
    const PATH = 'OSCOM_APP_EXPRESSLY_PATH';
    const HOST = 'OSCOM_APP_EXPRESSLY_HOST';
    const IMAGE = 'OSCOM_APP_EXPRESSLY_IMAGE';
    const NAME = 'STORE_NAME';
    const OFFER = 'OSCOM_APP_EXPRESSLY_OFFER';
    const PASSWORD = 'OSCOM_APP_EXPRESSLY_PASSWORD';
    const POLICY = 'OSCOM_APP_EXPRESSLY_POLICY';
    const TERMS = 'OSCOM_APP_EXPRESSLY_TERMS';
    const UUID = 'OSCOM_APP_EXPRESSLY_UUID';

    public function __construct(Application $app)
    {
        $this->app = $app;

        $merchant = new Merchant();
        $merchant
            ->setDestination($this->getParameter(self::DESTINATION))
            ->setPath($this->getParameter(self::PATH))
            ->setHost($this->getParameter(self::HOST))
            ->setImage($this->getParameter(self::IMAGE))
            ->setName($this->getParameter(self::NAME))
            ->setOffer($this->getParameter(self::OFFER))
            ->setPassword($this->getParameter(self::PASSWORD))
            ->setPolicy($this->getParameter(self::POLICY))
            ->setTerms($this->getParameter(self::TERMS))
            ->setUuid($this->getParameter(self::UUID));

        $this->merchant = $merchant;
    }

    public function getMerchant()
    {
        return $this->merchant;
    }

    public function setMerchant(Merchant $merchant)
    {
        $values = array(
            self::DESTINATION => $merchant->getDestination(),
            self::PATH => $merchant->getPath(),
            self::HOST => $merchant->getHost(),
            self::IMAGE => $merchant->getImage(),
            self::OFFER => $merchant->getOffer(),
            self::PASSWORD => $merchant->getPassword(),
            self::POLICY => $merchant->getPolicy(),
            self::TERMS => $merchant->getTerms(),
            self::UUID => $merchant->getUuid()
        );

        foreach ($values as $key => $value) {
            $this->saveParameter($key, $value);
        }

        $this->merchant = $merchant;

        return $this;
    }

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
            $this->app['logger']->addError(ExceptionFormatter::format($e));

            return false;
        }

        return true;
    }

    // TODO: use application wide define() statements instead
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
}