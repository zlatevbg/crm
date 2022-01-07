<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Mail;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use App\Mail\LaravelException;
use App\Facades\Domain;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\MessageBag;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Session;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (\App::environment('production') && $this->shouldReport($exception)) {
            $this->sendException($exception); // sends an email
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            if ($request->expectsJson()) {
                $request->flashExcept('_token');
                $request->session()->flash('errors', new MessageBag([trans('text.tokenMismatchException')]));
                // Fix for Ajax 302 Redirection
                // IE 11 works with status code 308, but Edge does not
                return response()->json(null, 200)->header('X-Location', url()->previous());
            }

            return redirect()->back()->withInput($request->except('_token'))->withErrors([trans('text.tokenMismatchException')]);
        } elseif ($exception instanceof \Mailgun\Exception) {
            return back()->withErrors($exception->getMessage());
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $error = trans('auth.unauthenticated');

        if ($request->expectsJson()) {
            return response()->json(['errors' => $error], 401); // 200
        }

        Session::flash('errors', new MessageBag([$error]));

        return redirect()->guest(Domain::guest());
    }

    public function sendException(Exception $exception)
    {
        try {
            $e = FlattenException::create($exception);

            $handler = new SymfonyExceptionHandler();

            $html = $handler->getHtml($e);

            $crawler = new Crawler($html);

            $body = $crawler->filter('body > div')->eq(1)->html();

            $subject = request()->url() . ' - ' . request()->ip();

            Mail::to('mitko@sunsetresort.bg')->send(new LaravelException($subject, $body));
        } catch (Exception $ex) {
        }
    }
}
