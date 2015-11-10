<?php
namespace UserAgentParser\Model;

class Device
{
    private $model;

    private $brand;

    private $type;

    private $isMobile;

    private $isTouch;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setIsMobile($isMobile)
    {
        $this->isMobile = $isMobile;
    }

    public function getIsMobile()
    {
        return $this->isMobile;
    }

    public function setIsTouch($isTouch)
    {
        $this->isTouch = $isTouch;
    }

    public function getIsTouch()
    {
        return $this->isTouch;
    }

    public function toArray()
    {
        return [
            'model'    => $this->getModel(),
            'brand'    => $this->getBrand(),
            'type'     => $this->getType(),
            'isMobile' => $this->getIsMobile(),
            'isTouch'  => $this->getIsTouch(),
        ];
    }
}
