<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\RateLimiter;
use App\Models\Usuario;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'titulo' => 'Entrar',
        ], withLayout: false);
    }

    public function login(): void
    {
        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/login');
            return;
        }

        // Verifica bloqueio por IP antes de qualquer consulta ao banco
        $checagemIp = RateLimiter::verificar(Auth::clientIp());
        if ($checagemIp['bloqueado']) {
            registrarAuditoria('auth', 'login_bloqueado_ip', Auth::clientIp());
            setFlash('erro', $checagemIp['erro']);
            $this->redirect('/login');
            return;
        }

        $login = trim((string) $this->input('usuario', ''));
        $senha = (string) $this->input('senha', '');

        if ($login === '' || $senha === '') {
            setFlash('erro', 'Informe usuário e senha.');
            $this->redirect('/login');
            return;
        }

        $resultado = Auth::attempt($login, $senha);

        if (!$resultado['ok']) {
            registrarAuditoria('auth', 'login_falha', $login);
            setFlash('erro', $resultado['erro']);
            $this->redirect('/login');
            return;
        }

        registrarAuditoria('auth', 'login_sucesso', $login);

        if (Auth::precisaTrocarSenha()) {
            $this->redirect('/trocar-senha');
            return;
        }

        $this->redirect('/dashboard');
    }

    public function trocarSenhaForm(): void
    {
        $this->requireLogin();

        $this->view('auth/trocar_senha', [
            'titulo' => 'Trocar senha',
        ], withLayout: false);
    }

    public function trocarSenha(): void
    {
        $this->requireLogin();

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/trocar-senha');
            return;
        }

        $novaSenha = (string) $this->input('nova_senha', '');
        $confirmacao = (string) $this->input('confirmacao_senha', '');

        if (strlen($novaSenha) < 8) {
            setFlash('erro', 'A nova senha deve ter pelo menos 8 caracteres.');
            $this->redirect('/trocar-senha');
            return;
        }

        if ($novaSenha !== $confirmacao) {
            setFlash('erro', 'A confirmação de senha não confere.');
            $this->redirect('/trocar-senha');
            return;
        }

        $usuarioModel = new Usuario();
        $usuarioModel->trocarSenha(Auth::id(), $novaSenha);

        $_SESSION['precisa_trocar_senha'] = false;

        registrarAuditoria('auth', 'senha_alterada', (string) Auth::id());

        setFlash('sucesso', 'Senha alterada com sucesso.');
        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        if (Auth::check()) {
            registrarAuditoria('auth', 'logout', (string) Auth::id());
        }

        Auth::logout();
        $this->redirect('/login');
    }
}
