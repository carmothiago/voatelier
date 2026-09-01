// Vitória Oliver Atelier - JavaScript base do sistema
// Reservado para funcionalidades das próximas etapas
// (Kanban do CRM e da Produção, calendário da Agenda, máscaras de campos, etc.)

document.addEventListener('DOMContentLoaded', function () {
    // Fecha alertas automaticamente após alguns segundos
    var alertas = document.querySelectorAll('.alerta');
    alertas.forEach(function (alerta) {
        setTimeout(function () {
            alerta.style.transition = 'opacity 0.4s ease';
            alerta.style.opacity = '0';
        }, 5000);
    });

    inicializarKanban('kanban-board', { itemKey: 'cliente_id', itemAttr: 'data-cliente-id', perguntarMotivoPerda: true });
    inicializarKanban('producao-board', { itemKey: 'producao_id', itemAttr: 'data-producao-id', perguntarMotivoPerda: false });
});

/**
 * Kanban genérico (usado no CRM e na Produção): permite arrastar os cards
 * entre colunas e persiste a movimentação via fetch, sem recarregar a página.
 *
 * @param {string} boardId ID do elemento .kanban-board no HTML
 * @param {{itemKey: string, itemAttr: string, perguntarMotivoPerda: boolean}} opcoes
 */
function inicializarKanban(boardId, opcoes) {
    var board = document.getElementById(boardId);
    if (!board) {
        return;
    }

    var csrfToken = board.dataset.csrf;
    var moverUrl = board.dataset.moverUrl;
    var cardArrastado = null;

    board.querySelectorAll('.kanban-card[draggable="true"]').forEach(function (card) {
        card.addEventListener('dragstart', function () {
            cardArrastado = card;
            card.classList.add('arrastando');
        });

        card.addEventListener('dragend', function () {
            card.classList.remove('arrastando');
            cardArrastado = null;
        });
    });

    board.querySelectorAll('.kanban-lista').forEach(function (lista) {
        lista.addEventListener('dragover', function (evento) {
            evento.preventDefault();
            lista.classList.add('kanban-lista-hover');
        });

        lista.addEventListener('dragleave', function () {
            lista.classList.remove('kanban-lista-hover');
        });

        lista.addEventListener('drop', function (evento) {
            evento.preventDefault();
            lista.classList.remove('kanban-lista-hover');

            if (!cardArrastado) {
                return;
            }

            var novaEtapa = lista.dataset.etapaAlvo;
            var itemId = cardArrastado.getAttribute(opcoes.itemAttr);
            var colunaOrigem = cardArrastado.closest('.kanban-lista');

            var motivoPerda = '';
            if (opcoes.perguntarMotivoPerda && novaEtapa === 'perdido') {
                motivoPerda = window.prompt('Motivo da perda (opcional):') || '';
            }

            lista.appendChild(cardArrastado);
            atualizarContadores(board);

            var payload = { csrf_token: csrfToken, nova_etapa: novaEtapa };
            payload[opcoes.itemKey] = itemId;
            if (opcoes.perguntarMotivoPerda) {
                payload.motivo_perda = motivoPerda;
            }

            fetch(moverUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(payload).toString()
            })
                .then(function (resposta) { return resposta.json(); })
                .then(function (dados) {
                    if (!dados.ok) {
                        colunaOrigem.appendChild(cardArrastado);
                        atualizarContadores(board);
                        alert(dados.erro || 'Não foi possível mover o card. Tente novamente.');
                    }
                })
                .catch(function () {
                    colunaOrigem.appendChild(cardArrastado);
                    atualizarContadores(board);
                    alert('Falha de conexão ao mover o card.');
                });
        });
    });

    function atualizarContadores(board) {
        board.querySelectorAll('.kanban-coluna').forEach(function (coluna) {
            var total = coluna.querySelectorAll('.kanban-card').length;
            var contador = coluna.querySelector('.kanban-contador');
            if (contador) {
                contador.textContent = total;
            }
        });
    }
}
