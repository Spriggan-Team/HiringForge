<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContractTypeControllerTest extends WebTestCase
{
    public function testGetContractTypes() : void
    {
        $client = static::createClient();
        
        $client->request(
            'GET',
            'api/contract'
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'Content-Type',
            'application/json'
        );
    }
}