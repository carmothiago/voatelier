<?php

namespace App\Core;

/**
 * Paginador simples e reutilizável.
 *
 * Uso nos controllers:
 *
 *   $total     = $model->contarAtivos($busca);
 *   $paginador = new Paginador($total, PAGINA_TAMANHO, (int) $this->input('pagina', 1));
 *   $registros = $model->listarAtivos($busca, $paginador->porPagina, $paginador->offset());
 *
 * Uso nas views:
 *
 *   <?= $paginador->links(url('/clientes') . '?q=' . urlencode($busca)) ?>
 */
class Paginador
{
    public readonly int $paginaAtual;
    public readonly int $porPagina;
    public readonly int $total;
    public readonly int $totalPaginas;

    public function __construct(int $total, int $porPagina, int $paginaAtual)
    {
        $this->total       = max(0, $total);
        $this->porPagina   = max(1, $porPagina);
        $this->totalPaginas = (int) ceil($this->total / $this->porPagina) ?: 1;
        $this->paginaAtual  = max(1, min($paginaAtual, $this->totalPaginas));
    }

    /**
     * Offset SQL (OFFSET :offset).
     */
    public function offset(): int
    {
        return ($this->paginaAtual - 1) * $this->porPagina;
    }

    /**
     * Retorna HTML do bloco de navegação entre páginas.
     *
     * @param string $urlBase  URL base sem o parâmetro &pagina=.
     *                         Ex: url('/clientes') . '?q=' . urlencode($busca)
     *                         O separador (&) é adicionado automaticamente.
     */
    public function links(string $urlBase): string
    {
        if ($this->totalPaginas <= 1) {
            return '';
        }

        // Garante que urlBase não termine com & nem ?
        $sep = str_contains($urlBase, '?') ? '&' : '?';

        $html  = '<nav class="paginacao" aria-label="Navegação de páginas">';
        $html .= '<ul>';

        // ← Anterior
        if ($this->paginaAtual > 1) {
            $href  = $urlBase . $sep . 'pagina=' . ($this->paginaAtual - 1);
            $html .= '<li><a href="' . e($href) . '" aria-label="Página anterior">‹ Anterior</a></li>';
        } else {
            $html .= '<li class="desabilitado"><span>‹ Anterior</span></li>';
        }

        // Janela de páginas: sempre mostra primeira, última e até 3 ao redor da atual
        $paginas = $this->janelaDePaginas();

        $anterior = null;
        foreach ($paginas as $p) {
            if ($anterior !== null && $p - $anterior > 1) {
                $html .= '<li class="reticencias"><span>…</span></li>';
            }

            if ($p === $this->paginaAtual) {
                $html .= '<li class="ativa"><span>' . $p . '</span></li>';
            } else {
                $href  = $urlBase . $sep . 'pagina=' . $p;
                $html .= '<li><a href="' . e($href) . '">' . $p . '</a></li>';
            }

            $anterior = $p;
        }

        // → Próxima
        if ($this->paginaAtual < $this->totalPaginas) {
            $href  = $urlBase . $sep . 'pagina=' . ($this->paginaAtual + 1);
            $html .= '<li><a href="' . e($href) . '" aria-label="Próxima página">Próxima ›</a></li>';
        } else {
            $html .= '<li class="desabilitado"><span>Próxima ›</span></li>';
        }

        $html .= '</ul>';

        // Resumo textual acessível
        $inicio = $this->offset() + 1;
        $fim    = min($this->offset() + $this->porPagina, $this->total);
        $html  .= '<p class="paginacao-resumo">'
               . "Exibindo {$inicio}–{$fim} de {$this->total} registros"
               . '</p>';

        $html .= '</nav>';

        return $html;
    }

    /**
     * Calcula a janela de números de página a exibir.
     * Sempre inclui: página 1, página atual ±2, última página.
     */
    private function janelaDePaginas(): array
    {
        $paginas = [];

        for ($p = 1; $p <= $this->totalPaginas; $p++) {
            if (
                $p === 1 ||
                $p === $this->totalPaginas ||
                abs($p - $this->paginaAtual) <= 2
            ) {
                $paginas[] = $p;
            }
        }

        return array_unique($paginas);
    }
}
