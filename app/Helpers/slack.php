<?php

namespace Helpers;

use Maknz\Slack\Facades\Slack;
class SlackNotification {
    /**
     * @param $message
     * @param string $botName
     * @param $messageType
     * @param string $emoji
     */
    public static function send($message, $botName = "Letro Bot", $messageType = 'info', $emoji = ':letro:')
    {
        $channel = $messageType == 'error' ? env('SLACK_ERROR_CHANNEL') : env('SLACK_INFO_CHANNEL');

        try {
            Slack::from($botName)->to($channel)->withIcon($emoji)->send($message);
        } catch (\Exception $exception) {
            \Log::error($exception);
        }
    }
}