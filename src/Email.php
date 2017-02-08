<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-7-13 下午12:09
 */
namespace Runner\GatlingMail;

class Email
{

    /**
     * @var array
     */
    protected $addressBook = [];

    /**
     * @var string
     */
    protected $fromName;

    /**
     * @var string
     */
    protected $fromAddress;

    /**
     * @var string
     */
    protected $replayTo;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var array
     */
    protected $attach = [];

    /**
     * @var int 1 3 5
     */
    protected $priority = 3;

    /**
     * @var array
     */
    protected $tags = [];


    /**
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function from($address, $name = '')
    {
        $this->fromName = $name;
        $this->fromAddress = $address;

        return $this;
    }


    /**
     * @param string $address
     * @param string $name
     * @return Email
     */
    public function to($address, $name = '')
    {
        return $this->setAddress($address, $name, 'to');
    }


    /**
     * @param string $address
     * @param string $name
     * @return $this
     */
    public function replyTo($address, $name)
    {
        $this->setAddress($address, $name, 'reply_to');

        return $this;
    }


    /**
     * @param string $address
     * @param string $name
     * @return Email
     */
    public function carbonCopy($address, $name = '')
    {
        return $this->setAddress($address, $name, 'cc');
    }


    /**
     * @param string $address
     * @param string $name
     * @return Email
     */
    public function blindCarbonCopy($address, $name)
    {
        return $this->setAddress($address, $name, 'bcc');
    }


    /**
     * @param string $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }


    /**
     * @param \Closure $callback
     * @return $this
     */
    public function content(\Closure $callback)
    {
        $message = new Message();

        $callback($message);

        $this->message = $message;

        return $this;
    }


    /**
     * @param string $name
     * @param string $filePath
     * @return $this
     */
    public function attach($name, $filePath)
    {
        // TODO 限制附件大小
        $this->attach[$name] = $filePath;

        return $this;
    }


    /**
     * @param int $level
     * @return $this
     */
    public function priority($level)
    {
        $this->priority = $level;

        return $this;
    }


    /**
     * @param string $tag
     * @return $this
     */
    public function tag($tag)
    {
        $this->tags[] = $tag;

        return $this;
    }


    /**
     * @return $this
     */
    public function flushTo()
    {
        $this->addressBook['to'] = [];

        return $this;
    }


    /**
     * @return $this
     */
    public function flushCarbonCopy()
    {
        $this->addressBook['cc'] = [];

        return $this;
    }


    /**
     * @return $this
     */
    public function flushBlindCarbonCopy()
    {
        $this->addressBook['bcc'] = [];

        return $this;
    }


    /**
     * @return $this
     */
    public function flushAddressee()
    {
        return $this->flushTo()->flushCarbonCopy()->flushBlindCarbonCopy();
    }


    /**
     * @return string
     */
    public function getFromAddress()
    {
        return $this->fromAddress;
    }


    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }


    /**
     * @return string
     */
    public function getReplyTo()
    {
        if(!$replyTo = $this->getAddress('reply_to')) {
            return [
                'name'      => $this->fromName,
                'address'   => $this->fromAddress,
            ];
        }
        return $replyTo[0];
    }


    /**
     * @return array
     */
    public function getTo()
    {
        return $this->getAddress('to');
    }


    /**
     * @return array
     */
    public function getCarbonCopy()
    {
        return $this->getAddress('cc');
    }


    /**
     * @return array
     */
    public function getBlindCarbonCopy()
    {
        return $this->getAddress('bcc');
    }


    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }


    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * @return string
     */
    public function getContent()
    {
        return $this->message->getMessage();
    }


    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }


    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }


    /**
     * @return array
     */
    public function getAttach()
    {
        return $this->attach;
    }


    /**
     * @param string $address
     * @param string $name
     * @param string $type
     * @return $this
     */
    protected function setAddress($address, $name, $type)
    {
        if(!isset($this->addressBook[$type]) || !in_array($address, $this->addressBook[$type])) {
            $this->addressBook[$type][] = [
                'name'      => $name,
                'address'   => $address,
            ];
        }

        return $this;
    }


    /**
     * @param $type
     * @return array
     */
    protected function getAddress($type)
    {
        if(!isset($this->addressBook[$type])) {
            return [];
        }
        return $this->addressBook[$type];
    }

}
