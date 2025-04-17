<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleVite
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request is for a Vite asset
        if (strpos($request->path(), '@vite') !== false || 
            strpos($request->path(), 'resources/js') !== false) {
            // Get the Vite development server URL
            $viteServerUrl = 'http://localhost:5173';
            
            // Proxy the request to the Vite server
            $ch = curl_init($viteServerUrl . '/' . $request->path());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            // Set headers from the original request
            $headers = [];
            foreach ($request->headers as $key => $value) {
                $headers[] = $key . ': ' . $value[0];
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            if ($httpcode === 200) {
                return response($response)->header('Content-Type', $contentType);
            }
        }
        
        return $next($request);
    }
} 
