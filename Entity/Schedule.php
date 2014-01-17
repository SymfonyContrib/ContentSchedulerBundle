<?php
/**
 *
 */

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\Entity;

class Schedule
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var \DateTime
     */
    protected $when;

    public function _construct()
    {}

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $when
     */
    public function setWhen($when)
    {
        $this->when = $when;
    }

    /**
     * @return \DateTime
     */
    public function getWhen()
    {
        return $this->when;
    }

}
