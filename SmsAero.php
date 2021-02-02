<?php
namespace sersid\smsaero;

use Yii;
use yii\base\Exception;

/**
 * Sms Aero component
 * @url http://smsaero.ru/
 * @author Sersid <sersONEd@gmail.com>
 * @copyright Copyright &copy; www.sersid.ru 2014
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class SmsAero extends \yii\base\Component 
{
    const HLR_STATUS_AVAILABLE = 1;
    const HLR_STATUS_UNAVAILABLE = 2;
    const HLR_STATUS_NOT_EXIST = 3;

    /**
     * @var string
     */
    public $user;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $api_key;

    /**
     * Default signature of sender
     * @var string
     */
    public $sender = 'INFORM';

    /**
     * @var string
     */
    public $answer = 'json';

    /**
     * @var string
     */
    public $sendUrl = 'https://gate.smsaero.ru/send/';

    /**
     * @var string
     */
    public $balanceUrl = 'https://gate.smsaero.ru/balance/';

    /**
     * @var string
     */
    public $sendersUrl = 'https://gate.smsaero.ru/senders/';

    /**
     * @var string
     */
    public $signUrl = 'https://gate.smsaero.ru/sign/';

    /**
     * @var string
     */
    public $statusUrl = 'http://gate.smsaero.ru/status/';

    /**
     * @var string
     */
    public $hlrUrl = 'http://gate.smsaero.ru/hlr/';

    /**
     * @var string
     */
    public $hlrStatusUrl = 'http://gate.smsaero.ru/hlrStatus/';

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->user === null) {
            throw new Exception('SmsAero. You must specify a "user".');
        }

        if ($this->password === null && $this->api_key === null) {
            throw new Exception('SmsAero. You must specify a "password" or "api_key".');
        }

        parent::init();
    }

    /**
     * Get query
     * @return array
     */
    public function getQuery()
    {
        $pwd = $this->api_key;
        if ($this->password) {
            $pwd = md5($this->password);
        }
        $this->query = array_merge([
            'answer' => $this->answer,
            'user' => $this->user,
            'password' => $pwd,
        ], $this->query);

        return $this->query;
    }

    /**
     * @param $url string Url
     * @return array
     * @throws Exception
     */
    protected function request($url)
    {
        $data = file_get_contents($url.'?'.http_build_query($this->getQuery()));
        $arr = \yii\helpers\Json::decode($data);
        if(isset($arr['result']) && $arr['result'] == 'reject') {
            $mess = isset($arr['reason']) ? ': '.$arr['reason'] : '';
            throw new Exception('SmsAero. '.$arr['result'].$mess);
        }

        $query = $this->query;
        unset($query['user'], $query['password'], $query['answer']);
        if(!empty($query)) {
            $url .= "?".http_build_query($query);
        }
        Yii::info("Url: {$url}\nData: {$data}", __METHOD__);

        return $arr;
    }

    /**
     * Send message
     * @param $to Recipient's phone number in the format 71234567890
     * @param $text Text messages in UTF-8 encoding
     * @param string $from Signature of the sender (eg INFORM)
     * @param integer $date Date for delayed sending the message (the number of seconds since January 1, 1970)
     * @return array
     */
    public function send($to, $text, $from = null, $date = null)
    {
        if($from === null) {
            $from = $this->sender;
        }

        if($date !== null) {
            $this->query['date'] = $date;
        }

        $this->query['to'] = $to;
        $this->query['text'] = $text;
        $this->query['from'] = $from;

        return $this->request($this->sendUrl);
    }

    /**
     * Get balance
     * @return array
     * @throws Exception
     */
    public function balance()
    {
        $data = $this->request($this->balanceUrl);
        if(!array_key_exists('balance', $data)) {
            throw new Exception('SmsAero. Failed to get the balance');
        }
        return $data;
    }

    /**
     * available senders
     * @return array
     * @throws Exception
     */
    public function senders()
    {
        return $this->request($this->sendersUrl);
    }

    /**
     * Request new signature
     * @param $name signature name
     * @return array
     * @throws Exception
     */
    public function sign($name)
    {
        $this->query['sign'] = $name;
        return $this->request($this->signUrl);
    }

    /**
     * Checking the status of the sent message
     * @param $id integer message identifier that is returned when you send the message service
     * @return array
     * @throws Exception
     */
    public function status($id)
    {
        $this->query['id'] = $id;
        return $this->request($this->statusUrl);
    }

    /**
     * Send HLR status request
     * @param $phone string phone number in the format 71234567890
     * @return array
     * @throws Exception
     */
    public function hlr($phone)
    {
        $this->query['phone'] = $phone;
        return $this->request($this->hlrUrl);
    }

    /**
     * Checking HLR status result
     * @param $phone string phone number in the format 71234567890
     * @return array
     * @throws Exception
     */
    public function hlrStatus($id)
    {
        $this->query['id'] = $id;
        return $this->request($this->hlrStatusUrl);
    }
}