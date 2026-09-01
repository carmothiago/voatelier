<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;

class BackupController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->somenteAdministrador();

        $db = Database::getConnection();
        $tabelas = $db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        $pastaUploads = UPLOADS_PATH;
        $tamanhoUploads = $this->tamanhoPasta($pastaUploads);

        $this->view('configuracoes/backup', [
            'titulo'          => 'Configurações · Backup',
            'totalTabelas'    => count($tabelas),
            'tamanhoUploads'  => $tamanhoUploads,
        ]);
    }

    /**
     * Gera um dump SQL completo (estrutura + dados) usando apenas PDO,
     * sem depender de mysqldump/exec() — muitos servidores compartilhados
     * desabilitam exec() por segurança, então isso mantém o backup
     * funcionando em qualquer instalação PHP + MySQL padrão.
     */
    public function exportarBanco(): void
    {
        $this->requireLogin();
        $this->somenteAdministrador();

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/configuracoes/backup');
            return;
        }

        $db = Database::getConnection();
        $tabelas = $db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);

        $nomeArquivo = 'voatelier_backup_' . date('Y-m-d_His') . '.sql';

        header('Content-Type: application/sql; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

        echo "-- Backup do banco de dados voatelier\n";
        echo "-- Gerado em " . date('d/m/Y H:i:s') . " por " . (Auth::user()['nome'] ?? 'sistema') . "\n";
        echo "-- Restaurar com: mysql -u root voatelier < " . $nomeArquivo . "\n\n";
        echo "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tabelas as $tabela) {
            $criacao = $db->query("SHOW CREATE TABLE `{$tabela}`")->fetch();
            echo "DROP TABLE IF EXISTS `{$tabela}`;\n";
            echo $criacao['Create Table'] . ";\n\n";

            $stmt = $db->query("SELECT * FROM `{$tabela}`");
            $linhas = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($linhas)) {
                continue;
            }

            $colunas = array_keys($linhas[0]);
            $colunasSql = '`' . implode('`, `', $colunas) . '`';

            echo "INSERT INTO `{$tabela}` ({$colunasSql}) VALUES\n";

            $totalLinhas = count($linhas);
            foreach ($linhas as $indice => $linha) {
                $valores = array_map(function ($valor) use ($db) {
                    return $valor === null ? 'NULL' : $db->quote((string) $valor);
                }, array_values($linha));

                echo '(' . implode(', ', $valores) . ')';
                echo ($indice < $totalLinhas - 1) ? ",\n" : ";\n\n";
            }
        }

        echo "SET FOREIGN_KEY_CHECKS = 1;\n";

        registrarAuditoria('configuracoes', 'exportar_backup_banco', null, null, ['arquivo' => $nomeArquivo]);
    }

    /**
     * Gera um .zip da pasta uploads/ inteira para download, usando a
     * extensão ZipArchive (nativa do PHP, já incluída no XAMPP).
     */
    public function exportarUploads(): void
    {
        $this->requireLogin();
        $this->somenteAdministrador();

        if (!Csrf::validate($this->input('csrf_token'))) {
            setFlash('erro', 'Sessão expirada. Tente novamente.');
            $this->redirect('/configuracoes/backup');
            return;
        }

        if (!class_exists('ZipArchive')) {
            setFlash('erro', 'A extensão ZipArchive não está disponível neste servidor PHP.');
            $this->redirect('/configuracoes/backup');
            return;
        }

        $nomeArquivo = 'voatelier_uploads_' . date('Y-m-d_His') . '.zip';
        $caminhoTemp = sys_get_temp_dir() . '/' . $nomeArquivo;

        $zip = new \ZipArchive();
        $zip->open($caminhoTemp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(UPLOADS_PATH, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $arquivo) {
            if ($arquivo->isFile()) {
                $caminhoLocal = substr($arquivo->getPathname(), strlen(UPLOADS_PATH) + 1);
                $zip->addFile($arquivo->getPathname(), $caminhoLocal);
            }
        }

        $zip->close();

        registrarAuditoria('configuracoes', 'exportar_backup_uploads', null, null, ['arquivo' => $nomeArquivo]);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
        header('Content-Length: ' . filesize($caminhoTemp));
        readfile($caminhoTemp);
        unlink($caminhoTemp);
    }

    private function somenteAdministrador(): void
    {
        $this->requirePermission('configuracoes.visualizar');
    }

    private function tamanhoPasta(string $pasta): int
    {
        $total = 0;
        if (!is_dir($pasta)) {
            return 0;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pasta, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $arquivo) {
            if ($arquivo->isFile()) {
                $total += $arquivo->getSize();
            }
        }

        return $total;
    }
}
