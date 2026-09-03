<div align="center">

# 👗 Vitória Oliver Atelier — Sistema de Gestão

**Sistema interno de gestão para atelier de vestidos de noiva**

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

![Status](https://img.shields.io/badge/status-completo-brightgreen?style=flat-square)
![Etapas](https://img.shields.io/badge/etapas-6%2F6-brightgreen?style=flat-square)
![Dependências externas](https://img.shields.io/badge/depend%C3%AAncias%20externas-zero-blue?style=flat-square)
![Licença](https://img.shields.io/badge/licença-privado-lightgrey?style=flat-square)

</div>

---

## ✨ Sobre o projeto

Sistema completo de gestão para o atelier de vestidos de noiva **Vitória Oliver**, cobrindo desde o cadastro de clientes até o financeiro, contratos e auditoria.

> 🧵 **100% PHP + MySQL + HTML/CSS/JS puro.** Sem Docker, Node.js, React, Python ou qualquer serviço externo obrigatório. Feito para rodar em rede local via **XAMPP**, exatamente como um atelier precisa: simples de instalar, fácil de manter.

Este README cobre o sistema **completo**, da Etapa 1 à Etapa 6:

| Etapa | Módulo |
|:---:|---|
| 1️⃣ | Estrutura, banco de dados e autenticação |
| 2️⃣ | Clientes · CRM · Agenda |
| 3️⃣ | Vestidos · Medidas · Provas · Produção |
| 4️⃣ | Estoque · Fornecedores · Documentos |
| 5️⃣ | Financeiro · Contratos · Relatórios |
| 6️⃣ | Auditoria · Backup · Segurança |

---

## 📑 Sumário

- [Arquitetura](#-1-arquitetura)
- [Modelo do banco de dados](#-2-modelo-do-banco-de-dados-etapa-1)
- [Perfis de acesso e permissões](#-3-perfis-de-acesso-e-permissões-seed-inicial)
- [Segurança implementada](#-4-segurança-implementada-nesta-etapa)
- [Instalação no XAMPP](#-5-instalação-no-xampp)
- [Como testar a Etapa 1](#-6-como-testar-a-etapa-1)
- [Erros comuns e soluções](#-7-erros-comuns-e-soluções)
- [Etapa 2 — Dashboard, Clientes, CRM e Agenda](#-8-etapa-2--dashboard-clientes-crm-e-agenda)
- [Etapa 3 — Vestidos, Medidas, Provas e Produção](#-9-etapa-3--vestidos-medidas-provas-e-produção)
- [Etapa 4 — Estoque, Fornecedores e Documentos](#-10-etapa-4--estoque-fornecedores-e-documentos)
- [Etapa 5 — Financeiro, Contratos e Relatórios](#-11-etapa-5--financeiro-contratos-com-pdf-e-relatórios)
- [Etapa 6 — Auditoria, Backup e Segurança](#-12-etapa-6--auditoria-backup-guiado-e-revisão-de-segurança-final)
- [Sistema completo](#-13-sistema-completo)

---

## 🏗 1. Arquitetura

MVC simples, sem framework externo, autoload próprio (PSR-4 manual, sem Composer):

```
voatelier/
├── app/
│   ├── core/          Router, Controller e Model base, Database (PDO), Auth, Csrf,
│   │                  PdfGerador (gerador de PDF em PHP puro, sem dependências)
│   ├── controllers/   Auth, Dashboard, Cliente, Crm, Agenda, Vestido, Medida, Prova,
│   │                  Producao, Material, Fornecedor, Documento, Financeiro, Contrato,
│   │                  Relatorio, Auditoria, Backup
│   ├── models/        Usuario, Perfil, Cliente, Agendamento, Vestido, Medida, Prova,
│   │                  Producao, Material, Fornecedor, Anexo, ContaReceber, ContaPagar,
│   │                  Contrato, Auditoria
│   ├── views/         layout/, auth/, dashboard/, clientes/, crm/, agenda/, vestidos/,
│   │                  medidas/, provas/, producao/, estoque/, fornecedores/,
│   │                  financeiro/, contratos/, relatorios/, auditoria/, configuracoes/, errors/
│   └── helpers/       functions.php (e(), url(), flash(), auditoria, armazenarUpload, etc.)
│
├── config/
│   ├── config.php      Configurações gerais (não sensíveis)
│   └── database.php    Credenciais do banco (host, usuário, senha)
│
├── public/              ← ponto de entrada "lógico" da aplicação
│   ├── index.php         Front controller (inclui cabeçalhos de segurança HTTP)
│   ├── css/style.css     Inclui os estilos do Kanban e do calendário
│   ├── js/app.js         Kanban genérico (drag-and-drop) usado no CRM e na Produção
│   └── assets/
│
├── uploads/             Arquivos enviados pelos usuários (protegido contra execução PHP)
│   ├── clientes/ vestidos/ provas/ documentos/ contratos/   (PDFs gerados sob demanda)
│
├── routes/web.php       Definição de todas as rotas
├── database/
│   ├── schema.sql            Tabelas da Etapa 1 (usuários, perfis, permissões, auditoria)
│   ├── seed.sql               Dados iniciais da Etapa 1 (perfis, permissões, admin)
│   ├── schema_etapa2.sql      Tabelas da Etapa 2 (clientes, histórico, CRM, agenda)
│   ├── seed_etapa2.sql        Dados fictícios de demonstração da Etapa 2
│   ├── schema_etapa3.sql      Tabelas da Etapa 3 (vestidos, medidas, provas, produção)
│   ├── seed_etapa3.sql        Dados fictícios de demonstração da Etapa 3
│   ├── schema_etapa4.sql      Tabelas da Etapa 4 (estoque, fornecedores, documentos)
│   ├── seed_etapa4.sql        Dados fictícios de demonstração da Etapa 4
│   ├── schema_etapa5.sql      Tabelas da Etapa 5 (contas a receber/pagar, contratos)
│   ├── seed_etapa5.sql        Dados fictícios de demonstração da Etapa 5
│   └── seed_etapa6.sql        Permissão de Configurações/Backup da Etapa 6 (sem tabelas novas)
├── storage/logs/         Logs de erro do PHP
├── backup.bat            Script de backup via linha de comando (Windows) — alternativa
│                         ao botão de backup pela interface (`/configuracoes/backup`)
├── .htaccess             Roteia tudo para public/index.php e bloqueia pastas internas
└── README.md
```

> **💡 Importante:** embora `public/` seja o front controller "de verdade", o projeto é acessado pela raiz (`http://localhost/voatelier`), como pedido. O `.htaccess` da raiz cuida de:
> 1. Servir os arquivos estáticos de `public/css`, `public/js`, `public/assets` sem precisar digitar `/public` na URL;
> 2. Bloquear acesso direto (via navegador) às pastas `app/`, `config/`, `database/`, `routes/` e `storage/`;
> 3. Encaminhar todas as demais requisições para `public/index.php`, que faz o roteamento interno.
>
> Isso preserva a simplicidade de "copiar a pasta pro htdocs e acessar `/voatelier`" pedida no projeto, mas sem expor código-fonte, configuração ou banco de dados via navegador.

---

## 🗃 2. Modelo do banco de dados (Etapa 1)

Banco `voatelier`, MySQL 8+, InnoDB, utf8mb4.

| Tabela | Finalidade |
|---|---|
| `perfis` | Perfis de acesso (Administrador, Gerente, Atendimento, Costureira, Financeiro) |
| `permissoes` | Permissões granulares no formato `modulo` + `acao` |
| `perfis_permissoes` | Relação N:N entre perfis e permissões |
| `usuarios` | Usuários do sistema, vinculados a um perfil |
| `auditoria` | Log de ações (login, logout, alterações relevantes) |

### Diagrama de relacionamento (Etapa 1)

```
perfis (1) ────────< usuarios (N)
   │
   │ (N:N via perfis_permissoes)
   ▼
permissoes

usuarios (1) ────────< auditoria (N)
```

* Um **perfil** tem muitos **usuários**.
* Um **perfil** tem muitas **permissões**, e uma **permissão** pode pertencer a vários perfis (tabela associativa `perfis_permissoes`).
* Um **usuário** gera muitos registros de **auditoria**.

As demais tabelas do sistema completo (`clientes`, `crm`, `agendamentos`, `vestidos`, `medidas`, `provas`, `producao`, `materiais`, `fornecedores`, `contratos`, `contas_receber`, `contas_pagar`, `anexos`, etc.) seguem o mesmo padrão de nomenclatura e sempre referenciam `usuarios` para rastreabilidade (quem criou/alterou) e `auditoria` para o histórico de mudanças.

---

## 🔐 3. Perfis de acesso e permissões (seed inicial)

| Perfil | Acesso |
|---|---|
| **Administrador** | Total (implícito no código: `Auth::can()` sempre retorna `true`) |
| **Gerente** | Tudo, exceto gestão de usuários e auditoria |
| **Atendimento** | Clientes, CRM, agenda, documentos |
| **Costureira** | Medidas, provas, produção, visualizar vestidos |
| **Financeiro** | Financeiro, relatórios, visualizar contratos |

As permissões já cobrem os módulos das próximas etapas (ex: `clientes.criar`, `vestidos.editar`, `financeiro.visualizar`) para que a tabela não precise ser alterada depois — só popular novas linhas de permissão conforme os módulos forem implementados.

---

## 🛡 4. Segurança implementada nesta etapa

- **SQL:** PDO com prepared statements em 100% das queries (`app/core/Model.php`, `app/models/*.php`). Nenhum dado é concatenado diretamente em SQL.
- **XSS:** função `e()` (wrapper de `htmlspecialchars`) usada em todas as views.
- **CSRF:** classe `App\Core\Csrf`, token validado em todo `POST` (login e troca de senha).
- **Senhas:** `password_hash()` / `password_verify()`, nunca texto puro.
- **Sessão:** `session_regenerate_id(true)` após login; cookies com `HttpOnly` e `SameSite`; `secure` habilitado automaticamente quando o acesso é via HTTPS.
- **Bloqueio de força bruta:** após `LOGIN_MAX_TENTATIVAS` (padrão: 5) tentativas erradas, o usuário fica bloqueado por `LOGIN_BLOQUEIO_MINUTOS` (padrão: 15 minutos).
- **Autorização no backend:** `Controller::requirePermission()` e `Auth::can()` — a interface pode até esconder um botão, mas o controller sempre valida de novo.
- **Uploads:** pasta `uploads/` com `.htaccess` que desabilita a execução de PHP mesmo que um arquivo malicioso seja enviado disfarçado (validação completa de MIME/extensão/tamanho implementada junto do módulo de Documentos, Etapa 4).
- **Erros:** mensagens técnicas nunca aparecem para o usuário final (ver `app/views/errors/500.php`); tudo é gravado em `storage/logs/php-errors.log`.

---

## ⚙️ 5. Instalação no XAMPP

1. Instale o [XAMPP](https://www.apachefriends.org/) (com PHP 8.1 ou superior).
2. Abra o **XAMPP Control Panel** e ative **Apache** e **MySQL**.
3. Copie a pasta `voatelier` inteira para:
   ```
   C:\xampp\htdocs\
   ```
4. Verifique se o módulo `mod_rewrite` do Apache está ativo (vem ativado por padrão no XAMPP). Caso não esteja, edite `C:\xampp\apache\conf\httpd.conf` e remova o `#` da linha `LoadModule rewrite_module modules/mod_rewrite.so`, depois reinicie o Apache.
5. Acesse o **phpMyAdmin** (`http://localhost/phpmyadmin`) ou o terminal do MySQL e crie o banco executando o arquivo `database/schema.sql`:
   - Pelo phpMyAdmin: aba **Importar** → selecione `database/schema.sql` → **Executar**.
   - Depois, importe também `database/seed.sql` da mesma forma (ele já cria o banco, as tabelas, os perfis, as permissões e o usuário administrador).
6. Confira o arquivo `config/database.php`. No XAMPP padrão, o usuário é `root` e a senha é vazia — não é necessário alterar nada na instalação padrão.
7. Acesse pelo navegador:
   ```
   http://localhost/voatelier
   ```
8. Faça login com o usuário administrador inicial:

   | Campo | Valor |
   |---|---|
   | Usuário | `admin` |
   | Senha | `admin123` |

   O sistema vai **obrigar a troca dessa senha** no primeiro acesso.

### 🌐 Acesso pela rede local

Para que outros computadores do atelier acessem o sistema:

1. Descubra o IP da máquina onde o XAMPP está instalado (`ipconfig` no CMD, procure por "Endereço IPv4").
2. Nos outros computadores, acesse:
   ```
   http://IP_DO_SERVIDOR/voatelier
   ```
   Exemplo: `http://192.168.0.100/voatelier`
3. **Firewall do Windows** — libere a porta 80 (ou a porta configurada no Apache) apenas para a rede local:
   - Painel de Controle → Sistema e Segurança → Firewall do Windows Defender → Configurações Avançadas → Regras de Entrada → Nova Regra.
   - Tipo: **Porta** → TCP → Porta específica: `80` (ajuste se o Apache usa outra porta).
   - Ação: **Permitir a conexão**.
   - Perfil: marque apenas **Privado** (rede local) — desmarque **Público** e **Domínio** para não expor o sistema para fora da rede do atelier.
   - Dê um nome como "VO Atelier - Acesso Local" e finalize.

---

## ✅ 6. Como testar a Etapa 1

1. Acesse `http://localhost/voatelier` — deve redirecionar para a tela de **login** (elegante, com a marca "Vitória Oliver Atelier").
2. Tente logar com usuário/senha errados → deve aparecer a mensagem genérica "Usuário ou senha inválidos." (sem revelar se o usuário existe).
3. Erre a senha 5 vezes seguidas → deve bloquear temporariamente o usuário.
4. Logue corretamente com `admin` / `admin123` → deve ser redirecionado para **Trocar senha** (obrigatório no primeiro acesso).
5. Defina uma nova senha (mínimo 8 caracteres) → deve ir para o **Painel (Dashboard)**, mostrando seu nome e perfil "Administrador".
6. Clique em **Sair** → deve voltar para o login e a sessão deve ser invalidada (tentar acessar `/dashboard` diretamente deve redirecionar de novo para `/login`).
7. Confira a tabela `auditoria` no phpMyAdmin — deve haver linhas de `login_sucesso`, `senha_alterada` e `logout`.
8. Tente acessar diretamente pelo navegador:
   ```
   http://localhost/voatelier/config/database.php
   http://localhost/voatelier/app/core/Database.php
   ```
   Ambos devem retornar **403 Forbidden** (bloqueados pelo `.htaccess`).

---

## 🩹 7. Erros comuns e soluções

| Sintoma | Causa provável | Solução |
|---|---|---|
| Tela em branco ou erro 500 | `mod_rewrite` desativado no Apache | Ative o módulo no `httpd.conf` e reinicie o Apache |
| "Não foi possível concluir esta ação" ao abrir o sistema | Banco `voatelier` não existe ou credenciais erradas | Confira `config/database.php` e se rodou `schema.sql` + `seed.sql` |
| Login sempre inválido mesmo com admin/admin123 | `seed.sql` não foi executado, ou executado antes do `schema.sql` | Rode `schema.sql` e depois `seed.sql`, nessa ordem |
| CSS não carrega (página sem estilo) | `BASE_URL` em `config/config.php` não corresponde à pasta real | Se copiou para outra pasta que não `voatelier`, ajuste `BASE_URL` |
| Erro 404 em tudo | `.htaccess` da raiz não está sendo lido | Confirme `AllowOverride All` no `httpd-vhosts.conf`/`httpd.conf` do Apache |
| "This page isn't working" em loop de redirecionamento | BASE_URL incorreta causando loop de redirect | Revise `BASE_URL` em `config/config.php` |

> Para depurar problemas durante o desenvolvimento, deixe `APP_ENV` como `'development'` em `config/config.php` (mostra erros na tela). **Troque para `'production'` antes do uso real no atelier**, para nunca expor detalhes técnicos às usuárias do sistema.

---

## 📇 8. Etapa 2 — Dashboard, Clientes, CRM e Agenda

<details>
<summary><strong>Ver detalhes da Etapa 2</strong></summary>

### 8.1 Instalação da Etapa 2

Depois de já ter rodado `schema.sql` e `seed.sql` (Etapa 1), execute também, **nesta ordem**:

1. `database/schema_etapa2.sql` — cria as tabelas `clientes`, `historico_clientes`, `crm_historico` e `agendamentos`.
2. `database/seed_etapa2.sql` *(opcional)* — cadastra 6 clientes fictícias em diferentes etapas do funil e alguns agendamentos de demonstração (hoje e nos próximos dias), para você já testar o sistema com dados. **Remova esses registros antes do uso real.**

Pelo phpMyAdmin: aba **Importar** → selecione o arquivo → **Executar**, na mesma ordem acima.

### 8.2 O que foi implementado

**Dashboard** (`/dashboard`) agora mostra dados reais: agendamentos de hoje, novos contatos no mês, clientes em negociação, alerta de casamentos nos próximos 30 dias, tabela do dia e resumo do pipeline comercial. Os blocos de Produção e Financeiro continuam como placeholder até as Etapas 3 e 5.

**Clientes** (`/clientes`):
- Listagem com busca por nome, CPF, telefone ou WhatsApp.
- Cadastro e edição com todos os campos pedidos (dados pessoais + dados do casamento).
- Ficha completa da cliente (`/clientes/{id}`) com informações, casamento, agenda vinculada, histórico de movimentação no CRM e histórico de alterações de campos (quem mudou o quê e quando).
- Exclusão **lógica** (campo `ativo = 0`) em vez de `DELETE` — a cliente some das listagens, mas o histórico e os vínculos com agenda/CRM são preservados. Essa decisão evita registros órfãos quando os módulos de Vestidos, Contratos e Financeiro (próximas etapas) passarem a referenciar `clientes.id`.

**CRM** (`/crm`): pipeline em Kanban com as 7 etapas do funil. Arraste um card entre colunas para mudar a etapa (via JavaScript puro + `fetch`, sem recarregar a página); a movimentação é validada no backend (permissão `crm.editar` + token CSRF) e registrada em `crm_historico`. Ao mover um card para "Perdido", o sistema pergunta o motivo da perda.

**Agenda** (`/agenda`): calendário mensal (navegação entre meses), com os compromissos do dia coloridos por tipo. Criar/editar um agendamento (`/agenda/novo`, `/agenda/{id}/editar`) verifica conflito de horário para o mesmo responsável no mesmo dia; se houver conflito, o sistema **alerta** com os detalhes do compromisso conflitante e permite confirmar o salvamento mesmo assim (opção "Salvar mesmo assim").

</details>

---

## 👗 9. Etapa 3 — Vestidos, Medidas, Provas e Produção

<details>
<summary><strong>Ver detalhes da Etapa 3</strong></summary>

O módulo de **Vestidos** (`/vestidos`) traz cadastro com código único, tipo (noiva/madrinha/mãe da noiva/outro), tecido, tamanho, cor e valor. A troca de status (Disponível → Reservado → Em produção → Indisponível) é feita por uma ação dedicada que sempre grava uma linha em `historico_vestidos` — o campo `status` não é editado pelo formulário comum de edição, exatamente para garantir que toda mudança de status fique rastreada.

**Medidas**: acessível pela ficha da cliente (`/clientes/{id}/medidas`). Cada envio do formulário cria um **novo registro** — não existe update aqui de propósito, para nunca perder o histórico de medidas anteriores, como pedido na especificação.

**Provas** (`/provas`): cada prova tem um número sequencial automático por cliente, lista de **ajustes** (com descrição, parte do vestido, medida atual/desejada e status individual) e permite anexar **fotos** — o upload passa pela mesma validação de segurança do resto do sistema (extensão + MIME real do conteúdo + tamanho máximo, arquivo sempre renomeado pelo sistema). Testado manualmente enviando um `.php` disfarçado de `.jpg` e um `.exe`: ambos foram rejeitados.

**Produção** (`/producao`): Kanban com as 11 etapas do fluxo de confecção (Projeto → Desenho → Aprovação → Modelagem → Corte → Costura → Bordado → Ajustes → Acabamento → Finalização → Entrega), reaproveitando o mesmo mecanismo de drag-and-drop do CRM. Projetos com prazo vencido aparecem em vermelho no card e disparam um alerta no topo do Kanban e no Dashboard.

A ficha da cliente agora também mostra a última ficha de medidas, a lista de provas e os vestidos vinculados a ela.

### 9.3 Como testar a Etapa 3

1. Rode o seed de demonstração e acesse `/dashboard` — deve aparecer o alerta vermelho "vestido(s) com produção atrasada" (o projeto da Juliana Costa foi propositalmente cadastrado com prazo vencido).
2. Acesse `/vestidos`, abra o **VO-001** e confira o histórico — deve mostrar o cadastro inicial e o status atual.
3. Cadastre um vestido novo com um código já existente (ex: `VO-001`) — o sistema deve recusar com a mensagem "Já existe um vestido cadastrado com este código."
4. Na ficha de uma cliente, adicione uma nova ficha de medidas e confirme que a anterior continua aparecendo no histórico (nunca é sobrescrita).
5. Crie uma prova, adicione um ajuste e envie uma foto (JPG ou PNG). Tente enviar um arquivo `.exe` ou `.php` renomeado para `.jpg` — deve ser rejeitado.
6. Acesse `/producao` e arraste um card para a próxima etapa — o contador da coluna atualiza na hora e a movimentação fica registrada no histórico do projeto.

### 9.4 Erros comuns adicionais (Etapa 3)

| Sintoma | Causa provável | Solução |
|---|---|---|
| "Já existe um vestido cadastrado com este código" ao editar sem trocar o código | Comportamento esperado — o próprio vestido é ignorado na checagem ao editar, então isso só deve ocorrer se o código pertencer a outro registro | Confirme o código digitado |
| Foto não aparece após o upload | Pasta `uploads/provas/` sem permissão de escrita no servidor | No Windows/XAMPP isso raramente ocorre; confirme que a pasta existe e não está bloqueada por antivírus |
| Upload sempre recusado mesmo com JPG válido | `UPLOAD_ALLOWED_MIME` em `config/config.php` não bate com o MIME real do arquivo | Verifique se o arquivo não está corrompido; o sistema checa o conteúdo real, não a extensão |
| Card de produção não sai do lugar | Mesmo caso do Kanban do CRM — confirme permissão `producao.editar` e o carregamento de `public/js/app.js` |

</details>

---

## 📦 10. Etapa 4 — Estoque, Fornecedores e Documentos

<details>
<summary><strong>Ver detalhes da Etapa 4</strong></summary>

### 10.1 Instalação da Etapa 4

Depois das Etapas 1, 2 e 3, execute nesta ordem:

1. `database/schema_etapa4.sql` — cria `fornecedores`, `materiais`, `movimentacoes_estoque` e `anexos` (documentos gerais da cliente).
2. `database/seed_etapa4.sql` *(opcional)* — 3 fornecedores e 5 materiais, dois deles propositalmente abaixo do estoque mínimo, para você testar o alerta.

### 10.2 O que foi implementado

**Estoque** (`/estoque`): cadastro de materiais com código único, categoria, unidade, estoque mínimo e fornecedor. A quantidade em estoque **nunca é editada diretamente** — toda mudança passa pela ação "Movimentar estoque", que registra Entrada, Saída ou Ajuste em `movimentacoes_estoque` de forma atômica (transação no banco) e mantém o histórico completo. Uma saída maior que o disponível é recusada com uma mensagem clara. Materiais com quantidade abaixo do estoque mínimo aparecem destacados em vermelho e disparam alerta no Dashboard e na própria listagem.

**Fornecedores** (`/fornecedores`): CRUD simples, com a ficha do fornecedor mostrando todos os materiais vinculados a ele (relação feita pelo campo `fornecedor_id` em `materiais`).

**Documentos**: a ficha da cliente ganhou uma seção "Documentos e fotos" para anexar arquivos gerais (RG, comprovante, contrato assinado em PDF, etc.), com a mesma validação de segurança usada nas fotos de prova (extensão + MIME real + tamanho, nome sempre gerado pelo sistema).

### 10.3 Como testar a Etapa 4

1. Rode o seed e acesse `/dashboard` — deve aparecer o alerta "material(is) de estoque abaixo do mínimo" citando Tule ilusão e Pedraria strass.
2. Acesse `/estoque`, marque "Só abaixo do mínimo" — deve filtrar para esses dois.
3. Abra um material, registre uma **Entrada** de 10 unidades e confira que a quantidade e o histórico atualizam. Tente uma **Saída** maior que o disponível — deve ser recusada. Use **Ajuste** para definir a quantidade exata (útil após contagem física de inventário).
4. Cadastre um material com um código já existente — deve ser recusado.
5. Na ficha de uma cliente, envie um documento PDF ou uma foto — confira que aparece na lista e que o link abre o arquivo.
6. Logado com um perfil **Atendimento**, confirme que `/estoque` e `/fornecedores` retornam 403 e não aparecem no menu lateral.

### 10.4 Erros comuns adicionais (Etapa 4)

| Sintoma | Causa provável | Solução |
|---|---|---|
| Quantidade não muda mesmo após "salvar" | A quantidade só muda pela ação **Movimentar estoque**, não pela edição comum do material | Use o formulário "Movimentar estoque" na ficha do material |
| "Quantidade de saída maior que o estoque disponível" | Tentativa de retirar mais do que existe | Registre uma entrada primeiro, ou ajuste o estoque para o valor correto |
| Documento enviado não aparece na lista | Upload falhou silenciosamente (extensão/MIME/tamanho inválido) | Confira a mensagem de erro exibida; só JPG, PNG e PDF são aceitos |

</details>

---

## 💰 11. Etapa 5 — Financeiro, Contratos (com PDF) e Relatórios

<details>
<summary><strong>Ver detalhes da Etapa 5</strong></summary>

### 11.1 Instalação da Etapa 5

Depois das Etapas 1 a 4, execute nesta ordem:

1. `database/schema_etapa5.sql` — cria `contas_receber`, `contas_pagar` e `contratos`.
2. `database/seed_etapa5.sql` *(opcional)* — algumas contas pagas, pendentes e **vencidas** de propósito (para testar os alertas), e um contrato de exemplo pronto para gerar o PDF.

> **⚠️ Atenção à acentuação ao importar via linha de comando:** se você importar os arquivos `.sql` pelo terminal em vez do phpMyAdmin, use sempre `--default-character-set=utf8mb4`, por exemplo:
> ```
> mysql -u root --default-character-set=utf8mb4 voatelier < database/seed_etapa5.sql
> ```
> Sem essa flag, o cliente `mysql` pode interpretar o arquivo com o charset errado e gravar acentos duplicados no banco (ex: "ç" virar "Ã§"). Pelo phpMyAdmin (como este guia recomenda) isso não acontece — a flag só é necessária para quem prefere o terminal.

### 11.2 O que foi implementado

**Financeiro** (`/financeiro`): painel de fluxo de caixa com receitas/despesas do mês, saldo, valores a receber/pagar e alertas de inadimplência. As telas de **Contas a receber** (`/financeiro/receber`) e **Contas a pagar** (`/financeiro/pagar`) permitem registrar lançamentos e marcar como pago com um clique. O status "Vencido" **não fica gravado no banco** — é calculado na consulta (`status = 'pendente' AND vencimento < hoje`). Isso evita depender de uma tarefa agendada (cron) só para manter esse status em dia, o que seria mais uma peça para quebrar em um servidor local no XAMPP sem administração dedicada.

**Contratos** (`/contratos`): cadastro com cláusulas em texto livre, vestido e valores vinculados. O botão **Gerar PDF** cria um PDF de verdade a partir dos dados do contrato, usando um gerador de PDF **escrito do zero em PHP puro** (`app/core/PdfGerador.php`) — sem Composer, sem bibliotecas externas, coerente com o restante do projeto. Ele monta a estrutura binária do PDF manualmente (objetos, fontes padrão do PDF, xref e trailer), com quebra de linha e paginação automáticas. A saída foi validada com `qpdf --check` (sem erros de sintaxe) e `pdftotext` (texto e acentuação extraídos perfeitamente). Cada vez que o PDF é gerado novamente, o arquivo anterior é apagado para não acumular lixo em `uploads/contratos/`.

**Relatórios** (`/relatorios`): quatro relatórios — Comercial, Financeiro, Produção e Estoque — cada um com um botão **Exportar CSV** (com BOM UTF-8, para o Excel reconhecer acentuação corretamente). Exportação em PDF não foi implementada para os relatórios nesta etapa (o gerador de PDF criado é focado em documentos de texto como o contrato); os relatórios ficam disponíveis em tela e em CSV, que cobre a grande maioria dos usos reais (abrir no Excel, importar em outra planilha etc.).

A ficha da cliente agora também mostra os contratos e as contas a receber vinculadas a ela.

### 11.3 Como testar a Etapa 5

1. Rode o seed de demonstração e acesse `/dashboard` — deve aparecer o alerta vermelho de pendências financeiras vencidas.
2. Acesse `/financeiro/receber`, filtre por "Vencidas" — deve aparecer o sinal da Ana Eloise Souza. Marque como pago e confirme que ele sai do filtro.
3. Acesse `/contratos`, abra o contrato de demonstração e clique em **Gerar PDF**. Abra o PDF gerado e confira que o texto e a acentuação aparecem corretamente.
4. Acesse `/relatorios/financeiro` e clique em **Exportar CSV** — abra o arquivo no Excel/LibreOffice e confirme que os acentos aparecem certos.
5. Com um usuário de perfil **Atendimento**, confirme que `/financeiro`, `/contratos` e `/relatorios` retornam 403 e não aparecem no menu lateral.

### 11.4 Erros comuns adicionais (Etapa 5)

| Sintoma | Causa provável | Solução |
|---|---|---|
| Acentos aparecem trocados no PDF ou no banco (ex: "Ã§Ã£o") | Import via linha de comando sem charset correto | Reimporte usando `mysql --default-character-set=utf8mb4 ...` ou prefira o phpMyAdmin |
| "Vencido" não aparece mesmo com data no passado | Conta já está com status "pago" ou "cancelado" — só contas "pendentes" ficam "vencidas" | Confira o status da conta |
| Botão "Gerar PDF" não responde / erro 500 | Pasta `uploads/contratos/` sem permissão de escrita | Confirme que o PHP consegue criar pastas dentro de `uploads/` (criada automaticamente na primeira geração) |
| CSV abre com acentos errados no Excel antigo | Versões muito antigas do Excel ignoram o BOM UTF-8 | Importar o CSV manualmente escolhendo "UTF-8" como codificação |

</details>

---

## 🔍 12. Etapa 6 — Auditoria, Backup guiado e Revisão de segurança (final)

<details open>
<summary><strong>Ver detalhes da Etapa 6</strong></summary>

Esta é a última etapa. **O sistema está funcionalmente completo.**

### 12.1 Instalação da Etapa 6

Depois das Etapas 1 a 5, execute:

```
database/seed_etapa6.sql
```

Não há tabelas novas nesta etapa — a tabela `auditoria` já existia desde a Etapa 1 (o sistema grava nela desde o primeiro login). Este arquivo só adiciona a permissão `configuracoes.visualizar`, concedida ao perfil Administrador, para liberar a tela de backup.

### 12.2 O que foi implementado

**Auditoria na interface** (`/auditoria`, exclusivo do Administrador): até então a auditoria só existia dentro do banco de dados. Agora há uma tela para consultá-la, com filtros por módulo, usuário, período e busca por texto, mostrando data/hora, usuário, IP, ação e um "Ver detalhes" expansível com os dados antes/depois de cada alteração relevante.

**Backup guiado** (`/configuracoes/backup`, exclusivo do Administrador): dois botões — **Baixar backup do banco (.sql)** e **Baixar uploads (.zip)** — que geram os arquivos e iniciam o download na hora, sem precisar abrir o phpMyAdmin ou mexer em linha de comando. O dump do banco é gerado **inteiramente em PHP puro via PDO** (sem chamar `mysqldump`/`exec()`, que muitas hospedagens compartilhadas desabilitam por segurança), incluindo estrutura (`CREATE TABLE`) e dados (`INSERT INTO`) de todas as tabelas. Testado de ponta a ponta: backup gerado, **restaurado em um banco novo do zero**, com a contagem de linhas de todas as tabelas batendo exatamente, inclusive a acentuação em português. O backup de uploads usa a extensão `ZipArchive` (nativa do PHP, incluída por padrão no XAMPP).

**Revisão de segurança:**
- Cabeçalhos HTTP de segurança adicionados (`X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: same-origin`).
- Auditoria de código confirmando que **todo** formulário `POST` do sistema inclui token CSRF, e que **todo** método de controller (exceto login/logout) exige autenticação e a permissão correspondente antes de executar qualquer ação.
- Varredura de todas as views confirmando que dados vindos do usuário ou do banco sempre passam por `htmlspecialchars()` (função `e()`) antes de ir para o HTML.
- Confirmação de que a pasta `uploads/` (incluindo a nova subpasta `uploads/contratos/`, criada sob demanda) herda automaticamente a proteção contra execução de PHP definida no `.htaccess` da pasta.

### 12.3 Como testar a Etapa 6

1. Logado como `admin`, acesse `/auditoria` — deve listar as ações recentes (login, cadastros, edições). Filtre por módulo "auth" — deve mostrar só eventos de login.
2. Acesse `/configuracoes/backup` e clique em **Baixar backup do banco** — o download de um `.sql` deve iniciar. Abra o arquivo em um editor de texto e confira que tem `CREATE TABLE` e `INSERT INTO` para as tabelas do sistema.
3. Clique em **Baixar uploads** — deve baixar um `.zip` com o conteúdo da pasta `uploads/`.
4. Com um usuário de perfil que não seja Administrador, confirme que `/auditoria` e `/configuracoes/backup` retornam 403 e não aparecem no menu lateral.
5. Abra as ferramentas de desenvolvedor do navegador (aba Network) em qualquer página do sistema e confirme a presença dos cabeçalhos `X-Content-Type-Options` e `X-Frame-Options` na resposta.

### 12.4 Erros comuns adicionais (Etapa 6)

| Sintoma | Causa provável | Solução |
|---|---|---|
| Botão "Baixar uploads" dá erro "extensão ZipArchive não disponível" | Extensão `zip` do PHP desabilitada | No XAMPP ela vem habilitada por padrão; se desabilitada, ative `extension=zip` no `php.ini` e reinicie o Apache |
| Link "Auditoria" ou "Configurações" não aparece no menu | Usuário logado não é Administrador | Isso é esperado — essas telas são restritas por design |
| Backup do banco demora muito ou trava | Banco de dados muito grande | Para bancos muito grandes, prefira o `backup.bat` (via `mysqldump`, mais eficiente) em vez do botão da interface |

### 12.5 Checklist final de segurança

| Item | Status |
|---|:---:|
| Senhas com `password_hash()`/`password_verify()` | ✅ |
| SQL sempre via PDO com prepared statements | ✅ |
| Saída em HTML sempre escapada (`htmlspecialchars`) | ✅ |
| Token CSRF em 100% dos formulários POST | ✅ |
| `session_regenerate_id()` após login | ✅ |
| Cookies de sessão `HttpOnly` + `SameSite` | ✅ |
| Bloqueio por tentativas de login | ✅ |
| Autorização (permissão) checada no backend, não só na interface | ✅ |
| Upload valida extensão + MIME real + tamanho, renomeia arquivo | ✅ |
| Execução de PHP desabilitada em `uploads/` | ✅ |
| Pastas internas (`app/`, `config/`, `database/`, etc.) bloqueadas via `.htaccess` | ✅ |
| Erros técnicos nunca expostos ao usuário final | ✅ |
| Cabeçalhos de segurança HTTP | ✅ |
| Auditoria de ações sensíveis | ✅ |
| Backup testado ponta a ponta (gerar → restaurar → validar) | ✅ |

> **⚠️ Antes de usar em produção real no atelier:** troque `APP_ENV` para `'production'` em `config/config.php` (desativa a exibição de erros técnicos na tela).

</details>

---

## 🏁 13. Sistema completo

Todas as 6 etapas do sistema estão implementadas, testadas e documentadas:

- [x] **Etapa 1:** Estrutura, banco de dados, MVC, login e permissões.
- [x] **Etapa 2:** Dashboard, Clientes, CRM (Kanban) e Agenda.
- [x] **Etapa 3:** Vestidos, Medidas, Provas e Produção (Kanban).
- [x] **Etapa 4:** Estoque, Fornecedores e Documentos/Fotos.
- [x] **Etapa 5:** Financeiro, Contratos (com geração de PDF) e Relatórios.
- [x] **Etapa 6:** Auditoria na interface, backup guiado e revisão de segurança.

O sistema está pronto para ser instalado no XAMPP seguindo as instruções da [Seção 5](#-5-instalação-no-xampp) e usado no dia a dia do atelier.

<div align="center">

---

Feito com 🧵 para o **Atelier Vitória Oliver**

</div>
