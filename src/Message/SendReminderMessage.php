<?php

namespace App\Message;

/**
 * Message asynchrone pour les rappels.
 */
final class SendReminderMessage
{
    public function __construct(
        public readonly string $type,
        public readonly int $entityId,
    ) {}
}
