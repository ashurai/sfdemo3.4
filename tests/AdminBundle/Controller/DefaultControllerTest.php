<?php

namespace AdminBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }

    private static $token;
    private static $newInvalid;
    private static $newValid;
    private static $editInvalid;
    private static $editValid;

    private $fixtures;

    /**
     * setUpBeforeClass
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //DATA
        self::$newInvalid = [
            'password' => 'test',
            'email' => 'test',
            'country' => 'test',
            'nationalIdentity' => 'test',
            'city' => 'test',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'birthDate' => '1990-01-01',
            'gender' => 'test',
            'countryRegion' => 'test',
            'countryState' => 'test',
        ];

        self::$editInvalid = [
            'country' => 'test',
            'nationalIdentity' => '1990-01-01',
            'city' => 'Medlab City',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'birthDate' => '1989-04-18',
            'gender' => 'M',
            'countryRegion' => 'test',
            'countryState' => 'test',
        ];
    }

    public function setUp()
    {
        parent::setUp();
        $this->fixtures = $this->loadFixtures([
            LoadPatientProfile::class,
            LoadPlatform::class,
            LoadCountry::class,
            LoadRegion::class,
            LoadState::class,
        ])->getReferenceRepository();

        $this->initCredentials();
    }

    private function initCredentials()
    {
        // Get token
        $client = static::createClient();
        $baseUri = $client->getKernel()->getContainer()->getParameter('domain_url');
        $localClient = new GuzzleHttp\Client([
            'base_uri' => $baseUri,
            //'http_errors' => false, // disable exceptions
        ]);

        $username = 'PATIENT@user.com';
        $password = 'PATIENT';

        $apiHashKey = $client->getKernel()->getContainer()->getParameter('api_hash_key');
        $token = hash_hmac('sha1', 'testing/1.0', $apiHashKey);

        $res = $localClient->request('POST', "$baseUri/api/v1/tokens", [
            'form_params' => [
                'username' => $username,
                'pass' => $password,
            ],
            'headers' => [
                'User-Agent' => 'testing/1.0',
                'Accept' => 'application/json',
                'hashKey' => $token,
            ]
        ]);

        $code = $res->getStatusCode();
        if ($code != Response::HTTP_OK) { // Status code 200 -> valid user
            throw new AccessDeniedHttpException('User not found or validation failed');
        }
        $body = $res->getBody();
        $contents = $body->getContents();
        $arrayContents = json_decode($contents, true);

        self::$token = $arrayContents['token'];
    }

    private function newValidData()
    {
        /** @var Fixture $fixtures */
        $fixtures = $this->fixtures;

        $str = substr(md5('test'),0,8);
        $data = [
            //'username' => $str,
            'password' => 'Test1234',
            'email' => $str.'@'.$str.'.com',
            'country' => $fixtures->getReference('country-TEST')->getId(),
            'agreementTerms' => true,
            'agreementAdvertising' => true,
            'nationalIdentity' => '00000000A',
            'city' => $str,
            'firstName' => $str,
            'lastName' => $str,
            'birthDate' => '2000-01-01',
            'gender' => 'M',
            'countryRegion' => $fixtures->getReference('region-TEST')->getId(),
            'countryState' => $fixtures->getReference('state-TEST')->getId(),
        ];

        return $data;
    }

    private function editValidData()
    {
        /** @var Fixture $fixtures */
        $fixtures = $this->fixtures;
        $str = substr(md5('test-edit'),0,8);

        $data = [
            'country' => $fixtures->getReference('country-TEST')->getId(),
            'nationalIdentity' => '00000000A',
            'city' => $str,
            'firstName' => $str,
            'lastName' => $str,
            'birthDate' => '1989-01-01',
            'gender' => 'M',
            'countryRegion' => $fixtures->getReference('region-TEST')->getId(),
            'countryState' => $fixtures->getReference('state-TEST')->getId(),
        ];

        return $data;
    }

    /**
     * Test new PatientProfile
     */
    public function test_postAction()
    {
        $client = $this->makeClient();
        $url = $this->getUrl('api_patient_profiles_post');
        $apiHashKey = $client->getKernel()->getContainer()->getParameter('api_hash_key');
        $hashToken = hash_hmac('sha1', 'testing/1.0', $apiHashKey);

        //2.- Allow and partial data
        $crawler = $client->request('POST', $url, [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_USER_AGENT' => 'testing/1.0',
                'HTTP_hashKey' => $hashToken,
                'HTTP_JWT' => self::$token,
            ],
            json_encode(['firstName' => 'test'])
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

        //3.- Allow and invalid data
        $crawler = $client->request('POST', $url, [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_USER_AGENT' => 'testing/1.0',
                'HTTP_hashKey' => $hashToken,
                'HTTP_JWT' => self::$token,
            ],
            json_encode(self::$newInvalid)
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

        //4.- Allow and valid data
        $crawler = $client->request('POST', $url, [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_USER_AGENT' => 'testing/1.0',
                'HTTP_hashKey' => $hashToken,
                'HTTP_JWT' => self::$token,
            ],
            json_encode($this->newValidData())
        );
        $res = $client->getResponse();
        $body = $res->getContent();
        $data = json_decode($body, true);
        $this->assertStatusCode(Response::HTTP_CREATED, $client);
        $this->assertNotEmpty($data);
    }


    public function test_getAction()
    {
        $patientProfileFixtures = $this->fixtures->getReference('patientProfile-TEST');

        $client = $this->makeClient();
        $url = $this->getUrl('api_patient_profiles_get',['id' => $patientProfileFixtures->getId()]);

        //1.- Unauthorized without JWT
        $crawler = $client->request('GET', $url);
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $client);

        //2.- Show
        $crawler = $client->request('GET', $url, [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_JWT' => self::$token,
            ]
        );
        $res  = $client->getResponse();
        $body = $res->getContent();
        $data = json_decode($body, true);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertNotEmpty($data);
    }


    /**
     * Test update patient profile
     */
    public function test_updateAction()
    {
        $patientProfileFixtures = $this->fixtures->getReference('patientProfile-TEST');

        $client = $this->makeClient();
        // Find valid patient profile (also can do it with list and select first record)
        $url = $this->getUrl('api_patient_profiles_get',['id' => $patientProfileFixtures->getId()]);

        $crawler = $client->request('GET', $url, [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_JWT' => self::$token,
            ]
        );
        $res  = $client->getResponse();
        $body = $res->getContent();
        $data = json_decode($body, true);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertNotEquals(0, count($data));

        if ($data) {

            $client = $this->makeClient();
            $url = $this->getUrl('api_patient_profiles_update',['id' => $data['id']]);

            //1.- Unauthorized without JWT
            $crawler = $client->request('PUT', $url);
            $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $client);

            //2.- Allow and invalid data
            $crawler = $client->request('PUT', $url, [], [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_JWT' => self::$token,
                ],
                json_encode(self::$editInvalid)
            );
            $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

            //3.- Allow and valid data
            $crawler = $client->request('PUT', $url, [], [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_JWT' => self::$token,
                ],
                json_encode($this->editValidData())
            );
            $res  = $client->getResponse();
            $body = $res->getContent();
            $data = json_decode($body, true);

            $this->assertStatusCode(Response::HTTP_OK, $client);
            $this->assertNotEmpty($data);
            $this->assertEquals($patientProfileFixtures->getId(), $data['id']);

        } else {
            // Force fail
            $this->assertEquals(0, 1);
        }

    }

    /**
     * Test delete patient profile
     */
    public function test_deleteAction()
    {
        $patientProfileFixtures = $this->fixtures->getReference('patientProfile-TEST');

        $client = $this->makeClient();
        // Find valid patient profile (also can do it with list and select first record)
        $url = $this->getUrl('api_patient_profiles_get',['id' => $patientProfileFixtures->getId()]);

        $crawler = $client->request('GET', $url, [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_JWT' => self::$token,
            ]
        );
        $res  = $client->getResponse();
        $body = $res->getContent();
        $data = json_decode($body, true);

        $this->assertStatusCode(Response::HTTP_OK, $client);
        $this->assertNotEquals(0, count($data));

        if ($data) {

            $client = $this->makeClient();
            $url = $this->getUrl('api_patient_profiles_delete',['id' => $data['id']]);

            //1.- Unauthorized without JWT
            $crawler = $client->request('DELETE', $url);
            $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $client);

            //2.- Allow and invalid data
            $urlInvalid = $this->getUrl('api_patient_profiles_delete',['id' => '001']);
            $crawler = $client->request('DELETE', $urlInvalid, [], [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_JWT' => self::$token,
                ]
            );
            $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);

            //3.- Allow and valid data
            $crawler = $client->request('DELETE', $url, [], [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_JWT' => self::$token,
                ]
            );
            $res  = $client->getResponse();
            $body = $res->getContent();
            $data = json_decode($body, true);

            $this->assertStatusCode(Response::HTTP_OK, $client);
            $this->assertNotEmpty($data);
            $this->assertEquals($patientProfileFixtures->getId(), $data['id']);

        } else {
            // Force fail
            $this->assertEquals(0, 1);
        }

    }
}
