<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{

    public function setUp()
    {
        parent::setUp();
        getConnection('yazuo_crm')->beginTransaction();
    }

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function tearDown()
    {
        parent::tearDown();
        getConnection('yazuo_crm')->rollBack();
    }

    public function getSession()
    {
        $session = [
            'userInfo' => [
                'currentMerchantId' => '16042',
                'currentChainId'    => 16042,
                'userId'            => '1',
                'investId'          => '16042',
            ]
        ];
        return $session;
    }
}
