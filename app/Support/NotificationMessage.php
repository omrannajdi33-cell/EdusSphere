<?php

namespace App\Support;

class NotificationMessage
{
    public static function label(string $type): string
    {
        return config("notifications.labels.{$type}", $type);
    }

    public static function body(string $type, array $data): string
    {
        $resolver = config("notifications.messages.{$type}");

        if (is_callable($resolver)) {
            return $resolver($data);
        }

        return $data['message'] ?? self::label($type);
    }
}
