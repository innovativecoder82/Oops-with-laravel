<?php

namespace samarnas\Http\Middleware;

use Closure;

use Config;

use Response;

class CheckApikey
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
       //print_r($request); exit();
    if($request->header('API_KEY') != 'SM@2019') {  

      $json = [
           'status' => false,
           'status_code' => Config::get('constants.no_access'),
           'message' => "Invalid API Key",
      ];

      return Response::json($json);
    }

    return $next($request);
  }

}