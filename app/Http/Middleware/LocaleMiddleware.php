<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{

    public static $mainLanguage = 'ua'; 
    public static $languages = ['en', 'ua']; 

    public static function getLocale()
    {
        $uri = request()->path();

        $segmentsURI = explode('/',$uri); 

        if (!empty($segmentsURI[0]) && in_array($segmentsURI[0], self::$languages)) {
            if ($segmentsURI[0] != self::$mainLanguage) return $segmentsURI[0];
        }
        return null;
        
    }


    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = self::getLocale();
        if($locale) App::setLocale($locale);
        else App::setLocale(self::$mainLanguage);

        return $next($request);
    }
}
