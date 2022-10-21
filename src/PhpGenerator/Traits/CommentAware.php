<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Nette\PhpGenerator\Traits;


/**
 * @internal
 */
trait CommentAware
{
	private ?string $comment = null;
    private ?string $prefixComment = null;
    private ?string $suffixComment = null;

    private ?string $prefixCommentInline = null;
    private ?string $suffixCommentInline = null;

	public function setComment(?string $val): static
	{
		$this->comment = $val;
		return $this;
	}

    public function setPrefixComment(?string $val, bool $inline = false): static
    {
        if ($inline) {
            $this->prefixCommentInline = $val;
        } else {
            $this->prefixComment = $val;
        }
        return $this;
    }

    public function setSuffixComment(?string $val, bool $inline = false): static
    {
        if ($inline) {
            $this->suffixCommentInline = $val;
        } else {
            $this->suffixComment = $val;
        }
        return $this;
    }

	public function getComment(): ?string
	{
		return $this->comment;
	}

    public function getPrefixComment(): ?string
    {
        return $this->prefixComment;
    }

    public function getSuffixComment(): ?string
    {
        return $this->suffixComment;
    }

    public function getPrefixInlineComment(): ?string
    {
        return $this->prefixCommentInline;
    }

    public function getSuffixInlineComment(): ?string
    {
        return $this->suffixCommentInline;
    }

	public function addComment(string $val): static
	{
		$this->comment .= $this->comment ? "\n$val" : $val;
		return $this;
	}
}
