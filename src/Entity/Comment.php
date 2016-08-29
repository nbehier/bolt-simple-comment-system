<?php

namespace Bolt\Extension\Leskis\BoltSimpleCommentSystem\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment class
 *
 * @author Nicolas Béhier-Dévigne
 */
class Comment
{
    /**
     * @Assert\NotBlank()
     */
    protected $body;

    /**
     * @Assert\NotBlank()
     */
    protected $guid;

    /**
     * @Assert\NotBlank()
     */
    protected $author_display_name;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    protected $author_email;

    /**
     *
     */
    protected $notify;

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function getGuid()
    {
        return $this->guid;
    }

    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    public function getAuthorDisplayName()
    {
        return $this->author_display_name;
    }

    public function setAuthorDisplayName($author_display_name)
    {
        $this->author_display_name = $author_display_name;
    }

    public function getAuthorEmail()
    {
        return $this->author_email;
    }

    public function setAuthorEmail($author_email)
    {
        $this->author_email = $author_email;
    }

    public function getNotify()
    {
        return $this->notify;
    }

    public function setNotify($notify)
    {
        $this->notify = $notify;
    }
}
