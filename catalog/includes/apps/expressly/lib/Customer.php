<?php

namespace Expressly\Lib;

use Expressly\Entity\Address;
use Expressly\Entity\Customer as CustomerEntity;
use Expressly\Entity\Email;
use Expressly\Entity\Phone;
use Expressly\Event\CustomerMigrateEvent;
use Expressly\Exception\ExceptionFormatter;
use Expressly\Exception\GenericException;
use Expressly\Presenter\CustomerMigratePresenter;
use Silex\Application;

class Customer
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function add($uuid, $json)
    {
        $merchant = $this->app['merchant.provider']->getMerchant();
        $event = new CustomerMigrateEvent($merchant, $uuid);

        try {
            $email = $json['migration']['data']['email'];
            $customer = $json['migration']['data']['customerData'];
//            $customerId = null;

            $query = tep_db_query(
                sprintf('SELECT `customers_id` FROM %s WHERE `customers_email_address`', TABLE_CUSTOMERS)
            );

            if (tep_db_num_rows($query) > 0) {
                $event = new CustomerMigrateEvent($merchant, $uuid, CustomerMigrateEvent::EXISTING_CUSTOMER);
//                $data = tep_db_fetch_array($query);
//                $customerId = $data['customers_id'];
            } else {
                tep_db_perform(
                    TABLE_CUSTOMERS, array(
                        'customers_firstname' => $customer['firstName'],
                        'customers_lastname' => $customer['lastName'],
                        'customers_gender' => $customer['gender'] == CustomerEntity::GENDER_MALE ? 'm' : 'f',
                        'customers_email_address' => $email,
                        'customers_password' => md5('xly' . microtime()),
                        'customers_telephone' => '',
                        'customers_fax' => '',
                        'customers_dob' => $customer['birthday'],
                        'customers_newsletter' => 1
                    )
                );
                $customerId = tep_db_insert_id();
                $defaultAddressId = 0;

                foreach ($customer['addresses'] as $index => $address) {
                    $countryCodeProvider = $this->app['country_code.provider'];
                    $countryCode = $countryCodeProvider->getIso3($address['country']);
                    $osCountryId = $countryCode;

                    tep_db_perform(
                        TABLE_ADDRESS_BOOK,
                        array(
                            'customers_id' => $customerId,
                            'entry_company' => $address['company'],
                            'entry_firstname' => $address['firstName'],
                            'entry_lastname' => $address['lastName'],
                            'entry_street_address' => $address['address1'],
                            'entry_suburb' => $address['address2'],
                            'entry_postcode' => $address['zip'],
                            'entry_city' => $address['city'],
                            'entry_state' => $address['stateProvence'],
                            'entry_country_id' => $osCountryId
                        )
                    );

                    $addressId = tep_db_insert_id();
                    if ($index == $address['shippingAddress']) {
                        $defaultAddressId = $addressId;
                        $phone = !empty($customer['phones']) ? $customer['phones'][$address['phone']]['number'] : '';
                        tep_db_perform(
                            TABLE_CUSTOMERS,
                            array(
                                'customers_telephone' => $phone,
                                'customers_default_address_id' => $addressId
                            ),
                            'update',
                            "customers_id={$customerId}"
                        );
                    }
                }

                // Log user in
                if ($customerId) {
                    global $customer_id, $cart;

                    $customer_id = $customerId;
                    $_SESSION['customer_id'] = $customerId;
                    $_SESSION['customer_default_address_id'] = $defaultAddressId;
                    $_SESSION['customer_first_name'] = $customer['firstName'];
                    $_SESSION['customer_last_name'] = $customer['lastName'];
                    $_SESSION['customer_zone_id'] = 0;


                    if (!empty($json['cart']['productId'])) {
                        $cart = new \shoppingCart();
                        $cart->restore_contents();
                        $cart->add_cart($json['cart']['productId'], 1);
                        $_SESSION['cart'] = $cart;
                    }
                }

            }
        } catch (\Exception $e) {
            $this->app['logger']->addError(ExceptionFormatter::format($e));
        }

        $this->app['dispatcher']->dispatch('customer.migrate.success', $event);
    }

    public function get($emailAddr)
    {
        // split query so user is retrieved even if no addresses exist
        $query = tep_db_query(
            sprintf(
                'SELECT cust.* , addr.*, country.countries_iso_code_3 FROM %s AS cust, %s AS addr, %s AS country WHERE cust.`customers_id`=addr.`customers_id` AND cust.`customers_default_address_id`=addr.`address_book_id` AND country.`countries_id`=addr.`entry_country_id` AND cust.`customers_email_address`="%s";',
                TABLE_CUSTOMERS,
                TABLE_ADDRESS_BOOK,
                TABLE_COUNTRIES,
                $emailAddr
            )
        );
        $osCustomer = tep_db_fetch_array($query);

        $customer = new CustomerEntity();
        $customer
            ->setFirstName($osCustomer['customers_firstname'])
            ->setLastName($osCustomer['customers_lastname'])
            ->setBirthday(new \DateTime($osCustomer['customers_dob']))
            ->setGender($osCustomer['customers_gender'] == 'm' ? CustomerEntity::GENDER_MALE : CustomerEntity::GENDER_FEMALE);

        $email = new Email();
        $email
            ->setAlias('default')
            ->setEmail($emailAddr);
        $customer->addEmail($email);

        $phone = new Phone();
        $phone
            ->setType(Phone::PHONE_TYPE_MOBILE)
            ->setNumber($osCustomer['customers_telephone']);
        if ($phone->getNumber()) {
            $customer->addPhone($phone);
        }

        $address = new Address();
        $address
            ->setAlias('default')
            ->setFirstName($osCustomer['entry_firstname'])
            ->setLastName($osCustomer['entry_lastname'])
            ->setAddress1($osCustomer['entry_street_address'])
            ->setCity($osCustomer['entry_city'])
            ->setCompanyName($osCustomer['entry_company'])
            ->setZip($osCustomer['entry_postcode'])
            ->setStateProvince($osCustomer['entry_state'])
            ->setCountry($osCustomer['countries_iso_code_3'])
            ->setPhonePosition($customer->getPhoneIndex($phone));
        if ($address->getAddress1()) {
            $customer->addAddress($address, true, Address::ADDRESS_BOTH);
        }

        $merchant = $this->app['merchant.provider']->getMerchant();
        $response = new CustomerMigratePresenter($merchant, $customer, $emailAddr, $osCustomer['customers_id']);

        echo json_encode($response->toArray());
    }
}