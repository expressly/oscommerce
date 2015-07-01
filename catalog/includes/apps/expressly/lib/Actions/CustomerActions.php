<?php

namespace Expressly\Lib\Actions;

use Expressly\Presenter\BatchCustomerPresenter;

class CustomerActions
{
    public static function getBulk()
    {
        $json = file_get_contents('php://input');
        $json = json_decode($json);

        $users = array();

        foreach ($json->customers as $customer) {
            $query = tep_db_query(
                sprintf(
                    'select `customers_id` from %s where `customers_email_address`="%s";',
                    TABLE_CUSTOMERS,
                    $customer
                )
            );

            if (tep_db_num_rows($query) > 0) {
                $users['existing'][] = $customer;
            }
        }

        $presenter = new BatchCustomerPresenter($users);

        return $presenter->toArray();
    }
}