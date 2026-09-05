# ⚙️ SimplesGestão — Backend API (Laravel 11)

API RESTful em Laravel 11 com PostgreSQL e autenticação via Sanctum.

---

## 🚀 Como Iniciar o Backend

```bash
# 1. Subir banco de dados PostgreSQL via Docker
docker compose up -d

# 2. Instalar dependências
composer install

# 3. Executar migrations e popular dados
php artisan migrate:fresh --seed

# 4. Iniciar servidor de desenvolvimento
php artisan serve
```

A API estará acessível em: `http://localhost:8000/api`

---

## 📑 Documentação Interativa (Swagger / OpenAPI)

Com o servidor rodando, acesse:
👉 **`http://localhost:8000/docs/api`**

---

## 🧪 Testes Automatizados

```bash
# Executar todos os testes
php artisan test
```

Para a documentação completa de endpoints e regras de negócio, consulte o [README principal do projeto](../README.md) ou o documento de referência [`API_DOCUMENTATION.md`](./API_DOCUMENTATION.md).
