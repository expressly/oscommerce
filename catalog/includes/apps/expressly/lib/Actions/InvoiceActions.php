<?php

namespace Expressly\Lib\Actions;

use Expressly\Entity\Invoice;
use Expressly\Entity\Order;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Presenter\BatchInvoicePresenter;
use Silex\Application;

class InvoiceActions
{
    public static function getBulk(Application $app)
    {
        $json = file_get_contents('php://input');
        $json = json_decode($json);

        $invoices = array();

        try {
            if (!property_exists($json, 'customers')) {
                throw new GenericException('Invalid JSON input');
            }

            foreach ($json->customers as $customer) {
                if (!property_exists($customer, 'email')) {
                    continue;
                }

                $order_query = tep_db_query(sprintf(
                    'SELECT ord.`orders_id`, ord.`date_purchased` FROM %s AS cust, %s AS ord WHERE cust.`customers_email_address`="%s" AND ord.`customers_id`=cust.`customers_id` AND ord.`date_purchased` BETWEEN "%s" AND "%s";',
                    TABLE_CUSTOMERS,
                    TABLE_ORDERS,
                    $customer->email,
                    $customer->from,
                    $customer->to
                ));

                $invoice = new Invoice();
                $invoice->setEmail($customer->email);

                while ($order_array = tep_db_fetch_array($order_query)) {

                    if (empty($order_array['orders_id'])) {
                        continue;
                    }

                    $total    = 0.0;
                    $tax      = 0.0;
                    $count    = 0;
                    $currency = '';

                    $product_query = tep_db_query(sprintf(
                        'SELECT * FROM %s WHERE `orders_id`=%u;',
                        TABLE_ORDERS_PRODUCTS,
                        $order_array['orders_id']
                    ));

                    while ($osProduct = tep_db_fetch_array($product_query)) {
                        $total += $osProduct['products_price'];
                        $tax   += $osProduct['products_tax'];
                        ++$count;
                        $currency = $osProduct['currency'];
                    }

                    $order = new Order();
                    $order
                        ->setId($order_array['orders_id'])
                        ->setDate(new \DateTime($order_array['date_purchased']))
                        ->setCurrency($currency)
                        ->setTotal($total, $tax);

                    $invoice->addOrder($order);

                }

                $invoices[] = $invoice;
            }
        } catch (\Exception $e) {
            $app['logger']->error(ExceptionFormatter::format($e));
        }

        $presenter = new BatchInvoicePresenter($invoices);

        return $presenter->toArray();
    }
}