<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-7-15 下午2:16
 */
namespace Runner\GatlingMail\Drivers;

use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Resource;
use Runner\GatlingMail\Email;

class SmtpDriver extends DriversAbstract
{

    /**
     * @var Resource
     */
    protected $connect = null;

    /**
     * @var bool
     */
    protected $needResetConnect = false;

    /**
     * @var bool
     */
    protected $isSsl = true;

    /**
     * @var int
     */
    protected $port = 465;

    /**
     * @var int
     */
    protected $timeout = 10;

    /**
     * @var array
     */
    protected $lastResponse;


    /**
     * @param Email $email
     * @return bool
     */
    public function send(Email $email)
    {
        if(is_null($this->connect)) {
            if(false === $this->createConnect()) {
                $this->exception($this->lastResponse['message']);
            }
        }
        if($this->needResetConnect) {
            $this->sendCommand('RSET', 250);
        }

        $this->needResetConnect = true;

        // set mail from
        if(false === $this->sendCommand("MAIL FROM:<{$email->getFromAddress()}>", 250)) {
            $this->exception($this->lastResponse['message']);
        }
        // set addressee
        foreach($email->getTo() as $address) {
            $this->sendCommand("RCPT TO:<{$address['address']}>", 250);
        }
        foreach($email->getCarbonCopy() as $address) {
            $this->sendCommand("RCPT TO:<{$address['address']}>", 250);
        }
        foreach($email->getBlindCarbonCopy() as $address) {
            $this->sendCommand("RCPT TO:<{$address['address']}>", 250);
        }

        if(false === $this->sendCommand("DATA", 354)) {
            $this->exception($this->lastResponse['message']);
        }

        $this->sendCommand('DATE:' . date('D,d M Y H:i:s Z'));

        $command = 'To: ';
        foreach($email->getTo() as $address) {
            $command .= "{$address['name']} <{$address['address']}>,";
        }
        $this->sendCommand(trim($command, ','));

        $this->sendCommand("From: {$email->getFromName()} <{$email->getFromAddress()}>");

        if($cc = $email->getCarbonCopy()) {
            $command = 'Cc: ';
            foreach($cc as $address) {
                $command .= "{$address['name']} <{$address['address']}>,";
            }
            $this->sendCommand(trim($command, ','));
        }

        $replyTo = $email->getReplyTo();
        $this->sendCommand("Reply-To: {$replyTo['name']} <{$replyTo['address']}>");

        $this->sendCommand("SUBJECT: {$email->getSubject()}");

        $messageId = md5(uniqid());

        $this->sendCommand("X-Priority: {$email->getPriority()}");
        $this->sendCommand('MIME-Version: 1.0');
        $this->sendCommand("Content-Type: multipart/mixed;boundary={$messageId}");
        $this->sendCommand('X-Mailer: runner/gatling-mail');
        $this->sendCommand('');

        // set message content
        $this->sendCommand("--{$messageId}");
        $this->sendCommand('Content-Type:text/html;charset=iso-8859-1');
        $this->sendCommand('Content-Transfer-Encoding: 8bit');
        $this->sendCommand('');
        $this->sendCommand($email->getContent());
        $this->sendCommand('');

        // set attachment
        if($attachs = $email->getAttach()) {
            foreach($attachs as $name => $path) {
                $this->sendCommand("--{$messageId}");
                $this->sendCommand('Content-Type: ' . mime_content_type($path) . "; name=\"{$name}\"");
                $this->sendCommand('Content-Transfer-Encoding: base64');
                $this->sendCommand("Content-Disposition: attachment; filename={$name}");
                $this->sendCommand('');
                $this->sendCommand(chunk_split(base64_encode(file_get_contents($path)), 76, "\n"));
                $this->sendCommand('');
            }
        }
        $this->sendCommand("--{$messageId}--");
        $this->sendCommand('');

        // finish
        if(false === $this->sendCommand('.', 250)) {
            $this->exception($this->lastResponse['message']);
        }

        return true;
    }

    /**
     * @return array
     */
    public function sendReturn()
    {
        // TODO: Implement sendReturn() method.
    }


    /**
     * @param bool $flag
     * @return $this
     */
    public function setSsl($flag = true)
    {
        $this->isSsl = !!$flag;

        return $this;
    }


    /**
     * @param int $port
     * @return $this
     */
    public function setPort($port)
    {
        $this->port = intval($port);

        return $this;
    }


    /**
     * @return bool
     */
    protected function createConnect()
    {
        $socketContext = stream_context_create([]);
        $host = ($this->isSsl ? 'ssl://' : '') . $this->config['host'];
        $errorNo = $errorStr = '';
        try {
            $this->connect = stream_socket_client(
                "{$host}:{$this->port}",
                $errorNo,
                $errorStr,
                $this->timeout,
                STREAM_CLIENT_CONNECT,
                $socketContext
            );
        }catch (\Exception $e) {
            $this->connect = fsockopen($host, $this->port, $errorNo, $errorStr, $this->timeout);
        }
        if(is_null($this->connect)) {
            $this->exception('can not create connection to the smtp server');
        }
        if(220 !== $this->readLastResponse()['code']) {
            $this->exception('something wrong...');
        }

        // send hello
        if(false == $this->sendCommand("EHLO {$this->config['username']}", 250)) {
            return false;
        }

        // parse hello response
        $serverCaps = [];
        foreach($this->lastResponse['detail'] as $row) {
            $row = explode(' ', $row);
            $serverCaps[array_shift($row)] = $row;
        }

        // auth login
        if(in_array('LOGIN', $serverCaps['AUTH'])) {
            if(false === $this->sendCommand('AUTH LOGIN', 334)) {
                $this->lastResponse['message'] = base64_decode($this->lastResponse['message']);
                return false;
            }
            if(false === $this->sendCommand(base64_encode($this->config['username']), 334)) {
                $this->lastResponse['message'] = base64_decode($this->lastResponse['message']);
                return false;
            }
            if(false === $this->sendCommand(base64_encode($this->config['password']), 235)) {
                $this->lastResponse['message'] = base64_decode($this->lastResponse['message']);
                return false;
            }
        }else if(in_array('PLAIN', $serverCaps['AUTH'])) {
            $this->exception('I am not ready');
        }

        return true;
    }


    /**
     * @param string $command
     * @param null $code
     * @return bool
     */
    protected function sendCommand($command, $code = null)
    {
        fwrite($this->connect, "{$command}\r\n");

        if(!is_null($code)) {
            if(intval($code) !== $this->readLastResponse()['code']) {
                return false;
            }
        }

        return true;
    }


    /**
     * @return array
     */
    protected function readLastResponse()
    {
        $return = [
            'code'    => '',
            'message' => '',
            'detail'  => [],
        ];
        while(!feof($this->connect)) {
            $line = fgets($this->connect, 515);
            if(!isset($line[3])) {
                break;
            }
            if('-' === $line[3]) {
                $return['detail'][] = trim(substr($line, 4));
                continue;
            }
            $return['code'] = intval(substr($line, 0, 3));
            $return['message'] = trim(substr($line, 3));
            break;
        }
        $this->lastResponse = $return;

        return $return;
    }


    /**
     * @param string $message
     * @throws \Exception
     */
    protected function exception($message)
    {
        $this->sendCommand('QUIT');
        fclose($this->connect);
        throw new \Exception($message);
    }


    public function __destruct()
    {
        $this->sendCommand('QUIT');
        fclose($this->connect);
    }
}
