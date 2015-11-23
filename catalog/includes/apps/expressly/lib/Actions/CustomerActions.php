<?php

namespace Expressly\Lib\Actions;

use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Presenter\BatchCustomerPresenter;
use Silex\Application;

class CustomerActions
{
    public static function getBulk(Application $app)
    {
        $json = file_get_contents('php://input');
        $json = json_decode($json);

        $users = array();

        try {
            if (!property_exists($json, 'emails')) {
                throw new GenericException('Invalid JSON input');
            }

            foreach ($json->emails as $email) {
                $query = tep_db_query(
                    sprintf(
                        'select `customers_id` from %s where `customers_email_address`="%s";',
                        TABLE_CUSTOMERS,
                        $email
                    )
                );

                if (tep_db_num_rows($query) > 0) {
                    $users['existing'][] = $email;
                }
            }
        } catch (\Exception $e) {
            $app['logger']->error(ExceptionFormatter::format($e));
        }

        $presenter = new BatchCustomerPresenter($users['existing']);

        return $presenter->toArray();
    }
}