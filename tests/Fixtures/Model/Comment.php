<?php

declare(strict_types=1);

namespace HTC\StrictFormMapper\Tests\Fixtures\Model;

class Comment
{
    private $post;

    private $message;

    public function __construct(Post $post, string $message)
    {
        $this->post = $post;
        $this->message = $message;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): void
    {
        $this->post = $post;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
