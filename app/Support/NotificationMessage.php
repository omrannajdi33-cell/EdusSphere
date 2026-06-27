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
        if (isset($data['message']) && is_string($data['message'])) {
            return $data['message'];
        }

        return match ($type) {
            'activity_submitted' => ($data['student_name'] ?? 'Un élève').' a soumis « '.($data['activity_title'] ?? '').' »',
            'activity_corrected' => '« '.($data['activity_title'] ?? 'Ton activité').' » a été corrigée'.self::scoreSuffix($data),
            'activity_returned' => '« '.($data['activity_title'] ?? 'Ton activité').' » a été renvoyée — tu peux la modifier',
            'exam_submitted' => ($data['student_name'] ?? 'Un élève').' a soumis « '.($data['exam_title'] ?? '').' »',
            'exam_corrected' => '« '.($data['exam_title'] ?? 'Ton examen').' » a été corrigé'.self::scoreSuffix($data),
            'exam_hand_raise' => ($data['student_name'] ?? 'Un élève').' lève la main pendant « '.($data['exam_title'] ?? 'examen').' »',
            'reward_redeemed' => ($data['student_name'] ?? 'Un élève').' a utilisé '.($data['reward_cost'] ?? '?').' pts pour « '.($data['reward_name'] ?? 'récompense').' »',
            default => self::label($type),
        };
    }

    private static function scoreSuffix(array $data): string
    {
        return isset($data['score']) ? ' · '.$data['score'].' %' : '';
    }
}
