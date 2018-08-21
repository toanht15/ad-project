<?php

namespace App\Providers;

use Helpers\SlackNotification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!env('AUTO_ERROR_NOTIFICATION')) {
            return;
        }

        \Log::listen(function ($level, $exception) {
            switch ($level) {
                case 'error':
                    if (is_object($exception)) {
                        $message = "------------------------------------------------------------";
                        $message .= "\n Exception: " . get_class($exception);
                        $message .= \Auth::check() ? "\n User ID : " . \Auth::user()->id : "";
                        $message .= \Auth::guard('advertiser')->check() ? "\n Advertiser ID: " . \Auth::guard('advertiser')->user()->id : "";
                        if (method_exists($exception, 'getFile')) {
                            $message .= "\n File: ". $exception->getFile();
                        }
                        if (method_exists($exception, 'getLine')) {
                            $message .= "\n Line: ". $exception->getLine();
                        }
                        if (method_exists($exception, 'getMessage')) {
                            $message .= "\n Message: ". $exception->getMessage();
                        }

                        $file = 'laravel-' . (new \DateTime())->format('Y-m-d') . '.log';
                        $link =  url('admin/logs?l=') . base64_encode($file);
                        $message .= "\n Link: " . $link;
                    } elseif(is_string($exception)) {
                        $message = $exception;
                    } else {
                        $message = 'Undefined error';
                    }

                    SlackNotification::send($message, "Error", $level, ':sos:');
                    return;
                case 'warning':
                    SlackNotification::send((string)$exception, "Alert", $level);
                    return;
                default:
                    return;
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
