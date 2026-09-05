# 📖 Documentação da API — SimplesGestão

API REST construída em **Laravel 11+** com banco **PostgreSQL**, autenticação via **Laravel Sanctum (Bearer Token)** e arquitetura desacoplada para consumo frontend.

---

## 🌐 Informações Gerais

* **Base URL:** `http://localhost:8000/api`
* **Ambiente de Desenvolvimento:** `http://127.0.0.1:8000/api`
* **Formato de Dados:** `application/json` (Envio e Resposta)

### Headers Obrigatórios

| Header | Valor | Obrigatório em |
|---|---|---|
| `Accept` | `application/json` | **Todas as requisições** |
| `Content-Type` | `application/json` | Requisições `POST`, `PUT`, `PATCH` |
| `Authorization` | `Bearer {seu_token_aqui}` | **Todas as rotas protegidas** |

---

## 🚦 Códigos de Status HTTP

| Código | Descrição |
|---|---|
| `200 OK` | Requisição processada com sucesso. |
| `201 Created` | Recurso criado com sucesso. |
| `204 No Content` | Recurso excluído com sucesso (sem corpo de retorno). |
| `401 Unauthorized` | Token ausente, inválido ou expirado. |
| `404 Not Found` | Recurso não encontrado pelo identificador informado. |
| `422 Unprocessable Entity` | Erro de validação de dados ou violação de regra de negócio. |

---

## 📋 Sumário de Recursos

1. [Autenticação](#1-autenticação)
2. [Clientes (Customers)](#2-clientes-customers)
3. [Categorias de Produtos (Categories)](#3-categorias-de-produtos-categories)
4. [Produtos e Estoque (Products)](#4-produtos-e-estoque-products)
5. [Pedidos e Vendas (Orders)](#5-pedidos-e-vendas-orders)
6. [Categorias Financeiras (Financial Categories)](#6-categorias-financeiras-financial-categories)
7. [Transações Financeiras (Transactions)](#7-transações-financeiras-transactions)
8. [Dashboard e Gráficos](#8-dashboard-e-gráficos)

---

## 1. Autenticação

### `POST /auth/register`
Cadastra um novo usuário no sistema. Por segurança, o papel é sempre definido como `operator`.

* **Autenticação:** Pública
* **Body:**
```json
{
  "name": "Maria Silva",
  "email": "maria@exemplo.com",
  "password": "senhaSegura123"
}
```
* **Resposta (201 Created):**
```json
{
  "user": {
    "id": 3,
    "name": "Maria Silva",
    "email": "maria@exemplo.com",
    "role": "operator",
    "created_at": "2026-09-05T14:00:00.000000Z"
  },
  "token": "1|qW3...plainTextToken"
}
```

---

### `POST /auth/login`
Autentica o usuário com e-mail e senha e retorna um token de acesso Sanctum.

* **Autenticação:** Pública
* **Body:**
```json
{
  "email": "admin@simplesgestao.com",
  "password": "password"
}
```
* **Resposta (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "Admin SimplesGestão",
    "email": "admin@simplesgestao.com",
    "role": "admin",
    "created_at": "2026-09-05T12:00:00.000000Z"
  },
  "token": "2|kJ8...plainTextToken"
}
```

---

### `POST /auth/logout`
Revoga o token atual do usuário no banco de dados.

* **Autenticação:** Bearer Token
* **Resposta (200 OK):**
```json
{
  "message": "Logout realizado com sucesso."
}
```

---

### `GET /auth/me`
Retorna os dados do usuário autenticado na sessão.

* **Autenticação:** Bearer Token
* **Resposta (200 OK):**
```json
{
  "data": {
    "id": 1,
    "name": "Admin SimplesGestão",
    "email": "admin@simplesgestao.com",
    "role": "admin",
    "created_at": "2026-09-05T12:00:00.000000Z"
  }
}
```

---

## 2. Clientes (Customers)

### `GET /customers`
Lista clientes com paginação e suporte a busca.

* **Autenticação:** Bearer Token
* **Query Parameters:**
  * `search` (string, opcional): filtra por nome, e-mail ou CPF/CNPJ.
  * `per_page` (int, default: 15): quantidade por página.
  * `page` (int, default: 1): número da página.
* **Resposta (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "João da Silva",
      "email": "joao@email.com",
      "phone": "(11) 98888-7777",
      "cpf_cnpj": "123.456.789-00",
      "address": "Av. Paulista, 1000 - Bela Vista, SP",
      "notes": "Cliente preferencial",
      "created_at": "2026-09-05T12:00:00.000000Z",
      "updated_at": "2026-09-05T12:00:00.000000Z"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "total": 1, "per_page": 15 }
}
```

---

### `POST /customers`
Cadastra um novo cliente.

* **Autenticação:** Bearer Token
* **Body:**
```json
{
  "name": "Padaria Estrela Ltda",
  "email": "contato@padariaestrela.com.br",
  "phone": "(11) 3333-4444",
  "cpf_cnpj": "12.345.678/0001-90",
  "address": "Rua das Flores, 45",
  "notes": "Entrega às segundas e quartas"
}
```
* **Resposta (201 Created):** Retorna o objeto do cliente criado.

---

### `GET /customers/{id}`
Detalhes de um cliente específico.

---

### `PUT /customers/{id}`
Atualiza dados do cliente. Validação de unicidade de e-mail/CPF ignora o ID do próprio cliente.

---

### `DELETE /customers/{id}`
Exclui um cliente. Se o cliente tiver pedidos vinculados, os pedidos têm `customer_id` definido para `null` preservando o histórico de vendas.

* **Resposta (204 No Content)**

---

## 3. Categorias de Produtos (Categories)

### `GET /categories`
Lista categorias de produtos com contagem de produtos associados (`products_count`).

* **Query Parameters:**
  * `all=1`: retorna todas as categorias sem paginação (ideal para selects do frontend).
  * `search`: busca por nome.

---

### `POST /categories`
Cria uma nova categoria.

* **Body:**
```json
{
  "name": "Bebidas",
  "description": "Refrigerantes, sucos e águas"
}
```

---

### `PUT /categories/{id}`
Atualiza uma categoria existente.

---

### `DELETE /categories/{id}`
Exclui uma categoria.
* **Regra de Negócio:** Se houver produtos vinculados à categoria, a API recusa a exclusão com `HTTP 422`:
```json
{
  "message": "Não é possível excluir categoria com produtos vinculados."
}
```

---

## 4. Produtos e Estoque (Products)

### `GET /products`
Lista produtos com categoria associada via eager loading.

* **Query Parameters:**
  * `search`: busca por nome ou SKU.
  * `category_id`: filtra por ID da categoria.
  * `is_active` (boolean): `true` para ativos, `false` para inativos.
  * `low_stock=1`: filtra apenas produtos em ponto de reposição (`stock_quantity <= min_stock`).
* **Resposta (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "category": {
        "id": 1,
        "name": "Bebidas"
      },
      "name": "Coca-Cola 350ml",
      "description": "Lata 350ml refrigerada",
      "sku": "BEB-0001",
      "price": 5.50,
      "cost_price": 3.30,
      "stock_quantity": 50,
      "min_stock": 5,
      "is_low_stock": false,
      "is_active": true,
      "created_at": "2026-09-05T12:00:00.000000Z"
    }
  ]
}
```

---

### `GET /products/low-stock`
Endpoint especializado que lista **apenas produtos com estoque crítico** (`stock_quantity <= min_stock`), ordenados pelo menor saldo.

---

### `POST /products`
Cria um novo produto.

* **Body:**
```json
{
  "category_id": 1,
  "name": "Café Especial 250g",
  "sku": "ALM-0010",
  "price": 28.50,
  "cost_price": 14.00,
  "stock_quantity": 20,
  "min_stock": 5,
  "is_active": true
}
```

---

### `PUT /products/{id}`
Atualiza dados do produto.

---

### `DELETE /products/{id}`
* **Regra de Negócio:** Se o produto já foi vendido em algum pedido anterior, a API bloqueia a exclusão com `HTTP 422`:
```json
{
  "message": "Não é possível excluir produto vinculado a vendas anteriores. Considere desativá-lo."
}
```

---

## 5. Pedidos e Vendas (Orders)

### `GET /orders`
Lista pedidos com cliente, vendedor (`user`) e contagem de itens.

* **Query Parameters:**
  * `status`: `pending`, `confirmed`, `delivered`, `cancelled`.
  * `customer_id`: filtra por cliente.
  * `search`: busca por número do pedido (`order_number`) ou nome do cliente.
  * `start_date` / `end_date`: filtro por período (`YYYY-MM-DD`).

---

### `POST /orders`
Registra uma nova venda. Executado dentro de transação de banco com bloqueio pessimista (`lockForUpdate`) para concorrência segura.

* **Regras Automáticas:**
  * Se `unit_price` não for enviado, o sistema congela o preço atual do produto (`price snapshot`).
  * Se `status: "confirmed"` for enviado:
    1. Verifica estoque de todos os itens.
    2. Decrementa o estoque dos produtos no banco.
    3. Gera automaticamente lançamento contábil em `transactions` (tipo `income`).
* **Body:**
```json
{
  "customer_id": 1,
  "payment_method": "pix",
  "discount": 5.00,
  "notes": "Entrega rápida",
  "status": "pending",
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "unit_price": 5.50
    },
    {
      "product_id": 4,
      "quantity": 1
    }
  ]
}
```
* **Resposta (201 Created):** Retorna o pedido completo com subtotais, totais calculados e itens detalhados.

---

### `PATCH /orders/{id}/confirm`
Confirma um pedido que estava `pending`.

* **Ações Automáticas:**
  1. Valida se o estoque atual é suficiente para cada produto.
  2. Decrementa as quantidades no estoque.
  3. Atualiza o status para `confirmed`.
  4. Cria lançamento financeiro de receita em `transactions` com o valor do pedido.
* **Erro de Falta de Estoque (422):**
```json
{
  "message": "Estoque insuficiente para Coca-Cola 350ml. Disponível: 1, Solicitado: 5",
  "errors": { "items": ["..."] }
}
```

---

### `PATCH /orders/{id}/cancel`
Cancela um pedido.

* **Ações Automáticas de Rollback:**
  * Se o pedido estava `confirmed` ou `delivered`, **estorna automaticamente o estoque** dos produtos (`increment('stock_quantity')`) e **remove a transação financeira de receita** correspondente.

---

### `DELETE /orders/{id}`
* **Regra de Negócio:** Apenas pedidos com status `cancelled` podem ser excluídos. Pedidos ativos exigem cancelamento prévio para garantir o estorno de estoque e financeiro.

---

## 6. Categorias Financeiras (Financial Categories)

Classifica as receitas e despesas da empresa (ex: Vendas, Aluguel, Fornecedores, Salários).

* `GET /financial-categories`: Lista categorias (suporta `type=income|expense` e `all=1`).
* `POST /financial-categories`: Cadastra categoria.
* `PUT /financial-categories/{id}`: Atualiza categoria.
* `DELETE /financial-categories/{id}`: Exclui categoria (bloqueia com 422 se possuir lançamentos vinculados).

---

## 7. Transações Financeiras (Transactions)

Gerencia todas as entradas e saídas do negócio (vendas automáticas e despesas manuais).

### `GET /transactions`
Lista transações financeiras.

* **Query Parameters:**
  * `type`: `income` (entradas) ou `expense` (saídas).
  * `financial_category_id`: filtra por categoria.
  * `start_date` / `end_date`: filtro por data.
  * `search`: busca na descrição.

---

### `POST /transactions`
Lança uma receita ou despesa avulsa (ex: pagamento de conta, aluguel, aporte).

* **Body:**
```json
{
  "financial_category_id": 3,
  "type": "expense",
  "amount": 2500.00,
  "description": "Aluguel do Galpão - Setembro",
  "transaction_date": "2026-09-05"
}
```

---

### `DELETE /transactions/{id}`
* **Regra de Segurança:** Transações geradas automaticamente por pedidos de venda **não podem ser excluídas manualmente**. O cancelamento deve ser feito via `PATCH /orders/{id}/cancel`.

---

## 8. Dashboard e Gráficos

Endpoints consumidos pelos cards métricos e gráficos do frontend (Recharts).

### `GET /dashboard/summary`
Retorna as métricas consolidadas do negócio no período selecionado.

* **Query Parameters:**
  * `period`: `this_month` (padrão), `last_month`, `last_30_days`, `this_year`, `today`.
  * `start_date` / `end_date`: período customizado (`YYYY-MM-DD`).
* **Resposta (200 OK):**
```json
{
  "period": {
    "start_date": "2026-09-01",
    "end_date": "2026-09-30"
  },
  "metrics": {
    "total_income": 45890.50,
    "total_expense": 21340.00,
    "net_balance": 24550.50,
    "confirmed_orders_count": 84,
    "pending_orders_count": 5,
    "average_ticket": 546.32,
    "low_stock_count": 3,
    "total_customers": 142
  }
}
```

---

### `GET /dashboard/charts`
Retorna dados estruturados e prontos para renderização nos gráficos da interface.

* **Query Parameters:**
  * `months` (int, default: 6): quantidade de meses no histórico do fluxo de caixa.
* **Resposta (200 OK):**
```json
{
  "cash_flow": [
    {
      "month_key": "2026-04",
      "label": "abr/26",
      "income": 38200.00,
      "expense": 19400.00,
      "balance": 18800.00
    },
    {
      "month_key": "2026-05",
      "label": "mai/26",
      "income": 42100.00,
      "expense": 22000.00,
      "balance": 20100.00
    }
  ],
  "expenses_by_category": [
    {
      "category_id": 3,
      "name": "Aluguel",
      "amount": 3500.00,
      "percentage": 28.5
    },
    {
      "category_id": 4,
      "name": "Salários",
      "amount": 6200.00,
      "percentage": 50.4
    }
  ],
  "top_selling_products": [
    {
      "id": 1,
      "name": "Coca-Cola 350ml",
      "sku": "BEB-0001",
      "total_quantity": 210,
      "total_revenue": 1155.00
    }
  ],
  "recent_orders": [
    {
      "id": 20,
      "order_number": "PED-20260905-020",
      "customer_name": "João da Silva",
      "status": "confirmed",
      "total": 128.50,
      "created_at": "2026-09-05T13:45:00.000000Z"
    }
  ]
}
```
