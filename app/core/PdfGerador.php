<?php

namespace App\Core;

/**
 * Gerador de PDF mínimo, em PHP puro, sem dependências externas
 * (sem Composer, sem bibliotecas de terceiros — alinhado ao princípio do
 * projeto de funcionar apenas com PHP + MySQL + HTML/CSS/JS).
 *
 * Suporta texto simples com quebra de página automática, usando as
 * fontes padrão do PDF (Helvetica / Helvetica-Bold), que não precisam
 * ser incorporadas ao arquivo.
 *
 * Uso:
 *   $pdf = new PdfGerador();
 *   $pdf->titulo('Contrato de Prestação de Serviços');
 *   $pdf->paragrafo('Texto do contrato...');
 *   file_put_contents($caminho, $pdf->gerar());
 */
class PdfGerador
{
    private const LARGURA_PAGINA = 595;  // A4 em pontos
    private const ALTURA_PAGINA = 842;
    private const MARGEM = 56;
    private const LARGURA_UTIL = self::LARGURA_PAGINA - (2 * self::MARGEM);

    private array $paginas = [];
    private string $bufferAtual = '';
    private float $y;

    public function __construct()
    {
        $this->novaPagina();
    }

    private function novaPagina(): void
    {
        if ($this->bufferAtual !== '') {
            $this->paginas[] = $this->bufferAtual;
        }
        $this->bufferAtual = '';
        $this->y = self::ALTURA_PAGINA - self::MARGEM;
    }

    private function garantirEspaco(float $altura): void
    {
        if ($this->y - $altura < self::MARGEM) {
            $this->novaPagina();
        }
    }

    public function titulo(string $texto): self
    {
        $this->garantirEspaco(28);
        $this->escreverLinha($texto, 16, true);
        $this->y -= 10;
        return $this;
    }

    public function subtitulo(string $texto): self
    {
        $this->garantirEspaco(20);
        $this->escreverLinha($texto, 12, true);
        $this->y -= 4;
        return $this;
    }

    public function paragrafo(string $texto, int $tamanho = 10): self
    {
        $linhas = $this->quebrarLinhas($texto, $tamanho);
        foreach ($linhas as $linha) {
            $this->garantirEspaco($tamanho + 4);
            $this->escreverLinha($linha, $tamanho, false);
        }
        $this->y -= 8;
        return $this;
    }

    public function campo(string $label, string $valor): self
    {
        $this->garantirEspaco(16);
        $texto = $label . ': ' . $valor;
        $this->escreverLinha($texto, 10, false);
        return $this;
    }

    public function espaco(int $altura = 12): self
    {
        $this->y -= $altura;
        return $this;
    }

    public function linhaDivisoria(): self
    {
        $this->garantirEspaco(14);
        $this->bufferAtual .= sprintf(
            "%.2F w\n%.2F %.2F m %.2F %.2F l S\n",
            0.5,
            self::MARGEM,
            $this->y,
            self::LARGURA_PAGINA - self::MARGEM,
            $this->y
        );
        $this->y -= 14;
        return $this;
    }

    private function escreverLinha(string $texto, int $tamanho, bool $negrito): void
    {
        $fonte = $negrito ? 'F2' : 'F1';
        $this->bufferAtual .= sprintf(
            "BT /%s %d Tf 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n",
            $fonte,
            $tamanho,
            self::MARGEM,
            $this->y,
            $this->escaparTexto($texto)
        );
        $this->y -= ($tamanho + 5);
    }

    private function escaparTexto(string $texto): string
    {
        // Converte de UTF-8 para Windows-1252 (equivalente ao WinAnsiEncoding
        // do PDF), que já suporta nativamente acentuação e cedilha do
        // português — não é necessário remover acentos.
        $convertido = @iconv('UTF-8', 'Windows-1252//IGNORE', $texto);
        if ($convertido !== false) {
            $texto = $convertido;
        }

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
    }

    /**
     * Quebra um texto em linhas que caibam na largura útil da página,
     * usando uma largura média de caractere aproximada (fontes padrão
     * não têm métricas exatas disponíveis sem tabela de largura).
     */
    private function quebrarLinhas(string $texto, int $tamanho): array
    {
        $larguraMediaChar = $tamanho * 0.5;
        $caracteresPorLinha = max(20, (int) floor(self::LARGURA_UTIL / $larguraMediaChar));

        $linhasFinais = [];
        foreach (explode("\n", $texto) as $paragrafo) {
            $palavras = explode(' ', $paragrafo);
            $linhaAtual = '';

            foreach ($palavras as $palavra) {
                $tentativa = $linhaAtual === '' ? $palavra : $linhaAtual . ' ' . $palavra;
                $comprimento = function_exists('mb_strlen') ? mb_strlen($tentativa) : strlen($tentativa);
                if ($comprimento > $caracteresPorLinha && $linhaAtual !== '') {
                    $linhasFinais[] = $linhaAtual;
                    $linhaAtual = $palavra;
                } else {
                    $linhaAtual = $tentativa;
                }
            }

            if ($linhaAtual !== '') {
                $linhasFinais[] = $linhaAtual;
            }
        }

        return $linhasFinais;
    }

    /**
     * Monta o arquivo PDF completo (cabeçalho, objetos, xref e trailer)
     * e retorna o conteúdo binário pronto para salvar em disco.
     */
    public function gerar(): string
    {
        // Garante que a última página em construção seja incluída
        $this->paginas[] = $this->bufferAtual;

        $totalPaginas = count($this->paginas);

        // Numeração dos objetos:
        // 1 = Catalog, 2 = Pages, 3 = Fonte regular, 4 = Fonte negrito
        // 5, 7, 9... = objetos de página / 6, 8, 10... = streams de conteúdo
        $idCatalog = 1;
        $idPages = 2;
        $idFonteRegular = 3;
        $idFonteNegrito = 4;

        $proximoId = 5;
        $idsPaginas = [];
        $idsConteudo = [];

        for ($i = 0; $i < $totalPaginas; $i++) {
            $idsPaginas[$i] = $proximoId++;
            $idsConteudo[$i] = $proximoId++;
        }

        $objetos = [];

        $objetos[$idCatalog] = "<< /Type /Catalog /Pages {$idPages} 0 R >>";

        $kids = implode(' ', array_map(fn($id) => "{$id} 0 R", $idsPaginas));
        $objetos[$idPages] = "<< /Type /Pages /Kids [{$kids}] /Count {$totalPaginas} >>";

        $objetos[$idFonteRegular] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>";
        $objetos[$idFonteNegrito] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>";

        for ($i = 0; $i < $totalPaginas; $i++) {
            $objetos[$idsPaginas[$i]] =
                "<< /Type /Page /Parent {$idPages} 0 R "
                . "/MediaBox [0 0 " . self::LARGURA_PAGINA . ' ' . self::ALTURA_PAGINA . '] '
                . "/Resources << /Font << /F1 {$idFonteRegular} 0 R /F2 {$idFonteNegrito} 0 R >> >> "
                . "/Contents {$idsConteudo[$i]} 0 R >>";

            $conteudo = $this->paginas[$i];
            $objetos[$idsConteudo[$i]] = "<< /Length " . strlen($conteudo) . " >>\nstream\n{$conteudo}endstream";
        }

        ksort($objetos);

        $saida = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [];

        foreach ($objetos as $numero => $corpo) {
            $offsets[$numero] = strlen($saida);
            $saida .= "{$numero} 0 obj\n{$corpo}\nendobj\n";
        }

        $totalObjetos = count($objetos) + 1;
        $offsetXref = strlen($saida);

        $saida .= "xref\n0 {$totalObjetos}\n";
        $saida .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $saida .= sprintf("%010d 00000 n \n", $offset);
        }

        $saida .= "trailer\n<< /Size {$totalObjetos} /Root {$idCatalog} 0 R >>\n";
        $saida .= "startxref\n{$offsetXref}\n%%EOF";

        return $saida;
    }
}
