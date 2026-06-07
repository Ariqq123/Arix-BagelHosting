<?php

namespace Pterodactyl\Exceptions\Service;

use Pterodactyl\Exceptions\PterodactylException;

class PackHostException extends PterodactylException
{
    /**
     * Exception thrown when a pack host operation fails (e.g., upload, download).
     */
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous ? $previous->getCode() : 0, $previous);
    }
}
