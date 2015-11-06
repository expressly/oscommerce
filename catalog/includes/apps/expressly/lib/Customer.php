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
use Expressly\Subscriber\CustomerMigrationSubscriber;
use Silex\Application;

class Customer
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function add($uuid, $json, $language = 'en')
    {
        $merchant = $this->app['merchant.provider']->getMerchant();
        $event = new CustomerMigrateEvent($merchant, $uuid);
        $exists = false;

        try {
            $email = $json['migration']['data']['email'];
            $customer = $json['migration']['data']['customerData'];

            $query = tep_db_query(
                sprintf('SELECT `customers_id` FROM %s WHERE `customers_email_address`="%s"', TABLE_CUSTOMERS, $email)
            );

            if (tep_db_num_rows($query) > 0) {
                $event = new CustomerMigrateEvent($merchant, $uuid, CustomerMigrateEvent::EXISTING_CUSTOMER);
                $exists = true;
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

                    if (defined('STORE_NAME') && defined('STORE_OWNER_EMAIL_ADDRESS')) {
                        require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PASSWORD_FORGOTTEN);
                        $key = tep_create_random_value(40);
                        tep_db_query(
                            sprintf(
                                'UPDATE %s set `password_reset_key`="%s", `password_reset_date`=now() WHERE `customers_info_id`=%u',
                                TABLE_CUSTOMERS_INFO,
                                tep_db_input($key),
                                $customerId
                            )
                        );

                        $url = tep_href_link(FILENAME_PASSWORD_RESET,
                            sprintf('account=%s&key=%s', urlencode($email), $key), 'SSL', true);
                        tep_mail("{$customer['firstName']} {$customer['lastName']}", $email,
                            EMAIL_PASSWORD_RESET_SUBJECT,
                            sprintf(EMAIL_PASSWORD_RESET_BODY, $url), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
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
            $this->app['logger']->error(ExceptionFormatter::format($e));
        }

        $this->app['dispatcher']->dispatch(CustomerMigrationSubscriber::CUSTOMER_MIGRATE_SUCCESS, $event);

        return !$exists;
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

        return $response->toArray();
    }
}