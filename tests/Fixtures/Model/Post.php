<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Tests\Fixtures\Model;

use function array_search;
use Doctrine\Common\Collections\ArrayCollection;

class Post
{
    private $tags = ['foo', 'bar'];

    private $subject;

    private $body;

    private $comments;

    public function __construct(string $subject, string $body)
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->comments = new ArrayCollection();
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function addTag(string $tag): void
    {
        $this->tags[] = $tag;
    }

    public function removeTag(string $tag): void
    {
        $key = array_search($tag, $this->tags, true);
        if (false !== $key) {
            unset($this->tags[$key]);
        }
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /** @return Comment[] */
    public function getComments(): array
    {
        return $this->comments->toArray();
    }

    public function addComment(Comment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
        }
    }

    public function removeComment(Comment $comment): void
    {
        $this->comments->removeElement($comment);
    }
}
