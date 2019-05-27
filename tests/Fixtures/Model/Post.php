<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Tests\Fixtures\Model;

use function array_search;

class Post
{
    private $tags = ['foo', 'bar'];

    private $subject;

    private $body;

    public function __construct(string $subject, string $body)
    {
        $this->subject = $subject;
        $this->body = $body;
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
}
