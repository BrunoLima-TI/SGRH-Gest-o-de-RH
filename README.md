# SGRH — Sistema de Gestão de Recursos Humanos

Sistema web completo de gestão de RH, desenvolvido em **PHP com arquitetura MVC** sobre o
**MySQL do XAMPP**, com interface moderna, responsiva e interativa.

Implementado a partir do documento *“Sistema de Gestão de Recursos Humanos — O Papel do SGRH
Nas Organizações”* (CETEP, disciplina de Sistema de Gestão de RH), que define os processos de
**coleta → organização → análise → decisão → avaliação** aplicados a **pessoas + processos + dados**.

---

## 1. Instalação (3 passos)

### 1. Copie o projeto
Coloque a pasta `Sistema-Gerenciamento-RH` dentro de `C:\xampp\htdocs\`.

### 2. Ligue o MySQL
Inicie o **Apache** e o **MySQL** pelo painel do XAMPP.

### 3. Acesse o sistema
```
http://localhost/Sistema-Gerenciamento-RH/
```

**Na primeira execução o sistema cria sozinho o banco de dados, todas as tabelas e popula
com dados de demonstração** (42 colaboradores, ~2.700 registros de frequência, férias,
vagas, candidatos, treinamentos, avaliações e documentos).

Não é preciso executar nenhum script manualmente. Mas, se preferir, há um instalador
autônomo:

| Forma | Comando / URL |
|---|---|
| Navegador | `http://localhost/Sistema-Gerenciamento-RH/instalar.php` |
| Linha de comando | `php instalar.php` |
| Ver status | `php instalar.php status` |

---

## 2. Credenciais

| Perfil | E-mail | Senha | Permissões |
|---|---|---|---|
| Administrador | `admin@sgrh.local` | `sgrh@2026` | Acesso total |
| Recursos Humanos | `rh@sgrh.local` | `sgrh@2026` | Gestão de pessoas, férias, documentos, recrutamento |
| Gestor | `gestor@sgrh.local` | `sgrh@2026` | Frequência, avaliações, Pipeline |

> Para trocar a senha do administrador, edite a senha no sistema ou rode
> `UPDATE usuarios SET senha = '...' WHERE email = 'admin@sgrh.local';`
> gerando o hash com `password_hash('novaSenha', PASSWORD_DEFAULT)`.

---

## 3. Configuração do banco

As credenciais ficam em **`config/config.php`**:

```php
'db' => [
    'host'    => '127.0.0.1',
    'porta'   => 3306,
    'nome'    => 'sgrh',
    'usuario' => 'sgrh',
    'senha'   => 'sgrh_dev',
    'charset' => 'utf8mb4',
],
```

O usuário `sgrh` foi criado com permissão total apenas para o desenvolvimento local.
Troque a senha em `config/config.php` **e** no MySQL antes de usar em produção.

---

## 4. Arquitetura MVC

```
Sistema-Gerenciamento-RH/
├── index.php                  # Front Controller (ponto de entrada único)
├── instalar.php               # Instalador do banco (web + CLI)
├── .htaccess                  # Rewrite das URLs para o front controller
├── config/
│   ├── config.php             # Configurações (banco, sessão, uploads)
│   └── routes.php             # Tabela de rotas
├── app/
│   ├── bootstrap.php          # Constantes, autoloader, sessão, error handler
│   ├── Core/
│   │   ├── Database.php       # Conexão PDO
│   │   ├── Model.php          # Active Record leve (CRUD)
│   │   ├── Controller.php     # Base dos controllers
│   │   ├── Router.php         # Rotas com parâmetros {id}
│   │   ├── View.php           # Renderização + layout
│   │   ├── Auth.php           # Login e perfis de acesso
│   │   ├── Csrf.php           # Proteção CSRF por token
│   │   ├── Validator.php      # Validação de formulários (CPF, e-mail…)
│   │   ├── Session.php        # Sessão e flash messages
│   │   ├── Charts.php         # Gráficos SVG gerados por PHP
│   │   ├── Installer.php      # Cria banco, tabelas e seed
│   │   └── Helpers.php        # Funções de view (e, url, badge, paginação…)
│   ├── Models/                # 11 models (Active Record)
│   ├── Controllers/           # 11 controllers
│   └── Views/                 # Layouts, partials e views de cada módulo
├── assets/
│   ├── css/app.css            # Design system completo (tokens + componentes)
│   └── js/app.js              # Interações (sidebar, modal, toasts, máscaras…)
├── database/schema.sql        # DDL das 12 tabelas
├── storage/                   # Registro da instalação
└── uploads/                   # Upload de documentos
```

**Fluxo de uma requisição:**
`index.php` → verifica/cria o banco → valida o token CSRF → `Router` → `Controller` →
`Model` (PDO) → `View` (PHP puro) → HTML com layout.

---

## 5. Módulos do sistema

| Módulo | Funcionalidades |
|---|---|
| **Painel de indicadores** | 4 KPIs (quadro, rotatividade, absenteísmo, vagas), gráficos de absenteísmo e distribuição por departamento, próximas férias, alertas/pendências, treinamentos em andamento, aniversariantes, média de desempenho |
| **Colaboradores** | Cadastro completo (pessoal, funcional, remuneração), busca e filtros, status (ativo/férias/afastado/inativo), paginação, ficha individual com histórico de frequência, férias, capacitação, avaliações e documentos |
| **Frequência** | Matriz mensal colaborador × dia com células coloridas por situação, indicadores de presenças/faltas/atrasos/horas extras, registro rápido em modal, detecção automática de horas trabalhadas |
| **Férias e ausências** | Férias, atestados, licenças, faltas e afastamentos; saldo de férias de 30 dias por colaborador; fila de aprovação com decisão em um clique; bloqueio de períodos sobrepostos |
| **Recrutamento** | Cadastro de vagas, pipeline visual em 5 etapas (candidatura → análise → entrevista → ofertado → contratado), movimentação de candidatos, cadastro de candidatos |
| **Treinamentos** | Trilhas de capacitação com carga horária, instrutor e custo; inscrições com progresso individual e taxa de conclusão |
| **Desempenho** | Ciclos de avaliação, 6 critérios com notas de 1 a 10, média geral, distribuição de notas, média por critério e parecer do avaliador |
| **Documentos** | Registro por tipo com data de validade e alerta automático de vencimento em 30 dias |
| **Relatórios** | Absenteísmo, horas trabalhadas, quadro por departamento, composição por contrato, série de admitidos/desligados, média por critério e **exportação CSV** |
| **Estrutura** | Departamentos e cargos com contagem de pessoal |
| **Usuários** | Controle de acesso por perfil (admin, RH, gestor, colaborador) |

---

## 6. Interface (UI/UX)

Design system próprio, sem frameworks externos, sem CDN e sem build — **funciona offline**.

- **Tokens CSS** com paleta índigo/slate, estados semânticos e escalas de espaçamento, tipografia e raio
- **Layout**: sidebar fixa (264px, colapsável, off-canvas no mobile) + topbar com busca, breadcrumb e menu de usuário
- **Componentes**: cards, KPIs com sparkline SVG, tabelas (com transformação para cards no mobile), badges de status, formulários com validação inline, modais, toasts, tabs, paginação, empty states, progress bars, timelines, dropdowns
- **Gráficos**: SVG gerado pelo PHP (barras, rosca, linha com área e sparkline) — sem bibliotecas de JS
- **Acessibilidade**: navegação por teclado, `aria-*`, foco visível, `prefers-reduced-motion`, contraste AA
- **Responsivo**: 1 a 4 colunas conforme a largura; tabelas viram cards abaixo de 768px

A especificação completa do design está em [`DESIGN-SPEC.md`](DESIGN-SPEC.md).

---

## 7. Segurança

- Senhas com `password_hash()` / `password_verify()`
- **Proteção CSRF** em todos os formulários POST
- Escape de saída com `htmlspecialchars()` (`e()`) contra XSS
- Consultas exclusivamente via **prepared statements** (PDO)
- Controle de acesso por perfil em cada controller
- Sessão com cookie `HttpOnly` + `SameSite=Lax`

---

## 8. Requisitos

- PHP 8.0+ (testado no PHP 8.2 do XAMPP) com `pdo_mysql`
- MySQL 5.7+ / MariaDB 10.4+
- Apache com `mod_rewrite` (habilitado por padrão no XAMPP)
