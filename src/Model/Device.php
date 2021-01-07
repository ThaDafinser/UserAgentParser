<?php

namespace UserAgentParser\Model;

/**
 * Device model.
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Device
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $brand;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $isMobile;

    /**
     * @var bool
     */
    private $isTouch;

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param bool $isMobile
     */
    public function setIsMobile($isMobile)
    {
        $this->isMobile = $isMobile;
    }

    /**
     * @return bool
     */
    public function getIsMobile()
    {
        return $this->isMobile;
    }

    /**
     * @param bool $isTouch
     */
    public function setIsTouch($isTouch)
    {
        $this->isTouch = $isTouch;
    }

    /**
     * @return bool
     */
    public function getIsTouch()
    {
        return $this->isTouch;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'model' => $this->getModel(),
            'brand' => $this->getBrand(),
            'type' => $this->getType(),
            'isMobile' => $this->getIsMobile(),
            'isTouch' => $this->getIsTouch(),
        ];
    }
}
