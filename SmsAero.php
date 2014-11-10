<?php
namespace sersid\smsaero;

use Yii;
use yii\base\Exception;

/**
 * SmsAero component
 * @url http://smsaero.ru/
 * @author Sersid <sersONEd@gmail.com>
 * @copyright Copyright &copy; www.sersid.ru 2014
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */
class SmsAero extends \yii\base\Component {
    /**
     * @var string Username
     */
    public $user;

    /**
     * @var string Password
     */
    public $password;

    /**
     * @var string Signature of sender
     */
    public $from = 'INFORM';

    /**
     * @var string Send url
     */
    public $sendUrl = 'https://gate.smsaero.ru/send/';

    /**
     * @var string Balance url
     */
    public $balanceUrl = 'https://gate.smsaero.ru/balance/';

    /**
     * @var array
     */
    protected  $query = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if($this->user === null) {
            throw new Exception('SmsAero. You must enter a "user".');
        }

        if($this->password === null) {
            throw new Exception('SmsAero. You must enter a "password".');
        }

        $this->query = [
            'answer' => 'json',
            'user' => $this->user,
            'password' => md5($this->password),
        ];

        parent::init();
    }

    /**
     * Send sms
     * @param $to Recipient's phone number in the format 71234567890
     * @param $text Text messages in UTF-8 encoding
     * @param string $from Signature of the sender (eg INFORM)
     * @param integer $date Date for delayed sending the message (the number of seconds since January 1, 1970)
     * @return array
     */
    public function send($to, $text, $from = null, $date = null)
    {
        if($from === null) {
            $from = $this->from;
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
     * @return string
     * @throws Exception
     */
    public function balance()
    {
        $data = $this->request($this->balanceUrl);
        if(!array_key_exists('balance', $data)) {
            throw new Exception('SmsAero. Failed to get the balance');
        }
        return $data['balance'];
    }

    /**
     * @param $url string Url
     * @return array
     * @throws Exception
     */
    protected function request($url)
    {
        $data = file_get_contents($url.'?'.http_build_query($this->query));
        $arr = \yii\helpers\Json::decode($data);
        if(isset($arr['result']) && $arr['result'] == 'reject') {
            $mess = isset($arr['reason']) ? ': '.$arr['reason'] : '';
            throw new Exception('SmsAero. '.$arr['result'].$mess);
        }
        return $arr;
    }
}