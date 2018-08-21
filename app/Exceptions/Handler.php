<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maknz\Slack\Client;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send Exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        if (!env('APP_DEBUG', false) && method_exists($e, 'getStatusCode') && $e->getStatusCode() != '404') {
            try {
                $message = "Status code: " . $e->getStatusCode();
                $message .= "\nRequest: " . app('request')->getRequestUri();
                $message .= "\nUser ID: " . (\Auth::check() ? \Auth::user()->id : "Undefine");
                $message .= "\nAdvertiser ID: " . (\Auth::guard('advertiser')->check() ? \Auth::guard('advertiser')->user()->id : "Undefine");
                $message .= "\nMessage: " . $e->getMessage();
                $message .= "\nFile: " . $e->getFile();
                $message .= "\nLine: " . $e->getLine();

                $slackClient = new Client('https://hooks.slack.com/services/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');
                $slackClient->send($message);
            } catch (Exception $ex) {
                \Log::error($ex);
            }
        }
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->wantsJson()) {
            $response = [
                'errors' => [
                    $e->getMessage()
                ]
            ];

            if ($e instanceof ValidationException) {
                $error = $e->validator->errors();
                $response['errors'] = $error->messages();
            }

            $status = 400;

            if ($this->isHttpException($e)) {
                $status = $e->getStatusCode();
            }

            return response()->json($response, $status);
        }

        if ($e instanceof APIRequestException) {
            abort(404);
        }

        return parent::render($request, $e);
    }
}
