<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Perfil;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.visualizar');

        $busca = trim((string) $this->input('q', ''));
        $usuarioModel = new Usuario();

        $this->view('usuarios/index', [
            'titulo'   => 'Usuários',
            'usuarios' => $usuarioModel->listarTodos($busca ?: null),
            'busca'    => $busca,
        ]);
    }

    public function novoForm(): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.criar');

        $perfilModel = new Perfil();

        $this->view('usuarios/form', [
            'titulo'  => 'Novo usuário',
            'usuario' => null,
            'perfis'  => $perfilModel->todosAtivos(),
            'acao'    => url('/usuarios'),
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.criar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/usuarios/novo');
            return;
        }

        $dados = $this->dadosDoFormulario();
        $senha = (string) $this->input('senha', '');

        $erro = $this->validar($dados, $senha, isNovo: true);
        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/usuarios/novo');
            return;
        }

        $usuarioModel = new Usuario();

        if ($usuarioModel->loginJaExiste($dados['usuario'])) {
            setFlash('erro', 'Este nome de usuário já está em uso.');
            $this->redirect('/usuarios/novo');
            return;
        }

        if (!empty($dados['email']) && $usuarioModel->emailEmUso($dados['email'])) {
            setFlash('erro', 'Este e-mail já está cadastrado.');
            $this->redirect('/usuarios/novo');
            return;
        }

        $dados['senha_hash']           = password_hash($senha, PASSWORD_DEFAULT);
        $dados['precisa_trocar_senha'] = (int) (bool) $this->input('precisa_trocar_senha', 0);

        $id = $usuarioModel->insert($dados);

        registrarAuditoria('usuarios', 'criar', (string) $id, null, array_diff_key($dados, ['senha_hash' => '']));

        setFlash('sucesso', 'Usuário criado com sucesso.');
        $this->redirect('/usuarios/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.visualizar');

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findComPerfil((int) $id);

        if (!$usuario) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $this->view('usuarios/show', [
            'titulo'  => $usuario['nome'],
            'usuario' => $usuario,
        ]);
    }

    public function editarForm(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.editar');

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findComPerfil((int) $id);

        if (!$usuario) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        $perfilModel = new Perfil();

        $this->view('usuarios/form', [
            'titulo'  => 'Editar usuário',
            'usuario' => $usuario,
            'perfis'  => $perfilModel->todosAtivos(),
            'acao'    => url('/usuarios/' . $id),
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/usuarios/' . $id . '/editar');
            return;
        }

        $usuarioModel = new Usuario();
        $usuarioAntigo = $usuarioModel->findComPerfil((int) $id);

        if (!$usuarioAntigo) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        // Impede que o próprio administrador logado se inative ou mude o próprio perfil
        if ((int) $id === Auth::id()) {
            $novoStatus = $this->input('status', 'ativo');
            if ($novoStatus !== 'ativo') {
                setFlash('erro', 'Você não pode inativar a sua própria conta.');
                $this->redirect('/usuarios/' . $id . '/editar');
                return;
            }
        }

        $dados = $this->dadosDoFormulario();
        $senha = (string) $this->input('senha', '');

        $erro = $this->validar($dados, $senha, isNovo: false);
        if ($erro) {
            setFlash('erro', $erro);
            $this->redirect('/usuarios/' . $id . '/editar');
            return;
        }

        if ($usuarioModel->loginJaExiste($dados['usuario'], (int) $id)) {
            setFlash('erro', 'Este nome de usuário já está em uso.');
            $this->redirect('/usuarios/' . $id . '/editar');
            return;
        }

        if (!empty($dados['email']) && $usuarioModel->emailEmUso($dados['email'], (int) $id)) {
            setFlash('erro', 'Este e-mail já está cadastrado.');
            $this->redirect('/usuarios/' . $id . '/editar');
            return;
        }

        $dados['precisa_trocar_senha'] = (int) (bool) $this->input('precisa_trocar_senha', 0);

        $usuarioModel->update((int) $id, $dados);

        // Troca de senha opcional: só altera se o campo vier preenchido
        if ($senha !== '') {
            if (strlen($senha) < 8) {
                setFlash('erro', 'A senha deve ter pelo menos 8 caracteres.');
                $this->redirect('/usuarios/' . $id . '/editar');
                return;
            }
            $usuarioModel->trocarSenha((int) $id, $senha);
        }

        registrarAuditoria('usuarios', 'editar', $id, $usuarioAntigo, $dados);

        setFlash('sucesso', 'Usuário atualizado com sucesso.');
        $this->redirect('/usuarios/' . $id);
    }

    /**
     * Força reset de senha via ação dedicada (POST separado).
     * Define uma senha temporária e exige troca no próximo login.
     */
    public function resetSenha(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.editar');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/usuarios/' . $id);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find((int) $id);

        if (!$usuario) {
            http_response_code(404);
            require APP_PATH . '/views/errors/404.php';
            return;
        }

        // Gera senha temporária aleatória de 10 caracteres
        $senhaTemp = bin2hex(random_bytes(5)); // ex: a3f8c21d04

        $usuarioModel->trocarSenha((int) $id, $senhaTemp);
        // Força troca no próximo login
        $usuarioModel->update((int) $id, ['precisa_trocar_senha' => 1]);

        registrarAuditoria('usuarios', 'reset_senha', $id, null, null);

        setFlash('sucesso', "Senha redefinida. Senha temporária: <strong>{$senhaTemp}</strong> — anote e entregue ao usuário.");
        $this->redirect('/usuarios/' . $id);
    }

    public function excluir(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('usuarios.excluir');

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/usuarios/' . $id);
            return;
        }

        // Impede auto-exclusão
        if ((int) $id === Auth::id()) {
            setFlash('erro', 'Você não pode inativar a sua própria conta.');
            $this->redirect('/usuarios/' . $id);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->find((int) $id);

        if ($usuario) {
            $usuarioModel->excluirLogicamente((int) $id);
            registrarAuditoria('usuarios', 'excluir', $id, $usuario, null);
        }

        setFlash('sucesso', 'Usuário inativado com sucesso.');
        $this->redirect('/usuarios');
    }

    // ------------------------------------------------------------------
    // Helpers privados
    // ------------------------------------------------------------------

    private function dadosDoFormulario(): array
    {
        $dados = [];

        // Campos de texto simples
        foreach (['nome', 'usuario', 'email'] as $campo) {
            $valor = trim((string) $this->input($campo, ''));
            $dados[$campo] = $valor === '' ? null : $valor;
        }

        // perfil_id como inteiro
        $dados['perfil_id'] = (int) $this->input('perfil_id', 0) ?: null;

        // status: aceita apenas os valores do ENUM
        $status = $this->input('status', 'ativo');
        $dados['status'] = in_array($status, ['ativo', 'inativo'], true) ? $status : 'ativo';

        return $dados;
    }

    /**
     * Valida campos obrigatórios e regras de negócio.
     * Retorna a mensagem de erro ou null se tudo estiver ok.
     */
    private function validar(array $dados, string $senha, bool $isNovo): ?string
    {
        if (empty($dados['nome'])) {
            return 'O nome do usuário é obrigatório.';
        }

        if (empty($dados['usuario'])) {
            return 'O login é obrigatório.';
        }

        if (!preg_match('/^[a-zA-Z0-9_.\-]{3,50}$/', $dados['usuario'])) {
            return 'O login deve ter entre 3 e 50 caracteres e conter apenas letras, números, _ . -';
        }

        if (!empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Informe um e-mail válido.';
        }

        if (empty($dados['perfil_id'])) {
            return 'Selecione um perfil para o usuário.';
        }

        if ($isNovo) {
            if ($senha === '') {
                return 'Defina uma senha para o novo usuário.';
            }
            if (strlen($senha) < 8) {
                return 'A senha deve ter pelo menos 8 caracteres.';
            }
        }

        return null;
    }
}
