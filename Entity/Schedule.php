<?php

namespace SymfonyContrib\Bundle\ContentSchedulerBundle\Entity;

/**
 * Content schedule entity.
 */
class Schedule
{
    const ACTION_PUBLISH   = 'publish';
    const ACTION_UNPUBLISH = 'unpublish';

    /** @var  int */
    protected $id;

    /** @var  string */
    protected $action;

    /** @var  \DateTime */
    protected $when;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Schedule
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     *
     * @return Schedule
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * @param \DateTime $when
     *
     * @return Schedule
     */
    public function setWhen($when)
    {
        $this->when = $when;

        return $this;
    }
}
