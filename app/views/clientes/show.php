<?php

use App\Core\Csrf;
use App\Models\Agendamento;

?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;">
    <div>
        <div style="font-size:12px;color:var(--cor-dourado);text-transform:uppercase;letter-spacing:0.1em;">
            <?= e($etapasCrm[$cliente['etapa_crm']] ?? $cliente['etapa_crm']) ?>
        </div>
    </div>
    <div style="display:flex;gap:10px;">
        <?php if (podeAcessar('clientes.editar')): ?>
            <a href="<?= url('/clientes/' . $cliente['id'] . '/editar') ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;">Editar</a>
        <?php endif; ?>
        <?php if (podeAcessar('medidas.visualizar')): ?>
            <a href="<?= url('/clientes/' . $cliente['id'] . '/medidas') ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;">Medidas</a>
        <?php endif; ?>
        <?php if (podeAcessar('provas.criar')): ?>
            <a href="<?= url('/provas/novo?cliente_id=' . $cliente['id']) ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;">+ Prova</a>
        <?php endif; ?>
        <?php if (podeAcessar('agenda.criar')): ?>
            <a href="<?= url('/agenda/novo?cliente_id=' . $cliente['id']) ?>" class="botao botao-secundario" style="width:auto;padding:9px 18px;text-decoration:none;">+ Agendar</a>
        <?php endif; ?>
        <?php if (podeAcessar('clientes.excluir')): ?>
            <form method="post" action="<?= url('/clientes/' . $cliente['id'] . '/excluir') ?>"
                  onsubmit="return confirm('Tem certeza que deseja remover esta cliente da listagem?');">
                <?= Csrf::field() ?>
                <button type="submit" class="botao botao-secundario" style="width:auto;padding:9px 18px;color:var(--cor-erro);border-color:#f0cfcb;">
                    Excluir
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
    <div>
        <div class="painel">
            <h3 style="margin-top:0;">Informações pessoais</h3>
            <table class="tabela-elegante">
                <tr><th style="width:180px;">CPF</th><td><?= e($cliente['cpf'] ?: '-') ?></td></tr>
                <tr><th>Nascimento</th><td><?= formatarData($cliente['data_nascimento']) ?></td></tr>
                <tr><th>Telefone</th><td><?= e($cliente['telefone'] ?: '-') ?></td></tr>
                <tr><th>WhatsApp</th><td><?= e($cliente['whatsapp'] ?: '-') ?></td></tr>
                <tr><th>E-mail</th><td><?= e($cliente['email'] ?: '-') ?></td></tr>
                <tr><th>Instagram</th><td><?= e($cliente['instagram'] ?: '-') ?></td></tr>
                <tr><th>Endereço</th><td><?= e($cliente['endereco'] ?: '-') ?><?= $cliente['cidade'] ? ' — ' . e($cliente['cidade']) . '/' . e($cliente['estado']) : '' ?></td></tr>
                <tr><th>Observações</th><td><?= nl2br(e($cliente['observacoes'] ?: '-')) ?></td></tr>
            </table>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Casamento</h3>
            <table class="tabela-elegante">
                <tr><th style="width:180px;">Data</th><td><?= formatarData($cliente['data_casamento']) ?><?= $cliente['horario_casamento'] ? ' às ' . substr($cliente['horario_casamento'], 0, 5) : '' ?></td></tr>
                <tr><th>Local</th><td><?= e($cliente['local_casamento'] ?: '-') ?></td></tr>
                <tr><th>Noivo(a)</th><td><?= e($cliente['nome_noivo'] ?: '-') ?></td></tr>
                <tr><th>Tipo</th><td><?= e($cliente['tipo_casamento'] ?: '-') ?></td></tr>
                <tr><th>Observações</th><td><?= nl2br(e($cliente['observacoes_casamento'] ?: '-')) ?></td></tr>
            </table>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Agenda</h3>
            <?php if (empty($agendamentos)): ?>
                <p style="color:var(--cor-texto-suave);">Nenhum agendamento registrado.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Data</th><th>Horário</th><th>Tipo</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($agendamentos as $ag): ?>
                            <tr>
                                <td><?= formatarData($ag['data_agendamento']) ?></td>
                                <td><?= substr($ag['hora_inicio'], 0, 5) ?> - <?= substr($ag['hora_fim'], 0, 5) ?></td>
                                <td><?= e(Agendamento::TIPOS[$ag['tipo']] ?? $ag['tipo']) ?></td>
                                <td><?= e(Agendamento::STATUS[$ag['status']] ?? $ag['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Medidas</h3>
            <?php if (empty($ultimaMedida)): ?>
                <p style="color:var(--cor-texto-suave);">Nenhuma ficha de medidas registrada.</p>
            <?php else: ?>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
                    <?php foreach ($labelsMedidas as $campo => $label): ?>
                        <?php if ($ultimaMedida[$campo] !== null): ?>
                            <span style="font-size:12px;background:#f4f1ea;padding:4px 10px;border-radius:6px;">
                                <?= e($label) ?>: <strong><?= e(rtrim(rtrim($ultimaMedida[$campo], '0'), '.')) ?></strong> cm
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <p style="font-size:12px;color:var(--cor-texto-suave);margin:0;">Registrada em <?= formatarData($ultimaMedida['created_at'], true) ?></p>
            <?php endif; ?>
            <p style="margin-top:14px;"><a href="<?= url('/clientes/' . $cliente['id'] . '/medidas') ?>">Ver histórico completo →</a></p>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Provas</h3>
            <?php if (empty($provas)): ?>
                <p style="color:var(--cor-texto-suave);">Nenhuma prova registrada.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Nº</th><th>Data</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($provas as $prova): ?>
                            <tr>
                                <td><a href="<?= url('/provas/' . $prova['id']) ?>">#<?= (int) $prova['numero'] ?></a></td>
                                <td><?= formatarData($prova['data_prova']) ?></td>
                                <td><?= e(\App\Models\Prova::STATUS[$prova['status']] ?? $prova['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Vestidos vinculados</h3>
            <?php if (empty($vestidosVinculados)): ?>
                <p style="color:var(--cor-texto-suave);">Nenhum vestido reservado para esta cliente.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Código</th><th>Nome</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($vestidosVinculados as $vest): ?>
                            <tr>
                                <td><a href="<?= url('/vestidos/' . $vest['id']) ?>"><?= e($vest['codigo']) ?></a></td>
                                <td><?= e($vest['nome']) ?></td>
                                <td><?= e(\App\Models\Vestido::STATUS[$vest['status']] ?? $vest['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <p style="color:var(--cor-texto-suave);margin-top:14px;">
                <?php if (podeAcessar('contratos.criar')): ?>
                    <a href="<?= url('/contratos/novo?cliente_id=' . $cliente['id']) ?>">+ Novo contrato</a>
                <?php endif; ?>
            </p>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Contratos</h3>
            <?php if (empty($contratos)): ?>
                <p style="color:var(--cor-texto-suave);">Nenhum contrato registrado.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Data</th><th>Valor</th><th>PDF</th></tr></thead>
                    <tbody>
                        <?php foreach ($contratos as $ct): ?>
                            <tr>
                                <td><a href="<?= url('/contratos/' . $ct['id']) ?>"><?= formatarData($ct['data_contrato']) ?></a></td>
                                <td><?= formatarMoeda((float) $ct['valor']) ?></td>
                                <td><?= $ct['arquivo_pdf'] ? '✅' : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Financeiro</h3>
            <?php if (empty($contasReceber)): ?>
                <p style="color:var(--cor-texto-suave);">Nenhuma conta registrada para esta cliente.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Descrição</th><th>Valor</th><th>Vencimento</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($contasReceber as $cr): ?>
                            <tr>
                                <td><?= e($cr['descricao']) ?></td>
                                <td><?= formatarMoeda((float) $cr['valor']) ?></td>
                                <td><?= formatarData($cr['vencimento']) ?></td>
                                <td>
                                    <?php if ($cr['vencido']): ?>
                                        <span style="color:var(--cor-erro);">Vencido</span>
                                    <?php else: ?>
                                        <?= e(\App\Models\ContaReceber::STATUS[$cr['status']] ?? $cr['status']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
            <?php if (podeAcessar('financeiro.editar')): ?>
                <p style="margin-top:14px;"><a href="<?= url('/financeiro/receber/novo?cliente_id=' . $cliente['id']) ?>">+ Nova conta a receber</a></p>
            <?php endif; ?>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Documentos e fotos</h3>

            <?php if (podeAcessar('documentos.criar')): ?>
                <form method="post" action="<?= url('/clientes/' . $cliente['id'] . '/documentos') ?>" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;margin-bottom:16px;flex-wrap:wrap;">
                    <?= Csrf::field() ?>
                    <input type="file" name="arquivo" accept=".jpg,.jpeg,.png,.pdf" required>
                    <input type="text" name="descricao" placeholder="Descrição (opcional)" style="flex:1;min-width:160px;padding:9px 12px;border:1px solid var(--cor-borda);border-radius:6px;">
                    <button type="submit" class="botao botao-secundario" style="width:auto;padding:9px 16px;">Enviar</button>
                </form>
            <?php endif; ?>

            <?php if (empty($documentos)): ?>
                <p style="color:var(--cor-texto-suave);font-size:14px;">Nenhum documento anexado.</p>
            <?php else: ?>
                <table class="tabela-elegante">
                    <thead><tr><th>Arquivo</th><th>Descrição</th><th>Enviado em</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($documentos as $doc): ?>
                            <tr>
                                <td><a href="<?= url('/uploads/documentos/' . $doc['nome_arquivo']) ?>" target="_blank"><?= e($doc['nome_original']) ?></a></td>
                                <td><?= e($doc['descricao'] ?: '-') ?></td>
                                <td><?= formatarData($doc['created_at'], true) ?></td>
                                <td>
                                    <?php if (podeAcessar('documentos.criar')): ?>
                                        <form method="post" action="<?= url('/clientes/' . $cliente['id'] . '/documentos/' . $doc['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir este documento?');">
                                            <?= Csrf::field() ?>
                                            <button type="submit" style="background:none;border:none;color:var(--cor-erro);font-size:12px;cursor:pointer;padding:0;">Excluir</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="painel">
            <h3 style="margin-top:0;">Histórico do CRM</h3>
            <?php if (empty($historicoCrm)): ?>
                <p style="color:var(--cor-texto-suave);font-size:14px;">Sem movimentações.</p>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($historicoCrm as $h): ?>
                        <li style="padding:10px 0;border-bottom:1px solid #f0ece4;font-size:13px;">
                            <strong><?= e($etapasCrm[$h['etapa_nova']] ?? $h['etapa_nova']) ?></strong><br>
                            <span style="color:var(--cor-texto-suave);">
                                <?= formatarData($h['created_at'], true) ?>
                                <?= $h['usuario_nome'] ? ' · ' . e($h['usuario_nome']) : '' ?>
                            </span>
                            <?php if (!empty($h['observacao'])): ?>
                                <br><span style="color:var(--cor-texto-suave);"><?= e($h['observacao']) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="painel">
            <h3 style="margin-top:0;">Histórico de alterações</h3>
            <?php if (empty($historicoCampos)): ?>
                <p style="color:var(--cor-texto-suave);font-size:14px;">Nenhuma alteração registrada.</p>
            <?php else: ?>
                <ul style="list-style:none;padding:0;margin:0;max-height:320px;overflow:auto;">
                    <?php foreach ($historicoCampos as $h): ?>
                        <li style="padding:8px 0;border-bottom:1px solid #f0ece4;font-size:12px;">
                            <strong><?= e($h['campo']) ?></strong> alterado<?= $h['usuario_nome'] ? ' por ' . e($h['usuario_nome']) : '' ?><br>
                            <span style="color:var(--cor-texto-suave);"><?= formatarData($h['created_at'], true) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
