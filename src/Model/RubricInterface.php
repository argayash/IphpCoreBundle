<?php

namespace Argayash\CoreBundle\Model;

interface RubricInterface
{
    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title);

    /**
     * Get title.
     *
     * @return string $title
     */
    public function getTitle();

    /**
     * Set abstract.
     *
     * @param text $abstract
     */
    public function setAbstract($abstract);

    /**
     * Get abstract.
     *
     * @return text $abstract
     */
    public function getAbstract();

    public function getPath();

    public function getFullPath();

    /**
     * Set status.
     *
     * @param bool $status
     */
    public function setStatus($status);

    /**
     * Get status.
     *
     * @return bool $status
     */
    public function getStatus();

    /**
     * Set created_at.
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get created_at.
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set updated_at.
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updated_at.
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt();
}
