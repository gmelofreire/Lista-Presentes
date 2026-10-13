<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

/**
 * @tags Autenticação
 */
class AuthController extends Controller
{
    /**
     * Cadastra um usuário e já devolve um token de acesso.
     */
    public function register(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'username' => ['required', 'string', 'min:3', 'max:30', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()->mixedCase()->numbers()->symbols()],
            'termos' => 'required|accepted',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $dados['name'],
            'email' => $dados['email'],
            'username' => $dados['username'],
            'password' => $dados['password'],
        ]);

        event(new Registered($user));

        return $this->respostaComToken($user, $dados['device_name'] ?? 'app', 201);
    }

    /**
     * Login por e-mail ou username. Bloqueia por 15 minutos após 10 tentativas inválidas.
     */
    public function login(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $chave = 'api-login:'.Str::lower($dados['login']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($chave, 10)) {
            throw ValidationException::withMessages([
                'login' => trans('auth.throttle', ['seconds' => RateLimiter::availableIn($chave), 'minutes' => ceil(RateLimiter::availableIn($chave) / 60)]),
            ]);
        }

        $campo = filter_var($dados['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($campo, $dados['login'])->first();

        if (! $user || ! Hash::check($dados['password'], $user->password)) {
            RateLimiter::hit($chave, 900);

            throw ValidationException::withMessages(['login' => trans('auth.failed')]);
        }

        RateLimiter::clear($chave);

        // Entrar de novo reativa uma conta desativada.
        if ($user->conta_ativa === false || $user->conta_ativa === 0) {
            $user->forceFill(['conta_ativa' => true, 'desativado_em' => null])->save();
        }

        return $this->respostaComToken($user, $dados['device_name'] ?? 'app');
    }

    /**
     * Revoga o token usado nesta requisição.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sessão encerrada.']);
    }

    /**
     * Usuário autenticado.
     */
    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user()->load('perfil'));
    }

    /**
     * Envia o link de redefinição de senha. Sempre responde igual, para não revelar quais e-mails existem.
     */
    public function esqueciSenha(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Se o e-mail existir, enviaremos o link de redefinição.']);
    }

    private function respostaComToken(User $user, string $dispositivo, int $status = 200): JsonResponse
    {
        // Sem isso o UserResource não sabe que é o próprio usuário e omitiria o e-mail.
        request()->setUserResolver(fn () => $user);

        // Um token por dispositivo: novo login no mesmo aparelho substitui o anterior.
        $user->tokens()->where('name', $dispositivo)->delete();

        return response()->json([
            'token' => $user->createToken($dispositivo)->plainTextToken,
            'tipo' => 'Bearer',
            'user' => UserResource::make($user->load('perfil')),
        ], $status);
    }
}
