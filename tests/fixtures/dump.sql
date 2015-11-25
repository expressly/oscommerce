INSERT INTO `customers`(customers_id, customers_firstname, customers_lastname, customers_email_address, customers_telephone, customers_password)
VALUES
  (101, 'John', 'Doe', 'john.doe@example.com', '+1(23)456-78-90', '0'),
  (102, 'Jane', 'Doe', 'jane.doe@example.com', '+1(23)456-78-91', '0');

INSERT INTO `orders`(orders_id, customers_id, customers_name, customers_company, customers_street_address, customers_suburb, customers_city, customers_postcode, customers_state, customers_country, customers_telephone, customers_email_address, customers_address_format_id, delivery_name, delivery_company, delivery_street_address, delivery_suburb, delivery_city, delivery_postcode, delivery_state, delivery_country, delivery_address_format_id, billing_name, billing_company, billing_street_address, billing_suburb, billing_city, billing_postcode, billing_state, billing_country, billing_address_format_id, payment_method, cc_type, cc_owner, cc_number, cc_expires, last_modified, date_purchased, orders_status, orders_date_finished, currency, currency_value)
VALUES
  (1, 101, 'John Doe', 'John Doe Company', 'test street', '', 'Los Angeles', '90210', 'California', 'United States', '+1 23 4567890', 'john.doe@example.com', 2, 'John Doe', 'John Doe Company', 'test street', '', 'Los Angeles', '90210', 'California', 'United States', 2, 'John Doe', 'John Doe Company', 'test street', '', 'Los Angeles', '90210', 'California', 'United States', 2, 'Cash on Delivery', '', '', '', '', null, '2015-10-13 16:48:53', 1, null, 'USD', 1.000000);

INSERT INTO `orders_products`(orders_products_id, orders_id, products_id, products_model, products_name, products_price, final_price, products_tax, products_quantity)
VALUES
  (1, 1, 16, 'DVD-CUFI', 'Courage Under Fire', 29.9900, 29.9900, 0.0000, 1),
  (2, 1,  2, 'MG400-32MB', 'Matrox G400 32MB', 499.9900, 489.9900, 0.0000, 1);