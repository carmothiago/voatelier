// Vitória Oliver Atelier - JavaScript base do sistema

document.addEventListener('DOMContentLoaded', function () {
    // Fecha alertas automaticamente após 5 segundos
    document.querySelectorAll('.alerta').forEach(function (alerta) {
        setTimeout(function () {
            alerta.style.transition = 'opacity 0.4s ease';
            alerta.style.opacity = '0';
        }, 5000);
    });

    inicializarKanban('kanban-board', {
        itemKey:              'cliente_id',
        itemAttr:             'data-cliente-id',
        perguntarMotivoPerda: true,
    });

    inicializarKanban('producao-board', {
        itemKey:              'producao_id',
        itemAttr:             'data-producao-id',
        perguntarMotivoPerda: false,
    });
});

// ---------------------------------------------------------------------------
// Modal de motivo de perda
// Retorna uma Promise que resolve com a string do motivo (pode ser vazia)
// ou rejeita se o usuário cancelar.
// ---------------------------------------------------------------------------
function abrirModalPerda() {
    return new Promise(function (resolve, reject) {
        var modal      = document.getElementById('modal-perda');
        var textarea   = document.getElementById('modal-perda-texto');
        var btnConfirm = document.getElementById('modal-perda-confirmar');
        var btnCancel  = document.getElementById('modal-perda-cancelar');

        if (!modal) {
            // Fallback para páginas sem o modal (não deve ocorrer no CRM)
            resolve('');
            return;
        }

        // Limpa textarea e abre o modal
        textarea.value = '';
        modal.style.display = 'flex';
        textarea.focus();

        function fechar() {
            modal.style.display = 'none';
            btnConfirm.removeEventListener('click', onConfirmar);
            btnCancel.removeEventListener('click', onCancelar);
            modal.removeEventListener('click', onOverlay);
            document.removeEventListener('keydown', onEsc);
        }

        function onConfirmar() {
            var motivo = textarea.value.trim();
            fechar();
            resolve(motivo);
        }

        function onCancelar() {
            fechar();
            reject();
        }

        // Fechar ao clicar fora do painel (no overlay)
        function onOverlay(e) {
            if (e.target === modal) {
                fechar();
                reject();
            }
        }

        // Fechar com Escape
        function onEsc(e) {
            if (e.key === 'Escape') {
                fechar();
                reject();
            }
        }

        btnConfirm.addEventListener('click', onConfirmar);
        btnCancel.addEventListener('click', onCancelar);
        modal.addEventListener('click', onOverlay);
        document.addEventListener('keydown', onEsc);
    });
}

// ---------------------------------------------------------------------------
// Kanban genérico — CRM e Produção
// ---------------------------------------------------------------------------
function inicializarKanban(boardId, opcoes) {
    var board = document.getElementById(boardId);
    if (!board) return;

    var csrfToken    = board.dataset.csrf;
    var moverUrl     = board.dataset.moverUrl;
    var cardArrastado = null;
    var listaOrigem   = null;

    // Torna os cards arrastáveis
    board.querySelectorAll('.kanban-card[draggable="true"]').forEach(function (card) {
        card.addEventListener('dragstart', function (e) {
            cardArrastado = card;
            listaOrigem   = card.closest('.kanban-lista');
            card.classList.add('arrastando');
            e.dataTransfer.effectAllowed = 'move';
        });

        card.addEventListener('dragend', function () {
            card.classList.remove('arrastando');
            // Não limpa cardArrastado aqui — o drop ainda pode estar em andamento
        });
    });

    // Configura as colunas de destino
    board.querySelectorAll('.kanban-lista').forEach(function (lista) {
        lista.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            lista.classList.add('kanban-lista-hover');
        });

        lista.addEventListener('dragleave', function () {
            lista.classList.remove('kanban-lista-hover');
        });

        lista.addEventListener('drop', function (e) {
            e.preventDefault();
            lista.classList.remove('kanban-lista-hover');

            if (!cardArrastado) return;

            var novaEtapa   = lista.dataset.etapaAlvo;
            var etapaAtual  = listaOrigem ? listaOrigem.dataset.etapaAlvo : null;
            var itemId      = cardArrastado.getAttribute(opcoes.itemAttr);

            // Sem mudança de coluna — ignora silenciosamente
            if (novaEtapa === etapaAtual) {
                cardArrastado = null;
                return;
            }

            var cardMovido = cardArrastado;
            cardArrastado  = null;

            if (opcoes.perguntarMotivoPerda && novaEtapa === 'perdido') {
                // Move visualmente já (feedback imediato) e aguarda o modal
                lista.appendChild(cardMovido);
                atualizarContadores(board);

                abrirModalPerda()
                    .then(function (motivo) {
                        // Atualiza o card visualmente com o motivo em vermelho
                        atualizarMotivoNoCard(cardMovido, motivo);
                        // Persiste no servidor
                        enviarMovimentacao(moverUrl, csrfToken, opcoes.itemKey, itemId, novaEtapa, motivo, cardMovido, listaOrigem, board);
                    })
                    .catch(function () {
                        // Usuário cancelou — desfaz o movimento visual
                        if (listaOrigem) listaOrigem.appendChild(cardMovido);
                        atualizarContadores(board);
                    });
            } else {
                lista.appendChild(cardMovido);
                atualizarContadores(board);
                enviarMovimentacao(moverUrl, csrfToken, opcoes.itemKey, itemId, novaEtapa, '', cardMovido, listaOrigem, board);
            }
        });
    });
}

// ---------------------------------------------------------------------------
// Insere ou atualiza o elemento de motivo de perda dentro do card
// ---------------------------------------------------------------------------
function atualizarMotivoNoCard(card, motivo) {
    // Remove elemento anterior se já existir
    var anterior = card.querySelector('.kanban-motivo-perda');
    if (anterior) anterior.remove();

    if (!motivo) return;

    var el = document.createElement('div');
    el.className = 'kanban-card-info kanban-motivo-perda';
    el.style.color      = 'var(--cor-erro)';
    el.style.fontStyle  = 'italic';
    el.style.marginTop  = '4px';
    el.textContent      = motivo;
    card.appendChild(el);
}

// ---------------------------------------------------------------------------
// Envia a movimentação para o servidor via fetch
// ---------------------------------------------------------------------------
function enviarMovimentacao(url, csrf, itemKey, itemId, novaEtapa, motivo, card, listaOrigem, board) {
    var payload = {
        csrf_token: csrf,
        nova_etapa: novaEtapa,
        motivo_perda: motivo,
    };
    payload[itemKey] = itemId;

    fetch(url, {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    new URLSearchParams(payload).toString(),
    })
        .then(function (r) { return r.json(); })
        .then(function (dados) {
            if (!dados.ok) {
                // Reverte visualmente
                if (listaOrigem) listaOrigem.appendChild(card);
                atualizarContadores(board);
                // Remove motivo exibido prematuramente
                var m = card.querySelector('.kanban-motivo-perda');
                if (m) m.remove();
                alert(dados.erro || 'Não foi possível mover o card. Tente novamente.');
            }
        })
        .catch(function () {
            if (listaOrigem) listaOrigem.appendChild(card);
            atualizarContadores(board);
            var m = card.querySelector('.kanban-motivo-perda');
            if (m) m.remove();
            alert('Falha de conexão ao mover o card.');
        });
}

// ---------------------------------------------------------------------------
// Atualiza os contadores numéricos de cada coluna
// ---------------------------------------------------------------------------
function atualizarContadores(board) {
    board.querySelectorAll('.kanban-coluna').forEach(function (coluna) {
        var total    = coluna.querySelectorAll('.kanban-card').length;
        var contador = coluna.querySelector('.kanban-contador');
        if (contador) contador.textContent = total;
    });
}
