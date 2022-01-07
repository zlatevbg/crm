<?php

namespace App\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Closure;

class Ajaxify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get the original response
        $response = $next($request);

        if (!$this->shouldAjaxify($request, $response)) {
            return $response;
        }

        // A 20* response
        if ($response->isSuccessful()) {
            // Get the data we passed to our view
            $data = $response->getOriginalContent()->getData();

            // Return the data passed to view as JSON response
            return response()->json($data);
        }

        // We don't have a successful response,
        // we rather have a redirect like response
        $flashData = $this->getFlashData($request, $response);

        // A 30* response
        if ($response->isRedirection()) {
            // if it's the same address and 'reload' is not set - just return successful JSON response
            if (!isset($flashData['data']['reload']) && in_array(rtrim($response->headers->get('location'), '/'), [rtrim($request->url(), '/'), rtrim($request->headers->get('referer'), '/')])) {
                // Delete flash data, as we've already used them
                $request->session()->forget($flashData['keys']);

                return response()->json($flashData['data'], 200);
            }

            // redirect to other URL
            // $response->getStatusCode()
            // Fix for Ajax 302 Redirection
            // IE 11 works with status code 308, but Edge does not
            return response()->json(null, 200)->header('X-Location', $response->headers->get('location'));
        }

        // Delete flash data, as we've already used them
        $request->session()->forget($flashData['keys']);

        // Return all the flash data as JSON
        return response()->json($flashData['data'], $response->getStatusCode());
    }

    protected function shouldAjaxify($request, $response)
    {
        // If we already have a JSON response we don't need to do anything
        if ($response instanceof JsonResponse) {
            return false;
        }

        // If it's a file download, we won't do anything
        if ($response instanceof BinaryFileResponse) {
            return false;
        }

        // If there is a server (status 50*) error, we won't do anything
        if ($response->isServerError()) {
            return false;
        }

        // It's not a View response
        if ($response->isSuccessful() && !method_exists($response->getOriginalContent(), 'getData')) {
            return false;
        }

        // Now if it's an Ajax request or the clients wants a JSON response or we've
        // a query string param 'ajaxify' then we'll Ajaxify, else we won't.
        return $request->expectsJson() || $request->exists('ajaxify');
    }

    protected function getFlashData($request, $response)
    {
        // Get all session data and convert the array to a Collection object
        $sessionData = collect($request->session()->all())->map(function ($item, $key) {
            if ($key === 'errors') {
                return $item->toArray(); // serialize ErrorMessageBag
            }

            return $item;
        });

        // Filter only flash data from session data
        $flashedKeys = $request->session()->get('_flash.new');
        $flashData = $sessionData->only($flashedKeys);

        return ['keys' => $flashedKeys, 'data' => $flashData];
    }
}
