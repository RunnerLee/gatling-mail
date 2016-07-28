<?php
/**
 * @author: RunnerLee
 * @email: runnerleer@gmail.com
 * @time: 16-7-14 下午6:57
 */
namespace Runner\GatlingMail;

class Message
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $leftDelimiter = '{';

    /**
     * @var string
     */
    protected $rightDelimiter = '}';


    /**
     * Message constructor.
     * @param string $name
     * @param string $content
     * @param array $parameters
     */
    public function __construct($name = '', $content = '', array $parameters = [])
    {
        $this->name         = $name;
        $this->content      = $content;
        $this->parameters   = $parameters;
    }


    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }


    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }


    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }


    public function setDelimiter($left, $right)
    {
        $this->leftDelimiter = $left;
        $this->rightDelimiter = $right;

        return $this;
    }


    /**
     * @return string
     */
    public function getMessage()
    {
        if(!$this->parameters) {
            return $this->content;
        }

        $keys = array_map(function($v) {
            return "{$this->leftDelimiter}{$v}{$this->rightDelimiter}";
        }, array_keys($this->parameters));

        return str_replace($keys, array_values($this->parameters), $this->content);
    }


}
