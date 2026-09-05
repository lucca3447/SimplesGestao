# 🚀 SimplesGestão — Sistema de Gestão para Pequenos Negócios

> **Projeto Fullstack Desacoplado** com API REST em **Laravel 11**, banco de dados **PostgreSQL (Docker)**, autenticação **Laravel Sanctum** e frontend em **React (Vite + Tailwind CSS)**.  
> Desenvolvido com foco nas necessidades reais de gestão para PMEs (clientes, catálogo, controle de estoque crítico, pedidos com congelamento histórico de preços e fluxo de caixa consolidado).

---

## 🏗️ Arquitetura do Sistema

O projeto adota arquitetura desacoplada (Headless / API-First):

```text
┌─────────────────────────────────────────────────────────┐
│                 Frontend (React + Vite)                 │
│      Tailwind CSS · Recharts · Axios Interceptors       │
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
* **PostgreSQL 16** via Docker Compose
* **Laravel Sanctum:** Autenticação stateless via Bearer Tokens
* **Eloquent ORM:** Relacionamentos `hasMany` / `belongsTo`, casts nativos e locks pessimistas
* **Dedoc Scramble:** Documentação interativa OpenAPI / Swagger UI
* **PHPUnit:** 29 testes de integração automatizados com 100% de cobertura dos fluxos críticos

### Frontend (Em desenvolvimento)
* **React 18** com **Vite**
* **Tailwind CSS:** Layout inspirado no design system da Conexa (sidebar verde-petróleo `#0f2d2e`, botões em laranja `#f97316`)
* **Recharts:** Visualização de fluxo de caixa (entradas × saídas) e gráficos de distribuição

---

## ⚡ Como Rodar o Projeto Localmente

### Pré-requisitos
* Docker e Docker Compose instalados
* PHP 8.2+ e Composer
* Node.js 18+

### 1. Iniciar o Banco de Dados (PostgreSQL)
Na pasta do backend, inicie o container Docker:
```bash
cd simples-gestao-api
docker compose up -d
```
*(O banco roda isolado na porta `5433` com volume persistente).*

### 2. Configurar o Ambiente e Dependências
```bash
composer install
cp .env.example .env # se necessário
php artisan key:generate
```

### 3. Rodar as Migrations e Popular com Dados de Teste
```bash
php artisan migrate:fresh --seed
```
O seeder criará automaticamente:
* 2 usuários (admin e operador)
* 15 clientes com CPF/CNPJ válidos
* 14 produtos com estoque e preços reais
* 20 pedidos com histórico de vendas
* Mais de 30 lançamentos contábeis de receitas e despesas

### 4. Iniciar a API
```bash
php artisan serve
```
A API estará disponível em: `http://localhost:8000`

---

## 🔑 Credenciais Padrão de Teste

| E-mail | Senha | Papel (Role) |
|---|---|---|
| `admin@simplesgestao.com` | `password` | Administrador |
| `maria@simplesgestao.com` | `password` | Operador |

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
| `DELETE` | `/api/customers/{id}` | **Sim** | Exclui cliente (preserva pedidos históricos desvinculando ID). |

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
| `GET` | `/api/products` | **Sim** | Lista produtos com categoria (`search`, `category_id`, `is_active`). |
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
| `GET` | `/api/orders/{id}` | **Sim** | Detalha pedido com itens, cliente e vendedor. |
| `PUT` | `/api/orders/{id}` | **Sim** | Atualiza forma de pagamento, desconto ou notas. |
| `PATCH` | `/api/orders/{id}/confirm` | **Sim** | **Confirma venda:** valida estoque, baixa saldo e gera receita contábil. |
| `PATCH` | `/api/orders/{id}/cancel` | **Sim** | **Cancela venda:** estorna estoque e remove a receita financeira. |
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
| `POST` | `/api/transactions` | **Sim** | Registra lançamento financeiro avulso (aluguel, contas, etc.). |
| `GET` | `/api/transactions/{id}` | **Sim** | Detalha lançamento. |
| `PUT` | `/api/transactions/{id}` | **Sim** | Atualiza transação avulsa. |
| `DELETE` | `/api/transactions/{id}` | **Sim** | Exclui transação avulsa (bloqueia se atrelada a pedido). |

---

### 📊 8. Dashboard Gerencial (`/api/dashboard`)

| Método | Endpoint | Protegido? | Descrição |
|---|---|---|---|
| `GET` | `/api/dashboard/summary` | **Sim** | Métricas consolidadas: Receita, Despesas, Saldo Líquido, Ticket Médio e Alertas. |
| `GET` | `/api/dashboard/charts` | **Sim** | Dados estruturados para Recharts: Fluxo de Caixa mensal, Despesas por Categoria e Top 5 Produtos. |

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
2. **Atomicidade e Concorrência:** O fechamento de pedidos utiliza `DB::transaction()` e `Product::lockForUpdate()` no PostgreSQL para evitar *race conditions* de estoque.
3. **Prevenção de Escalação de Privilégios:** O cadastro público de usuários força o papel `operator` no backend, ignorando qualquer tentativa de injeção de `role: admin`.
4. **Precisão Monetária:** Todos os valores financeiros são modelados em colunas `decimal(10,2)` e `decimal(12,2)`, eliminando erros de ponto flutuante.