<?php

declare(strict_types=1);

namespace Cellar\Tactician\Event;

use Symfony\Component\EventDispatcher\Event;

final class CommandHandledEvent extends Event
{
	/** @var object */
	private $command;

	public function __construct($command)
	{
		$this->command = $command;
	}

	public function getCommand()
	{
		return $this->command;
	}
}