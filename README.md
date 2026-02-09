# OnFly – Desafio Full Stack (Pedidos de Viagem Corporativa)

Aplicação Full Stack para gestão de pedidos de viagem corporativa, composta por:
- **API REST** em **Laravel** (autenticação via token com **Laravel Sanctum**)
- **Interface Web** em **Vue.js (Quasar Framework)**

Resumo rápido:
- Login por e-mail e senha
- Controle de acesso por perfil (Admin/User)
- Filtros por status, destino e datas
- Aprovação/cancelamento com notificações

O sistema permite:
- Criar pedidos de viagem
- Listar e filtrar pedidos
- Consultar detalhes por ID
- Atualizar status (apenas Admin)
- Enviar notificação ao aprovar/cancelar (via canal `database`)
- Testes automatizados no backend

---

## Estrutura do projeto

```
/
  backend/   -> Laravel API (Sail)
  frontend/  -> Quasar SPA (Vite)
```

---

## Requisitos

- Docker
- PHP 8.3
- Node.js 20
- Git

> O backend roda via **Laravel Sail** (Docker).  
> O frontend pode rodar via **Docker** ou **Quasar dev server** local.

---

## Variáveis de ambiente

Backend:
- `.env` a partir de `.env.example`
- Ajuste `APP_URL` se necessário
- `CORS_ALLOWED_ORIGINS` para liberar o frontend (ex.: `http://localhost:9000`)

Frontend:
- `.env` a partir de `.env.example`
- `VITE_API_URL` deve apontar para o backend (`http://localhost` no setup padrão)

---

## Como executar o projeto

### Opção A) Tudo via Docker (backend + frontend)

Entre na pasta do backend:

```bash
cd backend
```

Copie o `.env`:

```bash
cp .env.example .env
```

Garanta os diretórios de cache/storage (evita erro "Please provide a valid cache path"):

```bash
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Configure o `.env` do frontend:

```bash
cp ../frontend/.env.example ../frontend/.env
```

Instale as dependencias PHP (necessario para gerar `vendor/` para poder utilizar o Sail):

```bash
composer install
```

Suba os containers:

```bash
./vendor/bin/sail up -d --build
```

Gere a `APP_KEY` e rode migrations/seed:

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

Acesso:
- API (Laravel): `http://localhost`
- Frontend (Quasar): `http://localhost:9000`
- phpMyAdmin: `http://localhost:8081`
- Swagger (Docs): `http://localhost/api/documentation`

> O container do frontend executa `npm install` na primeira subida.

### 1) Backend (Laravel + Sail)

Entre na pasta:

```bash
cd backend
```

Suba os containers:

```bash
./vendor/bin/sail up --build
```

> Caso `sail` não esteja acessível, use sempre `./vendor/bin/sail ...`

Instale dependências PHP (uma vez):

```bash
composer install
```

#### 1.1) Configuração do ambiente

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

Garanta os diretórios de cache/storage (evita erro "Please provide a valid cache path"):

```bash
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Gere a `APP_KEY`:

```bash
./vendor/bin/sail artisan key:generate
```

Suba migrations e seed:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

#### 1.2) Acesso aos serviços

- API (Laravel): `http://localhost`
- Frontend (Quasar, via Docker): `http://localhost:9000`
- phpMyAdmin: `http://localhost:8081`
- Swagger (Docs): `http://localhost/api/documentation`

Credenciais padrão do banco (Docker):
- Host: `mysql`
- Database: `onfly`
- User: `onfly`
- Password: `onfly123`

---

### 2) Frontend (Quasar, local - opcional)

Em outro terminal, entre na pasta:

```bash
cd frontend
```

Instale dependências:

```bash
npm install
```

Crie o `.env` a partir do exemplo:

```bash
cp .env.example .env
```

Suba o front:

```bash
npm run dev
```

Acesse:
- Frontend (Quasar): `http://localhost:9000`

> Observação: se você subiu o backend via `sail up -d`, o **frontend já sobe no Docker**.  
> Use o modo local apenas se quiser rodar o front fora do container.

---

## Seeds (dados iniciais)

O seed cria:
- Usuários de teste (admin + usuários comuns)
- Aeroportos base
- 15 pedidos de viagem com status variados

---

## Usuários de teste

O projeto cria usuários via seed:

### Admin
- Email: `admin@onfly.local`
- Senha: `password`
- Permissões: pode aprovar/cancelar e visualizar todos os pedidos

### User
- Email: `user@onfly.local`
- Senha: `password`
- Permissões: cria/visualiza/lista apenas os próprios pedidos

---

## Autenticação

A API usa **Sanctum** com token Bearer.  
O frontend salva o token no `localStorage` e envia no header `Authorization`.

---

## Coleção Insomnia

Para testar a API com Insomnia, importe o arquivo:

`backend/onfly-insomnia.json`

---

## Regras de negócio implementadas

### Visibilidade dos pedidos
- Usuário comum só vê **os próprios pedidos**
- Admin pode ver **todos os pedidos**

### Atualização de status
- Apenas Admin pode alterar status para `approved` ou `canceled`
- Regra: **não é permitido cancelar um pedido que já esteja aprovado**
  - Se tentar, a API responde `422` com erro de validação

### Notificação
- Sempre que um pedido é `approved` ou `canceled`, o solicitante recebe uma notificação (`database`).

---

## Endpoints principais (API)

Base URL:
- `http://localhost/api`

### Autenticação (Sanctum)
- `POST /login`
- `GET /me` (autenticado)
- `POST /logout` (autenticado)

### Pedidos de viagem
- `GET /travel-orders` (autenticado)
  - Filtros:
    - `status` = `requested|approved|canceled`
    - `destination` (contém)
    - `travel_from` / `travel_to` (faixa das datas de viagem)
    - `created_from` / `created_to` (faixa de criação)
- `POST /travel-orders` (autenticado)
- `GET /travel-orders/{id}` (autenticado + policy)
- `PATCH /travel-orders/{id}/status` (autenticado + admin)
  - Body: `{ "status": "approved" }` ou `{ "status": "canceled" }`

---

## Exemplos de uso (curl)

### Login
```bash
curl -X POST http://localhost/api/login   -H "Content-Type: application/json"   -H "Accept: application/json"   -d '{"email":"admin@onfly.local","password":"password"}'
```

### Criar pedido
```bash
curl -X POST http://localhost/api/travel-orders   -H "Authorization: Bearer SEU_TOKEN"   -H "Content-Type: application/json"   -H "Accept: application/json"   -d '{"requester_name":"Lucas","destination":"São Paulo","departure_date":"2026-03-01","return_date":"2026-03-05"}'
```

### Listar com filtros
```bash
curl "http://localhost/api/travel-orders?status=requested&destination=São&travel_from=2026-03-01&travel_to=2026-03-10"   -H "Authorization: Bearer SEU_TOKEN"   -H "Accept: application/json"
```

### Aprovar (Admin)
```bash
curl -X PATCH http://localhost/api/travel-orders/1/status   -H "Authorization: Bearer SEU_TOKEN"   -H "Content-Type: application/json"   -H "Accept: application/json"   -d '{"status":"approved"}'
```

---

## Testes automatizados (Backend)

Os testes cobrem:
- Permissões de acesso (usuário não vê pedido de outros)
- Admin-only para atualização de status
- Regra de negócio de cancelamento
- Notificação enviada ao aprovar

Executar testes:

```bash
cd backend
./vendor/bin/sail test
```

---

## Testes (Frontend)

```bash
cd frontend
npm run lint
```

---

## UI/UX (Frontend)

- Login com armazenamento de token (LocalStorage)
- Rotas protegidas via router guard
- Dashboard com tabela (`QTable`)
- Filtros por status/destino/período
- Criação de pedido via modal (`QDialog`)
- Ações de Admin (aprovar/cancelar) com confirmação
- Feedback ao usuário com `Notify` e loading states

---

## Observações importantes

- O frontend utiliza interceptor do Axios para anexar automaticamente:
  - `Authorization: Bearer <token>`
  - `Accept: application/json`
- O backend utiliza `Laravel Sanctum` com token pessoal.
- A notificação foi implementada via canal `database` por simplicidade e testabilidade (evita dependência de SMTP).

---

## Troubleshooting

### Porta 3306 ocupada
O projeto **não expõe** `3306` no host para evitar conflito.  
O MySQL é acessível internamente pelos containers (`DB_HOST=mysql`).

### CORS
Caso o navegador bloqueie chamadas, ajuste `CORS_ALLOWED_ORIGINS` no `.env` ou edite `config/cors.php`.

### Recriar containers do backend
```bash
cd backend
./vendor/bin/sail down -v
./vendor/bin/sail up -d --build
```

---
