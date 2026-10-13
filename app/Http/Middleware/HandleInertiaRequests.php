<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            // Mensagens de uma unica exibicao. Os controllers usam 'status' e 'success'; ambos viram flash.status.
            'flash' => fn () => [
                'status' => $request->session()->get('status') ?? $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'auth' => [
                // Só o essencial; relações pesadas (grupos, categorias, presentes, amizades) vêm por página.
                'user' => $request->user() ? array_merge(
                    $request->user()->only(['id', 'name', 'email', 'username', 'email_verified_at']),
                    ['perfil' => $request->user()->perfil]
                ) : null,
            ],
        ];
    }
}
