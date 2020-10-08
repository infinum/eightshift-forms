<?php

namespace EightshiftFormsTests\Integrations\Buckaroo;

class DataProvider
{

  /**
   * Example of successful transaction Buckaroo response.
   *
   * @return array
   */
  public function successResponseMock(): array {
    return [
        'BRQ_AMOUNT' => 5.00,
        'BRQ_CURRENCY' => 'EUR',
        'BRQ_CUSTOMER_NAME' => 'J. de Tèster',
        'BRQ_INVOICENUMBER' => 'test invoice 123',
        'BRQ_PAYER_HASH' => '67a6cebb1b99439c820a08cca4e0e2793dc4bcd26d2ed5678f55855d74359546c85066700982cfd40a535341f5ff8f45f92e1d21fca51e3c7192b151fd6d2c04',
        'BRQ_PAYMENT' => '24BA0D9E683046DCB9A209350C5F0D39',
        'BRQ_PAYMENT_METHOD' => 'ideal',
        'BRQ_SERVICE_IDEAL_CONSUMERBIC' => 'RABONL2U',
        'BRQ_SERVICE_IDEAL_CONSUMERIBAN' => 'NL44RABO0123456789',
        'BRQ_SERVICE_IDEAL_CONSUMERISSUER' => 'ABN AMRO',
        'BRQ_SERVICE_IDEAL_CONSUMERNAME' => 'J. de Tèster',
        'BRQ_SERVICE_IDEAL_TRANSACTIONID' => '0000000000000001',
        'BRQ_STATUSCODE' => 190,
        'BRQ_STATUSCODE_DETAIL' => 'S001',
        'BRQ_STATUSMESSAGE' => 'Transaction successfully processed',
        'BRQ_TEST' => true,
        'BRQ_TIMESTAMP' => '2020-10-08 10:41:27',
        'BRQ_TRANSACTIONS' => '4B29D787E2C5486E946EE7462DF221DF',
        'BRQ_WEBSITEKEY' => 'pjTB7CdkZ8',
        'BRQ_SIGNATURE' => 'b25f86fd735ddb01607d23a0aa3fe6ae69816052',
    ];
  }

  /**
   * Example of error on transaction response from Buckaroo (failed transaction).
   *
   * @return array
   */
  public function errorResponseMock(): array {
    return [
      'BRQ_AMOUNT' => 5.00,
      'BRQ_CURRENCY' => 'EUR',
      'BRQ_INVOICENUMBER' => 'test invoice 123',
      'BRQ_PAYMENT' => 'BFB7E1980C6F45A5AFA7DFE7C298E697',
      'BRQ_PAYMENT_METHOD' => 'ideal',
      'BRQ_SERVICE_IDEAL_CONSUMERISSUER' => 'ABN AMRO',
      'BRQ_SERVICE_IDEAL_TRANSACTIONID' => '0000000000000001',
      'BRQ_STATUSCODE' => 490,
      'BRQ_STATUSCODE_DETAIL' => 'S997',
      'BRQ_STATUSMESSAGE' => 'The transaction failed at the consumer\'s bank.',
      'BRQ_TEST' => true,
      'BRQ_TIMESTAMP' => '2020-10-08 10:57:20',
      'BRQ_TRANSACTIONS' => 'E08843AD5F4D4B9E86C9708E1F91E934',
      'BRQ_WEBSITEKEY' => 'pjTB7CdkZ8',
      'BRQ_SIGNATURE' => '2c97e026d79dbbfec6c982a511c97ceecacf55c5',
    ];
  }

  /**
   * Example of "transaction rejected" response from Buckaroo.
   *
   * @return array
   */
  public function rejectResponseMock(): array {
    return [
      'BRQ_AMOUNT' => 5.00,
      'BRQ_CURRENCY' => 'EUR',
      'BRQ_INVOICENUMBER' => 'test invoice 123',
      'BRQ_PAYMENT' => 'BA7B8582CCAB4C5C94EDC1BB99BB3D69',
      'BRQ_PAYMENT_METHOD' => 'ideal',
      'BRQ_SERVICE_IDEAL_CONSUMERISSUER' => 'ABN AMRO',
      'BRQ_SERVICE_IDEAL_TRANSACTIONID' => '0000000000000001',
      'BRQ_STATUSCODE' => 690,
      'BRQ_STATUSCODE_DETAIL' => 'S101',
      'BRQ_STATUSMESSAGE' => 'The transaction was rejected during processing by BasProcessor.',
      'BRQ_TEST' => true,
      'BRQ_TIMESTAMP' => '2020-10-08 10:58:29',
      'BRQ_TRANSACTIONS' => '27D7A257AA1F4537A763F48F3CEBD585',
      'BRQ_WEBSITEKEY' => 'pjTB7CdkZ8',
      'BRQ_SIGNATURE' => '79142b0e0c4a494a8ddf9242f6977b1a2cc9c99c'
    ];
  }

  /**
   * Example of "transaction cancelled by user" response from Buckaroo.
   *
   * @return array
   */
  public function cancelledResponseMock(): array {
    return [
      'BRQ_AMOUNT' => 5.00,
      'BRQ_CURRENCY' => 'EUR',
      'BRQ_INVOICENUMBER' => 'test invoice 123',
      'BRQ_PAYMENT' => '6679C43DB504467BB470CF9E100081E7',
      'BRQ_PAYMENT_METHOD' => 'ideal',
      'BRQ_SERVICE_IDEAL_CONSUMERISSUER' => 'ABN AMRO',
      'BRQ_SERVICE_IDEAL_TRANSACTIONID' => '0000000000000001',
      'BRQ_STATUSCODE' => 890,
      'BRQ_STATUSCODE_DETAIL' => 'S111',
      'BRQ_STATUSMESSAGE' => 'The transaction was cancelled by the user.',
      'BRQ_TEST' => true,
      'BRQ_TIMESTAMP' => '2020-10-08 10:59:32',
      'BRQ_TRANSACTIONS' => '5657D6141D6D499CB0791D81C296B774',
      'BRQ_WEBSITEKEY' => 'pjTB7CdkZ8',
      'BRQ_SIGNATURE' => 'd64b3c9dd89c5794f043e3b396d06d6a565a4fb3',
    ];
  }

  /**
   * Example of "pending processing" response from Buckaroo.
   *
   * @return array
   */
  public function pendingResponseMock(): array {
    return [
      'BRQ_AMOUNT' => 5.00,
      'BRQ_CURRENCY' => 'EUR',
      'BRQ_INVOICENUMBER' => 'test invoice 123',
      'BRQ_PAYMENT' => '29754AD35C31462090EF9644970B9CA8',
      'BRQ_PAYMENT_METHOD' => 'ideal',
      'BRQ_SERVICE_IDEAL_CONSUMERISSUER' => 'ABN AMRO',
      'BRQ_SERVICE_IDEAL_TRANSACTIONID' => '0000000000000001',
      'BRQ_STATUSCODE' => 791,
      'BRQ_STATUSMESSAGE' => 'Pending processing',
      'BRQ_TEST' => true,
      'BRQ_TIMESTAMP' => '2020-10-08 11:01:13',
      'BRQ_TRANSACTIONS' => 'AAC90995D35347C9B2D8D9EDFB265B1F',
      'BRQ_WEBSITEKEY' => 'pjTB7CdkZ8',
      'BRQ_SIGNATURE' => '0a03229db3842c7042fbca3091092d8df628f097',
    ];
  }
}