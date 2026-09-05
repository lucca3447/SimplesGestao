# 🚀 Simples — Sistema de Gestão Empresarial (ERP)

> **Projeto Fullstack Desacoplado** composto por API REST em **Laravel 11**, banco de dados relacional **PostgreSQL (Docker)**, autenticação stateless **Laravel Sanctum** e Single Page Application (SPA) em **Vue 3 (Vite + Tailwind CSS + Pinia)**.  
> Desenvolvido com foco nas necessidades reais de gestão para pequenas e médias empresas: clientes, catálogo, controle de estoque crítico, pedidos com congelamento histórico de preços e fluxo de caixa contábil consolidado.

---

## 🏗️ Arquitetura do Sistema

O projeto adota arquitetura desacoplada (Headless / API-First):

```text
┌─────────────────────────────────────────────────────────┐
│              Frontend SPA (Vue 3 + Vite)                │
│    Tailwind CSS · Pinia · Chart.js · Lucide Icons       │
└────────────────────────────┬────────────────────────────┘
                             │  HTTP / JSON (Bearer Token)
                             ▼
┌─────────────────────────────────────────────────────────┐
│              Backend REST API (Laravel 11)              │
│   Sanctum Auth · Form Requests · Eloquent · Resources   │
└────────────────────────────┬────────────────────────────┘
                             │  PDO pgsql (Porta 5433)
                             ▼
┌─────────────────────────────────────────────────────────┐
│               PostgreSQL 16 (Container)                 │
│         Volumes persistentes · Docker Compose           │
└─────────────────────────────────────────────────────────┘
```

---

## 🛠️ Tecnologias Utilizadas

### Backend
* **PHP 8.4** & **Laravel 11+**
* **PostgreSQL 16** via Docker Compose (porta `5433`)
* **Laravel Sanctum:** Autenticação stateless via Bearer Tokens com persistência segura
* **Eloquent ORM:** Relacionamentos `hasMany` / `belongsTo`, casts nativos e locks pessimistas (`lockForUpdate`)
* **Dedoc Scramble:** Documentação interativa OpenAPI 3.1 / Swagger UI nativa
* **PHPUnit:** 29 testes de integração automatizados com 100% de aprovação (221 asserções)

### Frontend
* **Vue 3 (Composition API & `<script setup>`)** com **Vite**
* **Pinia:** Gerenciamento centralizado de estado reativo (sessão, perfil do usuário e token Sanctum)
* **Vue Router 4:** Roteamento com Navigation Guards (`beforeEach`) para proteção de rotas privadas
* **Tailwind CSS:** Design system corporativo inspirado em interfaces de gestão modernas (paleta `#e06236`, `#208b5d` e fundos neutros `#f5f6f8`)
* **Chart.js & vue-chartjs:** Visualização de fluxo de caixa consolidado (entradas × saídas) e despesas por categoria
* **Lucide Vue:** Ícones vetoriais padronizados
* **Práticas Modern Web Guidance:** Modais com elemento nativo `<dialog closedby="any">`, light-dismiss com detecção de coordenadas do backdrop, formulários semânticos HTML5 com validações e acessibilidade ARIA

---

## ⚡ Como Rodar o Projeto Localmente

### Pré-requisitos
* Docker e Docker Compose instalados
* PHP 8.2+ e Composer
* Node.js 18+ e npm

---

### 1. Iniciar o Backend (API REST)

```bash
# Entrar no diretório da API
cd simples-gestao-api

# Subir container do PostgreSQL 16 isolado na porta 5433
docker compose up -d

# Instalar dependências PHP
composer install

# Executar migrações e popular com dados de demonstração
php artisan migrate:fresh --seed

# Iniciar servidor da API
php artisan serve
```

A API estará disponível em: `http://localhost:8000`  
Documentação Swagger interativa: `http://localhost:8000/docs/api`

---

### 2. Iniciar o Frontend (Vue 3 SPA)

Em outro terminal:

```bash
# Entrar no diretório do frontend
cd simples-gestao-web

# Instalar dependências JavaScript
npm install

# Iniciar servidor de desenvolvimento do Vite
npm run dev
```

O aplicativo estará acessível em: `http://localhost:5173`

---

## 🔑 Credenciais Padrão de Demonstração

O seeder inicializa automaticamente contas pré-configuradas com botão de 1 clique na tela de login:

| E-mail | Senha | Papel (Role) | Permissões |
|---|---|---|---|
| `admin@simplesgestao.com` | `password` | Administrador | Acesso irrestrito a todas as métricas, cadastros e lançamentos |
| `maria@simplesgestao.com` | `password` | Operador | Gestão de clientes, produtos, emissão de pedidos e estoque |

---

## 📑 Documentação Interativa da API (Swagger / OpenAPI)

Com a API rodando, acesse a documentação interativa no navegador:

👉 **`http://localhost:8000/docs/api`**

Permite visualizar esquemas de dados, regras de validação e testar requisições em tempo real (*Try it out*) diretamente pelo navegador.  
O arquivo de especificação OpenAPI 3.1 também está disponível em `http://localhost:8000/docs/api.json`.

---

## 🚦 Catálogo Completo de Endpoints

### 🔐 1. Autenticação (`/api/auth`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `POST` | `/api/auth/register` | Não | Registra novo usuário (papel forçado como `operator`). |
| `POST` | `/api/auth/login` | Não | Autentica e retorna o Bearer Token Sanctum. |
| `GET` | `/api/auth/me` | **Sim** | Retorna o perfil do usuário logado. |
| `POST` | `/api/auth/logout` | **Sim** | Revoga o token de acesso no banco. |

---

### 👥 2. Clientes (`/api/customers`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/customers` | **Sim** | Lista clientes (suporta `search`, `per_page`, paginação). |
| `POST` | `/api/customers` | **Sim** | Cadastra um novo cliente com validação de CPF/CNPJ. |
| `GET` | `/api/customers/{id}` | **Sim** | Detalha um cliente específico. |
| `PUT` | `/api/customers/{id}` | **Sim** | Atualiza dados cadastrais. |
| `DELETE` | `/api/customers/{id}` | **Sim** | Exclui cliente do sistema. |

---

### 🏷️ 3. Categorias de Produtos (`/api/categories`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/categories` | **Sim** | Lista categorias (suporta `all=1` e contagem de produtos). |
| `POST` | `/api/categories` | **Sim** | Cadastra uma nova categoria. |
| `GET` | `/api/categories/{id}` | **Sim** | Detalha a categoria com contagem de produtos. |
| `PUT` | `/api/categories/{id}` | **Sim** | Atualiza nome e descrição. |
| `DELETE` | `/api/categories/{id}` | **Sim** | Exclui categoria (bloqueia com 422 se houver produtos). |

---

### 📦 4. Produtos e Estoque (`/api/products`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/products` | **Sim** | Lista produtos com categoria (`search`, `category_id`, `is_active`, `low_stock`). |
| `GET` | `/api/products/low-stock` | **Sim** | Lista apenas produtos com **estoque crítico** (`saldo <= mínimo`). |
| `POST` | `/api/products` | **Sim** | Cadastra produto com SKU, preço, custo e estoque mínimo. |
| `GET` | `/api/products/{id}` | **Sim** | Detalha um produto. |
| `PUT` | `/api/products/{id}` | **Sim** | Atualiza dados e saldo de estoque. |
| `DELETE` | `/api/products/{id}` | **Sim** | Exclui produto (bloqueia com 422 se tiver histórico de vendas). |

---

### 🛒 5. Pedidos e Vendas (`/api/orders`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/orders` | **Sim** | Lista pedidos (`status`, `customer_id`, `start_date`, `end_date`). |
| `POST` | `/api/orders` | **Sim** | Cria pedido com múltiplos itens dentro de transação atômica. |
| `GET` | `/api/orders/{id}` | **Sim** | Detalha pedido com itens, cliente e operador. |
| `PUT` | `/api/orders/{id}` | **Sim** | Atualiza forma de pagamento, desconto ou notas. |
| `PATCH` | `/api/orders/{id}/confirm` | **Sim** | **Confirma venda:** valida estoque, dá baixa atômica e gera receita contábil. |
| `PATCH` | `/api/orders/{id}/cancel` | **Sim** | **Cancela venda:** estorna estoque ao inventário e anula o lançamento financeiro. |
| `DELETE` | `/api/orders/{id}` | **Sim** | Exclui pedido (apenas se já estiver cancelado). |

---

### 💼 6. Categorias Financeiras (`/api/financial-categories`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/financial-categories` | **Sim** | Lista categorias de receitas/despesas (`type=income\|expense`). |
| `POST` | `/api/financial-categories` | **Sim** | Cadastra categoria contábil. |
| `PUT` | `/api/financial-categories/{id}` | **Sim** | Atualiza categoria. |
| `DELETE` | `/api/financial-categories/{id}` | **Sim** | Exclui categoria (bloqueia com 422 se possuir transações). |

---

### 💰 7. Transações e Livro Caixa (`/api/transactions`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/transactions` | **Sim** | Lista receitas e despesas (`type`, `period`, `category_id`). |
| `POST` | `/api/transactions` | **Sim** | Registra lançamento financeiro avulso (aluguel, fornecedores, etc.). |
| `GET` | `/api/transactions/{id}` | **Sim** | Detalha lançamento. |
| `PUT` | `/api/transactions/{id}` | **Sim** | Atualiza transação avulsa. |
| `DELETE` | `/api/transactions/{id}` | **Sim** | Exclui transação avulsa (bloqueia se atrelada a pedido). |

---

### 📊 8. Dashboard Gerencial (`/api/dashboard`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/dashboard/summary` | **Sim** | 6 Métricas consolidadas: Contas a Pagar, Contas a Receber, Saldo Líquido, Vendas Faturadas, Baixo Estoque e Total de Clientes. |
| `GET` | `/api/dashboard/charts` | **Sim** | Dados estruturados para gráficos: Fluxo de Caixa mensal, Despesas por Categoria, Top 5 Produtos e Últimos Pedidos. |

---

## 🧪 Testes Automatizados

O projeto conta com suíte de testes de integração via **PHPUnit**, executados em banco SQLite isolado em memória RAM:

```bash
# Executar todos os 29 testes
php artisan test

# Executar suíte específica
php artisan test --filter=OrderApiTest
php artisan test --filter=AuthApiTest
php artisan test --filter=DashboardApiTest
```

**Status Atual:**
```text
Pass: 29 tests, 221 assertions (0 failures)
```

---

## 💡 Decisões Técnicas Notáveis

1. **Snapshot de Preço Histórico:** O preço unitário do produto é gravado no momento da venda em `order_items.unit_price`. Reajustes futuros no catálogo nunca alteram vendas antigas.
2. **Atomicidade e Concorrência:** O fechamento de pedidos e confirmação de faturamento utilizam `DB::transaction()` e `Product::lockForUpdate()` no PostgreSQL para evitar *race conditions* de estoque.
3. **Prevenção de Escalação de Privilégios:** O cadastro público de usuários força o papel `operator` no backend, ignorando qualquer tentativa de injeção de `role: admin`.
4. **Precisão Monetária:** Todos os valores financeiros são modelados em colunas `decimal(10,2)` e `decimal(12,2)`, eliminando erros de ponto flutuante.
5. **Modern Web Standards:** Adoção de `<dialog closedby="any">` para caixas modais declarativas com suporte a light dismiss e fallback progressivo para navegadores legados, formulários semânticos e tipagem estrita de inputs.