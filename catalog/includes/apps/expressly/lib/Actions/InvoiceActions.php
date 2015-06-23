<?php

namespace Expressly\Lib\Actions;

use Expressly\Entity\Invoice;
use Expressly\Entity\Order;
use Expressly\Presenter\BatchInvoicePresenter;

class InvoiceActions
{
    public static function getBulk()
    {
        $json = file_get_contents('php://input');
        $json = json_decode($json);

        $invoices = array();

        foreach ($json->customers as $customer) {
            $orderQuery = tep_db_query(
                sprintf(
                    'SELECT ord.`orders_id`, ord.`date_purchased` FROM %s AS cust, %s AS ord WHERE cust.`customers_email_address`="%s" AND ord.`customers_id`=cust.`customers_id` AND ord.`date_purchased` BETWEEN "%s" AND "%s";',
                    TABLE_CUSTOMERS,
                    TABLE_ORDERS,
                    $customer->email,
                    $customer->from,
                    $customer->to
                )
            );

            $invoice = new Invoice();
            $invoice->setEmail($customer->email);

            foreach (tep_db_fetch_array($orderQuery) as $row) {
                if (empty($row['orders_id'])) {
                    continue;
                }

                $productQuery = tep_db_query(
                    sprintf(
                        'SELECT * FROM %s WHERE `orders_id`=%u;',
                        TABLE_ORDERS_PRODUCTS,
                        $row['orders_id']
                    )
                );

                $total = 0.0;
                $tax = 0.0;
                $count = 0;
                $currency = '';

                foreach (tep_db_fetch_array($productQuery) as $osProduct) {
                    $total += $osProduct['products_price'];
                    $tax += $osProduct['products_tax'];
                    ++$count;
                    $currency = $osProduct['currency'];
                }

                $order = new Order();
                $order
                    ->setId($row['orders_id'])
                    ->setDate(new \DateTime($row['date_purchased']))
                    ->setCurrency($currency)
                    ->setTotal($total, $tax);

                $invoice->addOrder($order);
            }
        }

        $presenter = new BatchInvoicePresenter($invoices);
        echo json_encode($presenter->toArray());
    }
}