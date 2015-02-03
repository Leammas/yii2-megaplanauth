<?php

namespace tests\codeception\unit\components\megaplanauth;

use Yii;
use yii\codeception\TestCase;
use app\components\megaplanauth\MegaplanAuth;
use Codeception\Specify;
use Codeception\Util\Stub;
use linslin\yii2\curl\Curl;
use app\components\megaplanauth\MPAuthException;
use yii\base\Exception;

class MegaplanAuthTest extends TestCase
{
    public function testAuthenticateValid()
    {
        $validResponse = <<<JSON
{
    "status":{
        "code":"ok",
        "message":null
    },
    "params":{
        "Login":"someuser",
        "Password":"9c42a1346e333a770904b2a2b37fa7d3"
    },
    "data":{
        "AccessId":"5f615c654865eAddF0c2",
        "SecretKey":"2Dd0E3ff3d4a7e3695d3CeD3ec9Ff8D39D67365c",
        "UserId":12324,
        "EmployeeId":1000001
    }
}
JSON;
        $expected = [
            'AccessId' => '5f615c654865eAddF0c2',
            'SecretKey' => '2Dd0E3ff3d4a7e3695d3CeD3ec9Ff8D39D67365c',
            'UserId' => '12324',
            'EmployeeId' => '1000001'
        ];
        $curlMock = Stub::make(new Curl(), ['post' => $validResponse]);
        $obj = new MegaplanAuth();
        $obj->url = 'https://some.url';
        $obj->curl = $curlMock;
        $obj->init();
        $result = $obj->authenticate('someuser', 'somepassword');
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException app\components\megaplanauth\MPAuthException
     * @expectedExceptionMessage Invalid username or password.
     */
    public function testAuthenticateInvalidPass()
    {
        $validResponse = <<<JSON
{
    "status":{
        "code":"error",
        "message":"Required parameter is not specified"
    }
}
JSON;
        $curlMock = Stub::make(new Curl(), ['post' => $validResponse]);
        $obj = new MegaplanAuth();
        $obj->url = 'https://some.url';
        $obj->curl = $curlMock;
        $obj->init();
        $result = $obj->authenticate('someuser', 'somepassword');
    }

    /**
     * @expectedException app\components\megaplanauth\MPAuthException
     * @expectedExceptionMessage Error decoding server response. Raw response
     */
    public function testAuthenticateInvalidJson()
    {
        $curlMock = Stub::make(new Curl(), ['post' => 'somerror']);
        $obj = new MegaplanAuth();
        $obj->url = 'https://some.url';
        $obj->curl = $curlMock;
        $obj->init();
        $result = $obj->authenticate('someuser', 'somepassword');
    }

    /**
     * @expectedException app\components\megaplanauth\MPAuthException
     * @expectedExceptionMessage Error requesting host
     */
    public function testAuthenticateFailedRequest()
    {
        $curlMock = Stub::make(new Curl(), ['post' => function() {throw new Exception;}]);
        $obj = new MegaplanAuth();
        $obj->url = 'https://some.url';
        $obj->curl = $curlMock;
        $obj->init();
        $result = $obj->authenticate('someuser', 'somepassword');
    }

}