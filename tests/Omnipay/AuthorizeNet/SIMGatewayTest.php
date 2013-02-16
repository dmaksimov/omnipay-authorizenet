<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\AuthorizeNet;

use Omnipay\GatewayTestCase;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class SIMGatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new SIMGateway($this->httpClient, $this->httpRequest);
        $this->gateway->setApiLoginId('example');

        $this->options = array(
            'amount' => 1000,
            'transactionId' => '99',
            'returnUrl' => 'https://www.example.com/return',
        );
    }

    public function testAuthorize()
    {
        $response = $this->gateway->authorize($this->options);

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());

        $redirectData = $response->getRedirectData();
        $this->assertSame('https://www.example.com/return', $redirectData['x_relay_url']);
    }

    public function testCompleteAuthorize()
    {
        $this->httpRequest->request->replace(
            array(
                'x_response_code' => '1',
                'x_trans_id' => '12345',
                'x_MD5_Hash' => md5('example9910.00'),
            )
        );

        $response = $this->gateway->completeAuthorize($this->options);

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('12345', $response->getGatewayReference());
        $this->assertNull($response->getMessage());
    }

    public function testPurchase()
    {
        $response = $this->gateway->purchase($this->options);

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNotEmpty($response->getRedirectUrl());

        $redirectData = $response->getRedirectData();
        $this->assertSame('https://www.example.com/return', $redirectData['x_relay_url']);
    }

    public function testCompletePurchase()
    {
        $this->httpRequest->request->replace(
            array(
                'x_response_code' => '1',
                'x_trans_id' => '12345',
                'x_MD5_Hash' => md5('example9910.00'),
            )
        );

        $response = $this->gateway->completePurchase($this->options);

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('12345', $response->getGatewayReference());
        $this->assertNull($response->getMessage());
    }
}
