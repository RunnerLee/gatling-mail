<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-7-14 下午5:36
 */
namespace Runner\GatlingMail\Drivers;

use Runner\GatlingMail\Email;

class SendCloudDriver extends DriversAbstract
{

    /**
     * @var string
     */
    const GENERAL_SEND_API = 'http://api.sendcloud.net/apiv2/mail/send';

    /**
     * @var string
     */
    const TEMPLATE_SEND_API = 'http://api.sendcloud.net/apiv2/mail/sendtemplate';

    /**
     * @var array
     */
    protected $return;

    /**
     * @var bool
     */
    protected $useAddressList = false;

    /**
     * @var bool
     */
    protected $useNotification = false;

    /**
     * @var bool
     */
    protected $respEmailId = true;

    /**
     * @var bool
     */
    protected $useTemplate = false;


    /**
     * @param Email $email
     * @return bool
     */
    public function send(Email $email)
    {
        $parameters = [
            'apiUser'           => $this->config['api_user'],
            'apiKey'            => $this->config['api_key'],
            'from'              => $email->getFromAddress(),
            'fromName'          => $email->getFromName(),
            'subject'           => $email->getSubject(),
            'cc'                => implode(';', $email->getCarbonCopy()),
            'bcc'               => implode(';', $email->getBlindCarbonCopy()),
            'replyTo'           => $email->getReplyTo(),
            'headers'           => json_encode([
                'X-Priority' => $email->getPriority(),
            ]),
            'respEmailId'       => $this->respEmailId ? 'true' : 'false',
            'useNotification'   => $this->useNotification ? 'true' : 'false',
            'useAddressList'    => $this->useAddressList ? 'true' : 'false',
        ];

        $parameters['xsmtpapi'] = [
            'to' => $email->getTo(),
        ];

        // set use template
        if($this->useTemplate) {
            $parameters['templateInvokeName'] = $email->getMessage()->getName();
            foreach($email->getMessage()->getParameters() as $key => $value) {
                $parameters['xsmtpapi']['%' . $key . '%'] = $value;
            }
        }else {
            $parameters['html'] = $email->getContent();
        }

        // set use address list
        if($this->useAddressList) {
            $parameters['to'] = implode(';', $email->getTo());
        }

        // set label
        if($tags = $email->getTags()) {
            $parameters['labelId'] = $tags[0];
        }

        $parameters['xsmtpapi'] = json_encode($parameters['xsmtpapi']);

        // set attach
        if($attachs = $email->getAttach()) {
            $count = 0;
            foreach($attachs as $name => $path) {
                $parameters["attachments[{$count}]"] = new \CURLFile($path);
                ++$count;
            }
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL             => $this->useTemplate ? self::TEMPLATE_SEND_API : self::GENERAL_SEND_API,
            CURLOPT_POST            => true,
            CURLOPT_CONNECTTIMEOUT  => 10,
            CURLOPT_TIMEOUT         => 10,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_POSTFIELDS      => $parameters,
        ]);

        $result = json_decode(curl_exec($curl), true);

        curl_close($curl);

        if(!is_array($result) || !$result['result']) {
            return false;
        }

        return true;
    }


    /**
     * @param bool $flag
     * @return $this
     */
    public function useTemplate($flag = true)
    {
        $this->useTemplate = !!$flag;

        return $this;
    }


    /**
     * @param bool $flag
     * @return $this
     */
    public function useAddressList($flag = true)
    {
        $this->useAddressList = $flag;

        return $this;
    }


    public function sendReturn()
    {
        // TODO: Implement sendReturn() method.
    }
}
