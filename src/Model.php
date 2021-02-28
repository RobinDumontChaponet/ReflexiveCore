<?php

declare(strict_types=1);

namespace Reflexive\Core;

abstract class Model implements \JsonSerializable
{
    public function __construct(
		protected int|string $id = -1,
	) {}

    public function getId(): int|string
    {
        return $this->id;
    }

    public function setId(int|string $id): void
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return  self::class.' [ id: '.$this->id.(((!get_parent_class())) ? ' ]' : ';  ');
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
        ];
    }
}
