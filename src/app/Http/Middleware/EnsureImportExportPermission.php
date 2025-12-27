<?php

namespace App\Http\Middleware;

use App\Orchid\Permissions\Rbac;
use Closure;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Alert;
use Symfony\Component\HttpFoundation\Response;

class EnsureImportExportPermission
{
    /**
     * Проверяет доступ к операциям импорта и экспорта.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->hasAccess(Rbac::PERMISSION_IMPORTS)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('У вас нет прав на импорт и экспорт данных.'),
                ], Response::HTTP_FORBIDDEN);
            }

            Alert::warning(__('У вас нет прав на импорт и экспорт данных.'));

            return redirect()->route('platform.main');
        }

        return $next($request);
    }
}
