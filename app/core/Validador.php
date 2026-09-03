<?php

namespace App\Core;

/**
 * Validador centralizado para dados de formulário.
 *
 * Uso padrão nos controllers:
 *
 *   $v = new Validador($dados);
 *   $v->obrigatorio('nome_completo', 'Nome completo');
 *   $v->cpf('cpf');
 *   $v->email('email');
 *   $v->data('data_nascimento', 'Data de nascimento');
 *
 *   if ($v->falhou()) {
 *       setFlash('erro', $v->primeiroErro());
 *       $this->redirect('/clientes/novo');
 *       return;
 *   }
 *
 * Todas as regras são tolerantes a campos nulos/vazios quando o campo
 * não é obrigatório — a regra só dispara se o campo tiver algum conteúdo.
 */
class Validador
{
    /** @var array<string, string> Mapa campo → primeira mensagem de erro */
    private array $erros = [];

    /** @var array<string, mixed> Dados a validar */
    private array $dados;

    public function __construct(array $dados)
    {
        $this->dados = $dados;
    }

    // ------------------------------------------------------------------
    // Regras
    // ------------------------------------------------------------------

    /**
     * Campo obrigatório: não pode ser null, '' ou '0' equivalente vazio.
     */
    public function obrigatorio(string $campo, string $rotulo): static
    {
        if (empty($this->dados[$campo]) && $this->dados[$campo] !== '0') {
            $this->adicionar($campo, "{$rotulo} é obrigatório.");
        }
        return $this;
    }

    /**
     * Valida CPF brasileiro (dígitos verificadores).
     * Aceita formatos com ou sem máscara: 000.000.000-00 ou 00000000000.
     * Campo vazio/null é ignorado (use obrigatorio() em conjunto se necessário).
     */
    public function cpf(string $campo, string $rotulo = 'CPF'): static
    {
        $valor = $this->dados[$campo] ?? null;
        if (empty($valor)) {
            return $this;
        }

        $cpf = preg_replace('/\D/', '', $valor);

        if (strlen($cpf) !== 11) {
            $this->adicionar($campo, "{$rotulo} inválido.");
            return $this;
        }

        // Rejeita sequências como 111.111.111-11
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            $this->adicionar($campo, "{$rotulo} inválido.");
            return $this;
        }

        // Cálculo dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * ($t + 1 - $i);
            }
            $digito = (10 * $soma) % 11 % 10;
            if ((int) $cpf[$t] !== $digito) {
                $this->adicionar($campo, "{$rotulo} inválido.");
                return $this;
            }
        }

        return $this;
    }

    /**
     * Valida CNPJ brasileiro (dígitos verificadores).
     * Aceita com ou sem máscara: 00.000.000/0000-00 ou 00000000000000.
     * Campo vazio/null é ignorado.
     */
    public function cnpj(string $campo, string $rotulo = 'CNPJ'): static
    {
        $valor = $this->dados[$campo] ?? null;
        if (empty($valor)) {
            return $this;
        }

        $cnpj = preg_replace('/\D/', '', $valor);

        if (strlen($cnpj) !== 14) {
            $this->adicionar($campo, "{$rotulo} inválido.");
            return $this;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            $this->adicionar($campo, "{$rotulo} inválido.");
            return $this;
        }

        $calcularDigito = function (string $cnpj, int $tamanho): int {
            $pesos = $tamanho === 12
                ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
                : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
            $soma = 0;
            for ($i = 0; $i < $tamanho; $i++) {
                $soma += (int) $cnpj[$i] * $pesos[$i];
            }
            $resto = $soma % 11;
            return $resto < 2 ? 0 : 11 - $resto;
        };

        if ((int) $cnpj[12] !== $calcularDigito($cnpj, 12) ||
            (int) $cnpj[13] !== $calcularDigito($cnpj, 13)) {
            $this->adicionar($campo, "{$rotulo} inválido.");
        }

        return $this;
    }

    /**
     * Valida CPF ou CNPJ automaticamente pelo comprimento.
     * Útil para campos como "cnpj_cpf" em fornecedores.
     */
    public function cpfOuCnpj(string $campo, string $rotulo = 'CPF/CNPJ'): static
    {
        $valor = $this->dados[$campo] ?? null;
        if (empty($valor)) {
            return $this;
        }

        $digitos = preg_replace('/\D/', '', $valor);

        if (strlen($digitos) === 11) {
            return $this->cpf($campo, $rotulo);
        }

        if (strlen($digitos) === 14) {
            return $this->cnpj($campo, $rotulo);
        }

        $this->adicionar($campo, "{$rotulo} inválido. Informe um CPF (11 dígitos) ou CNPJ (14 dígitos).");
        return $this;
    }

    /**
     * Valida formato de e-mail.
     * Campo vazio/null é ignorado.
     */
    public function email(string $campo, string $rotulo = 'E-mail'): static
    {
        $valor = $this->dados[$campo] ?? null;
        if (empty($valor)) {
            return $this;
        }

        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $this->adicionar($campo, "{$rotulo} inválido.");
        }

        return $this;
    }

    /**
     * Valida data no formato Y-m-d (padrão dos inputs date do HTML).
     * Campo vazio/null é ignorado.
     */
    public function data(string $campo, string $rotulo = 'Data'): static
    {
        $valor = $this->dados[$campo] ?? null;
        if (empty($valor)) {
            return $this;
        }

        $d = \DateTime::createFromFormat('Y-m-d', $valor);
        if (!$d || $d->format('Y-m-d') !== $valor) {
            $this->adicionar($campo, "{$rotulo} inválida. Use o formato dd/mm/aaaa.");
        }

        return $this;
    }

    /**
     * Valida que a data do campo $campoFim é igual ou posterior à do $campoInicio.
     * Ambos os campos devem estar no formato Y-m-d.
     * Ignora se qualquer um dos campos estiver vazio.
     */
    public function dataFimAposInicio(string $campoInicio, string $campoFim, string $rotuloFim): static
    {
        $inicio = $this->dados[$campoInicio] ?? null;
        $fim    = $this->dados[$campoFim]    ?? null;

        if (empty($inicio) || empty($fim)) {
            return $this;
        }

        if ($fim < $inicio) {
            $this->adicionar($campoFim, "{$rotuloFim} não pode ser anterior à data de início.");
        }

        return $this;
    }

    /**
     * Valida valor monetário positivo (aceita vírgula ou ponto como decimal).
     * Campo vazio/null é ignorado.
     */
    public function valorMonetario(string $campo, string $rotulo = 'Valor'): static
    {
        $valor = $this->dados[$campo] ?? null;
        if ($valor === null || $valor === '') {
            return $this;
        }

        $normalizado = str_replace(',', '.', preg_replace('/[^\d,\.]/', '', (string) $valor));

        if (!is_numeric($normalizado)) {
            $this->adicionar($campo, "{$rotulo} inválido.");
            return $this;
        }

        if ((float) $normalizado < 0) {
            $this->adicionar($campo, "{$rotulo} não pode ser negativo.");
        }

        return $this;
    }

    /**
     * Valida comprimento máximo de uma string.
     */
    public function tamanhoMaximo(string $campo, int $max, string $rotulo): static
    {
        $valor = $this->dados[$campo] ?? null;
        if (empty($valor)) {
            return $this;
        }

        if (mb_strlen((string) $valor) > $max) {
            $this->adicionar($campo, "{$rotulo} deve ter no máximo {$max} caracteres.");
        }

        return $this;
    }

    /**
     * Valida que um valor pertence a um conjunto permitido (enum/whitelist).
     * Campo vazio/null é ignorado.
     */
    public function emLista(string $campo, array $lista, string $rotulo): static
    {
        $valor = $this->dados[$campo] ?? null;
        if (empty($valor)) {
            return $this;
        }

        if (!in_array($valor, $lista, true)) {
            $this->adicionar($campo, "{$rotulo} inválido.");
        }

        return $this;
    }

    // ------------------------------------------------------------------
    // Resultado
    // ------------------------------------------------------------------

    public function falhou(): bool
    {
        return !empty($this->erros);
    }

    public function passou(): bool
    {
        return empty($this->erros);
    }

    /** Retorna a primeira mensagem de erro (para setFlash). */
    public function primeiroErro(): string
    {
        return array_values($this->erros)[0] ?? '';
    }

    /** Retorna todos os erros indexados por campo. */
    public function erros(): array
    {
        return $this->erros;
    }

    // ------------------------------------------------------------------
    // Interno
    // ------------------------------------------------------------------

    private function adicionar(string $campo, string $mensagem): void
    {
        // Registra apenas o primeiro erro por campo
        if (!isset($this->erros[$campo])) {
            $this->erros[$campo] = $mensagem;
        }
    }
}
