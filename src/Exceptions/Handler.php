<?php

namespace Recca0120\LaravelTracy\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Recca0120\LaravelTracy\Helper;
use Symfony\Component\Debug\ExceptionHandler as SymfonyDisplayer;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException',
        'Illuminate\Database\Eloquent\ModelNotFoundException',
    ];

    /*
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */

    public function render($request, Exception $e)
    {
        if (method_exists($this, 'isUnauthorizedException')) {
            $response = parent::render($request, $e);
        } else {
            if ($this->isHttpException($e)) {
                $status = $e->getStatusCode();
                if (view()->exists("errors.{$status}")) {
                    $response = response()->view("errors.{$status}", [], $status);
                } else {
                    $response = $this->convertExceptionToResponse($e);
                }
            } else {
                $response = $this->toIlluminateResponse($this->convertExceptionToResponse($e), $e);
            }
        }

        return Helper::appendDebuggerInfo($request, $response);
    }

    protected function convertExceptionToResponse(Exception $e)
    {
        $debug = config('app.debug');
        if ($debug === false) {
            return (new SymfonyDisplayer(config('app.debug')))->createResponse($e);
        } else {
            return Helper::getHttpResponse(Helper::getBlueScreen($e), $e);
        }
    }
}
