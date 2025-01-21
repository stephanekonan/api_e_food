<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationService
{
    /**
     * Envoie une notification via Firebase Cloud Messaging (FCM).
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @return bool|string Retourne true si la notification est envoyée ou un message d'erreur.
     */
    public function sendNotification(string $token, string $title, string $body)
    {
        try {
            $messaging = Firebase::messaging();
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification([
                    'title' => $title,
                    'body' => $body,
                ]);

            $messaging->send($message);

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
