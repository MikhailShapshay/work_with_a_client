<?php

namespace App\Http\Middleware;

use Closure;

class HasClient
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
        if (! auth()->check()) {
            return redirect('/')->withFlash('Войдите на сайт чтобы просмотреть страницу');
        }

        if (! client()) {
            return redirect(route('home'))->withFlash('Выберите клиента');
        }

        return $next($request);
    }
}
