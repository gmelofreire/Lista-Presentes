<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        // Sem proxy por padrão (uso local). Com túnel (ex.: ngrok) defina TRUSTED_PROXIES; "*" só por opt-in explícito.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES') === '*' ? '*' : (env('TRUSTED_PROXIES') ? explode(',', env('TRUSTED_PROXIES')) : null));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Toda rota /api responde JSON, mesmo sem o header Accept.
        $exceptions->shouldRenderJsonWhen(fn (\Illuminate\Http\Request $request) => $request->is('api/*') || $request->expectsJson());

        $exceptions->render(function (\App\Exceptions\RegraDeNegocioException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], $e->status);
            }

            if ($e->status === 403) {
                return app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
                    ->render($request, new \Symfony\Component\HttpKernel\Exception\HttpException(403, $e->getMessage()));
            }

            return back()->withErrors(['error' => $e->getMessage()])->with('error', $e->getMessage());
        });
    })->create();
