<?php
namespace UserAgentParser\Model;

class Bot
{
    private $isBot;

    private $name;

    private $type;

    public function setIsBot($mode)
    {
        $this->isBot = $mode;
    }

    public function getIsBot()
    {
        return $this->isBot;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function toArray()
    {
        return [
            'isBot' => $this->getIsBot(),
            'name'  => $this->getName(),
            'type'  => $this->getType(),
        ];
    }
}
