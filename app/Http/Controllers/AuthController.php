<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function loginSubmitForm(Request $request)
    {
        // Validação dos dados do formulário de login
        $request->validate(
            [
                'username' => 'required|email',
                'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/',
            ],
            [
                'username.required' => 'O campo de usuário é obrigatório.',
                'username.email' => 'O campo de usuário deve ser um endereço de e-mail válido.',
                'password.required' => 'O campo de senha é obrigatório.',
                'password.regex' => 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.',
            ]
        );

        // Autenticação do usuário
        $user = User::where('email', trim($request->username))
            ->where('active', true)
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '<', now());
            })
            ->first();

        // Verifica se o usuário existe e se a senha está correta
        if ($user && Hash::check(trim($request->password), $user->password)) {
            $this->loginUser($user);
            return redirect()->route('dashboard');
        } else {
            // Autenticação falhou
            return redirect()->back()
                ->with(['server_error' => 'Credenciais inválidas! Verifique seu e-mail e senha.'])
                ->withInput();
        }
    }

    private function loginUser($user)
    {
        // Atualiza o campo last_login_at com a data e hora atual
        $user->last_login = now();
        $user->code = null;
        $user->code_expiration = null;
        $user->blocked_until = null;
        $user->save();
        // Autentica o usuário
        auth()->login($user);
    }

    public function logout()
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    }

    public function changePassword()
    {
        return view('auth.change_password_form', [
            'subtitle' => 'Alterar Senha',
        ]);
    }

    public function changePasswordSubmit(Request $request)
    {
        // Validação dos dados do formulário de alteração de senha
        $request->validate(
            [
                'current_password' => 'required',
                'new_password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/|confirmed',
            ],
            [
                'current_password.required' => 'O campo de senha atual é obrigatório.',
                'new_password.required' => 'O campo de nova senha é obrigatório.',
                'new_password.regex' => 'A nova senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula, uma letra minúscula e um número.',
                'new_password.confirmed' => 'A confirmação da nova senha não corresponde.',
            ]
        );

        $user = auth()->user();

        // Verifica se a senha atual está correta
        if (!Hash::check(trim($request->current_password), $user->password)) {
            return redirect()->back()
                ->with('server_error' , 'A senha atual está incorreta.')
                ->withInput();
        }

        // Atualiza a senha do usuário
        $user->password = Hash::make(trim($request->new_password));
        $user->save();

        return redirect()->route('dashboard')->with('message', 'Senha alterada com sucesso!');
    }

}
