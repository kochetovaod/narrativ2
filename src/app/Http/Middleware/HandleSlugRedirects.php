<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleSlugRedirects
{
    /**
     * Перенаправляет пользователя на новый URL, если для пути есть сохраненный редирект.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            $path = '/'.ltrim($request->path(), '/');
            $normalizedPath = $path === '//' ? '/' : $path;

            $redirect = Redirect::query()
                ->where('is_active', true)
                ->where('from_path', $normalizedPath)
                ->first();

            if ($redirect !== null && $redirect->to_path !== $normalizedPath) {
                return redirect($redirect->to_path, $redirect->code ?? 301);
            }
        }

        return $next($request);
    }
}
