<?php

/**
 * Definição de rotas do sistema.
 * @var \App\Core\Router $router
 */

use App\Controllers\AgendaController;
use App\Controllers\AuditoriaController;
use App\Controllers\AuthController;
use App\Controllers\BackupController;
use App\Controllers\ClienteController;
use App\Controllers\ContratoController;
use App\Controllers\CrmController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentoController;
use App\Controllers\FinanceiroController;
use App\Controllers\FornecedorController;
use App\Controllers\MaterialController;
use App\Controllers\MedidaController;
use App\Controllers\ProducaoController;
use App\Controllers\ProvaController;
use App\Controllers\RelatorioController;
use App\Controllers\VestidoController;

// Redireciona a raiz para o dashboard (que por sua vez exige login)
$router->get('/', [DashboardController::class, 'index']);

// Autenticação
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/trocar-senha', [AuthController::class, 'trocarSenhaForm']);
$router->post('/trocar-senha', [AuthController::class, 'trocarSenha']);

// Dashboard
$router->get('/dashboard', [DashboardController::class, 'index']);

// Clientes
$router->get('/clientes', [ClienteController::class, 'index']);
$router->get('/clientes/novo', [ClienteController::class, 'novoForm']);
$router->post('/clientes', [ClienteController::class, 'store']);
$router->get('/clientes/{id}', [ClienteController::class, 'show']);
$router->get('/clientes/{id}/editar', [ClienteController::class, 'editarForm']);
$router->post('/clientes/{id}', [ClienteController::class, 'update']);
$router->post('/clientes/{id}/excluir', [ClienteController::class, 'excluir']);

// CRM
$router->get('/crm', [CrmController::class, 'kanban']);
$router->post('/crm/mover', [CrmController::class, 'mover']);

// Agenda
$router->get('/agenda', [AgendaController::class, 'index']);
$router->get('/agenda/novo', [AgendaController::class, 'novoForm']);
$router->post('/agenda', [AgendaController::class, 'store']);
$router->get('/agenda/{id}/editar', [AgendaController::class, 'editarForm']);
$router->post('/agenda/{id}', [AgendaController::class, 'update']);
$router->post('/agenda/{id}/excluir', [AgendaController::class, 'excluir']);

// Financeiro
$router->get('/financeiro', [FinanceiroController::class, 'index']);
$router->get('/financeiro/receber', [FinanceiroController::class, 'receber']);
$router->get('/financeiro/receber/novo', [FinanceiroController::class, 'novoReceberForm']);
$router->post('/financeiro/receber', [FinanceiroController::class, 'storeReceber']);
$router->post('/financeiro/receber/{id}/pago', [FinanceiroController::class, 'marcarReceberPago']);
$router->get('/financeiro/pagar', [FinanceiroController::class, 'pagar']);
$router->get('/financeiro/pagar/novo', [FinanceiroController::class, 'novoPagarForm']);
$router->post('/financeiro/pagar', [FinanceiroController::class, 'storePagar']);
$router->post('/financeiro/pagar/{id}/pago', [FinanceiroController::class, 'marcarPagarPago']);

// Contratos
$router->get('/contratos', [ContratoController::class, 'index']);
$router->get('/contratos/novo', [ContratoController::class, 'novoForm']);
$router->post('/contratos', [ContratoController::class, 'store']);
$router->get('/contratos/{id}', [ContratoController::class, 'show']);
$router->post('/contratos/{id}/gerar-pdf', [ContratoController::class, 'gerarPdf']);

// Relatórios
$router->get('/relatorios', [RelatorioController::class, 'index']);
$router->get('/relatorios/comercial', [RelatorioController::class, 'comercial']);
$router->get('/relatorios/financeiro', [RelatorioController::class, 'financeiro']);
$router->get('/relatorios/producao', [RelatorioController::class, 'producao']);
$router->get('/relatorios/estoque', [RelatorioController::class, 'estoque']);

// -----------------------------------------------------------------
// Etapa 6: Auditoria na interface e Backup guiado
// -----------------------------------------------------------------

// Auditoria
$router->get('/auditoria', [AuditoriaController::class, 'index']);

// Configurações / Backup
$router->get('/configuracoes/backup', [BackupController::class, 'index']);
$router->post('/configuracoes/backup/banco', [BackupController::class, 'exportarBanco']);
$router->post('/configuracoes/backup/uploads', [BackupController::class, 'exportarUploads']);

// Estoque
$router->get('/estoque', [MaterialController::class, 'index']);
$router->get('/estoque/novo', [MaterialController::class, 'novoForm']);
$router->post('/estoque', [MaterialController::class, 'store']);
$router->get('/estoque/{id}', [MaterialController::class, 'show']);
$router->get('/estoque/{id}/editar', [MaterialController::class, 'editarForm']);
$router->post('/estoque/{id}', [MaterialController::class, 'update']);
$router->post('/estoque/{id}/movimentar', [MaterialController::class, 'movimentar']);
$router->post('/estoque/{id}/excluir', [MaterialController::class, 'excluir']);

// Fornecedores
$router->get('/fornecedores', [FornecedorController::class, 'index']);
$router->get('/fornecedores/novo', [FornecedorController::class, 'novoForm']);
$router->post('/fornecedores', [FornecedorController::class, 'store']);
$router->get('/fornecedores/{id}', [FornecedorController::class, 'show']);
$router->get('/fornecedores/{id}/editar', [FornecedorController::class, 'editarForm']);
$router->post('/fornecedores/{id}', [FornecedorController::class, 'update']);
$router->post('/fornecedores/{id}/excluir', [FornecedorController::class, 'excluir']);

// Documentos (anexados à ficha da cliente)
$router->post('/clientes/{clienteId}/documentos', [DocumentoController::class, 'enviar']);
$router->post('/clientes/{clienteId}/documentos/{anexoId}/excluir', [DocumentoController::class, 'excluir']);

// Vestidos
$router->get('/vestidos', [VestidoController::class, 'index']);
$router->get('/vestidos/novo', [VestidoController::class, 'novoForm']);
$router->post('/vestidos', [VestidoController::class, 'store']);
$router->get('/vestidos/{id}', [VestidoController::class, 'show']);
$router->get('/vestidos/{id}/editar', [VestidoController::class, 'editarForm']);
$router->post('/vestidos/{id}', [VestidoController::class, 'update']);
$router->post('/vestidos/{id}/status', [VestidoController::class, 'mudarStatus']);
$router->post('/vestidos/{id}/excluir', [VestidoController::class, 'excluir']);

// Medidas (aninhadas em cliente)
$router->get('/clientes/{clienteId}/medidas', [MedidaController::class, 'index']);
$router->post('/clientes/{clienteId}/medidas', [MedidaController::class, 'store']);

// Provas
$router->get('/provas', [ProvaController::class, 'index']);
$router->get('/provas/novo', [ProvaController::class, 'novoForm']);
$router->post('/provas', [ProvaController::class, 'store']);
$router->get('/provas/{id}', [ProvaController::class, 'show']);
$router->post('/provas/{id}/status', [ProvaController::class, 'mudarStatus']);
$router->post('/provas/{id}/ajustes', [ProvaController::class, 'adicionarAjuste']);
$router->post('/provas/{id}/ajustes/{ajusteId}/status', [ProvaController::class, 'mudarStatusAjuste']);
$router->post('/provas/{id}/fotos', [ProvaController::class, 'enviarFoto']);
$router->post('/provas/{id}/fotos/{anexoId}/excluir', [ProvaController::class, 'excluirFoto']);

// Produção
$router->get('/producao', [ProducaoController::class, 'kanban']);
$router->get('/producao/novo', [ProducaoController::class, 'novoForm']);
$router->post('/producao', [ProducaoController::class, 'store']);
$router->get('/producao/{id}', [ProducaoController::class, 'show']);
$router->post('/producao/mover', [ProducaoController::class, 'mover']);
