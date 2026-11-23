<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante que um usuário desativado perca o acesso, tanto no login quanto
 * em sessões que já estavam abertas quando a conta foi desativada.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Comparação estrita: apenas false/0 bloqueia. Um atributo não carregado
        // (null) não deve derrubar a sessão.
        if ($user !== null && $user->is_active === false) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                abort(403, 'Sua conta está desativada. Procure um administrador.');
            }

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Sua conta está desativada. Procure um administrador.']);
        }

        return $next($request);
    }
}
