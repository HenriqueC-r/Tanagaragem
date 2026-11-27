<div align="center">

# 🚗 Tá na Garagem

### Seu próximo veículo está aqui.

Marketplace automotivo em desenvolvimento para aproximar quem procura um veículo de quem tem um bom negócio para oferecer.

<br />

![Status](https://img.shields.io/badge/status-em%20desenvolvimento-f2b632?style=for-the-badge)
![Frontend](https://img.shields.io/badge/frontend-Vue%203%20%2B%20Quasar-41B883?style=for-the-badge&logo=vuedotjs&logoColor=white)
![Backend](https://img.shields.io/badge/backend-Laravel%2013-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Database](https://img.shields.io/badge/database-MySQL%208.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)

</div>

---

## Sobre o projeto

O **Tá na Garagem** é uma plataforma de compra e venda de veículos pensada para particulares e lojas. A aplicação está sendo construída com uma interface responsiva em Vue/Quasar e uma API em Laravel.

O repositório contém duas aplicações independentes:

| Aplicação | Diretório                  | Responsabilidade                                    |
| --------- | -------------------------- | --------------------------------------------------- |
| Frontend  | [`frontend/`](./frontend/) | Vitrine, busca, filtros e experiência de navegação. |
| Backend   | [`backend/`](./backend/)   | API, autenticação, veículos, fotos e planos.        |

## O que já existe

### Experiência web

- Página inicial com busca, destaques e categorias populares.
- Listagem e página de detalhes dos veículos.
- Layout responsivo, incluindo cards horizontais em telas pequenas.
- Rotas para acesso e criação de anúncios. Essas duas telas são placeholders e ainda não oferecem os fluxos de login ou publicação.

### API

- Consulta pública, filtros e paginação de veículos.
- Consulta de veículos em destaque, categorias e planos.
- Cadastro e login com tokens Sanctum.
- Área autenticada para gerenciar veículos e fotos, incluindo publicação, pausa e marcação como vendido.
- Regras de publicação relacionadas a fotos, status do anúncio e limites do plano.

> O projeto está em desenvolvimento. A presença de endpoints no backend não significa que todos os fluxos já estejam disponíveis na interface.

## Tecnologias

**Frontend**

- Vue 3, Quasar e Vue Router
- JavaScript
- ESLint e Prettier

**Backend**

- PHP 8.3 e Laravel 13
- Laravel Sanctum
- MySQL 8.4 no ambiente Docker Compose

## Começar

### Pré-requisitos

- Git
- Docker e Docker Compose
- Node.js `22.12+`, `24` ou `26+` e npm para rodar o frontend

### 1. Obter o projeto

```bash
git clone https://github.com/HenriqueC-r/Tanagaragem.git
cd Tanagaragem
```

### 2. Configurar e iniciar a API

Crie o arquivo de ambiente local:

```bash
cp backend/.env.example backend/.env
```

Para usar o MySQL do Docker Compose, configure no `backend/.env`:

```dotenv
APP_URL=http://localhost:8081
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=tanagaragem
DB_USERNAME=tanagaragem
DB_PASSWORD=secret
```

Inicie os serviços e prepare o Laravel:

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

A API ficará disponível em `http://localhost:8081/api`. O Compose publica o MySQL na porta `3307` do host; entre containers, a aplicação usa o serviço `db` na porta `3306`.

> As credenciais declaradas no Compose são valores locais de desenvolvimento. Troque-as antes de qualquer implantação ou ambiente compartilhado.

### 3. Iniciar o frontend

Em outro terminal:

```bash
cd frontend
npm install
npm run dev
```

O Quasar exibirá no terminal o endereço local do frontend. Por padrão, as chamadas à API usam `http://localhost:8081/api`. Para apontar para outro servidor, crie `frontend/.env`:

```dotenv
VITE_API_URL=http://localhost:8081/api
```

> Rode os comandos npm dentro de `frontend/`; não há um `package.json` com script `dev` na raiz do repositório.

## Comandos úteis

Na pasta `frontend/`:

```bash
npm run dev          # inicia o servidor de desenvolvimento
npm run lint:check   # verifica formatação e lint
npm run build        # gera a versão de produção
```

Na raiz do repositório:

```bash
docker compose up -d       # inicia API e banco em segundo plano
docker compose down        # para os serviços sem apagar o banco
docker compose logs -f     # acompanha os logs
```

No backend, os testes podem ser executados dentro do container:

```bash
docker compose exec app php artisan test
```

## Rotas principais

O frontend usa URLs com hash, por exemplo `/#/veiculos/10`.

| Caminho         | Conteúdo                   |
| --------------- | -------------------------- |
| `/`             | Página inicial             |
| `/veiculos`     | Busca e listagem           |
| `/veiculos/:id` | Detalhes do veículo        |
| `/entrar`       | Tela provisória de acesso  |
| `/anunciar`     | Tela provisória de anúncio |

## Estrutura do repositório

```text
.
├── backend/       # API Laravel, banco, migrações e testes
├── docker/        # Configuração de PHP-FPM e Nginx
├── frontend/      # Aplicação Vue 3 + Quasar
└── docker-compose.yml
```

Para detalhes sobre as rotas, a implementação responsiva e os componentes do frontend, consulte [Implementações do frontend](./frontend/docs/implementacoes-frontend.md).

## Contribuição

Sugestões e melhorias são bem-vindas. Para contribuir:

1. Crie uma branch a partir da versão atual.
2. Faça mudanças focadas e inclua ou atualize testes quando necessário.
3. Execute as verificações relevantes para a aplicação alterada.
4. Abra um pull request descrevendo o problema e a solução.

## Autor

**Caio Henrique Ramos**

[GitHub](https://github.com/HenriqueC-r) · [LinkedIn](https://www.linkedin.com/in/caio-h-1b02793b0)

---

<div align="center">

Feito para ajudar você a encontrar o próximo veículo — ou o próximo dono dele.

</div>
