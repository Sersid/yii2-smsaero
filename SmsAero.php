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
    public $sendUrl = "http://gate.smsaero.ru/send/";

    /**
     * @var array
     */
    private $_query = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if($this->user === null) {
            throw new Exception("SmsAero. You must enter a \"user\".");
        }

        if($this->password === null) {
            throw new Exception("SmsAero. You must enter a \"password\".");
        }

        $this->_query = [
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
            $this->_query['date'] = $date;
        }

        $this->_query['to'] = $to;
        $this->_query['text'] = $text;
        $this->_query['from'] = $from;

        return $this->_request($this->sendUrl);
    }

    /**
     * @param $url string Url
     * @return array
     */
    private function _request($url)
    {
        $data = file_get_contents($url.'?'.http_build_query($this->_query));
        return \yii\helpers\Json::decode($data);
    }
}