<?php
/**
 *
 */

namespace leammas\yii2\megaplanauth;

use linslin\yii2\curl\Curl;
use yii\base\Exception;
use yii\helpers\Json;
use yii\base\InvalidParamException;
use yii\base\Component;

/**
 *
 */
class MegaplanAuth extends Component
{

    /**
     * @var Curl
     */
    public $curl;
    /**
     * @var string
     */
    public $url;
    /**
     * @var int
     */
    public $timeout;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->curl))
        {
            $this->curl = new Curl();
        }
    }

    /**
     * @var string
     */
    protected $authRelativeUrl ='/BumsCommonApiV01/User/authorize.api';

    /**
     * @param array $data {
     *  @var string Login
     *  @var string Password md5 encoded password. Since md5 is not very strong hashing algorithm, https usage highly recommended.
     * }
     */
    protected function prepareRequest($data)
    {
        $isHttps = parse_url($this->url, PHP_URL_SCHEME) === 'https';

        $headers = [
            'Date' => (new \DateTime())->format( 'r' ),
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept: application/json'
        ];
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->curl->setOption(CURLOPT_USERAGENT, __CLASS__);
        $this->curl->setOption(CURLOPT_POSTFIELDS, $data);
        if ($isHttps)
        {
            $this->curl->setOption(CURLOPT_SSL_VERIFYHOST, 0);
            $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        }
        $this->curl->setOption(CURLOPT_CONNECTTIMEOUT, $this->timeout);
        $this->curl->setOption(CURLOPT_TIMEOUT, $this->timeout);
    }

    /**
     * @param $username
     * @param $password
     * @return array Success {
     *  @var string AccessId
     *  @var string SecretKey
     *  @var string UserId
     *  @var string EmployeeId
     * }
     * @throws MPAuthException
     */
    public function authenticate($username, $password)
    {
        $data = [
            'Login' => $username,
            'Password' => md5($password)
        ];
        $this->prepareRequest($data);
        try {
            $response = $this->curl->post($this->url . $this->authRelativeUrl, true);
            try
            {
                $decodedResponse = Json::decode($response, true);
                switch ($decodedResponse['status']['code']) {
                    case 'ok' :
                        break;

                    case 'error' :
                        throw new MPAuthException('Invalid username or password.');
                        break;

                    default :
                        throw new MPAuthException('Unknown response status.');
                }
                return $decodedResponse['data'];
            }
            catch (InvalidParamException $e)
            {
                throw new MPAuthException('Error decoding server response. Raw response: ' . var_export($response, true));
            }
        }
        catch (Exception $e)
        {
            throw new MPAuthException('Error requesting host:' . $e->getMessage() . $e->getCode());
        }
    }

}
