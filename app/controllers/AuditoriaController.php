<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Auditoria;
use App\Models\Usuario;

class AuditoriaController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('auditoria.visualizar');

        $filtros = [
            'modulo'     => (string) $this->input('modulo', ''),
            'usuario_id' => (string) $this->input('usuario_id', ''),
            'data_de'    => (string) $this->input('data_de', ''),
            'data_ate'   => (string) $this->input('data_ate', ''),
            'busca'      => trim((string) $this->input('busca', '')),
        ];

        $auditoriaModel = new Auditoria();
        $usuarioModel = new Usuario();

        $this->view('auditoria/index', [
            'titulo'    => 'Auditoria',
            'registros' => $auditoriaModel->listarComFiltros(array_filter($filtros)),
            'modulos'   => $auditoriaModel->listarModulosDistintos(),
            'usuarios'  => $usuarioModel->all('nome'),
            'filtros'   => $filtros,
            'total'     => $auditoriaModel->contarTotal(),
        ]);
    }
}
