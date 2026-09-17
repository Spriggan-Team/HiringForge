# Hiring Forge

> **Hiring Forge** is a recruitment platform designed to streamline the hiring workflow by bringing recruiters, candidates, job offers, semantic CV matching, and recruitment data together in one application.

---

## ✨ Overview

Hiring Forge aims to make recruitment more efficient by centralizing the main stages of the hiring process.

The application combines a modern web interface with a Symfony/PHP backend, relational data storage, vector search, and AI-powered services for semantic CV matching.

### Key capabilities

- 👤 Candidate and recruiter workflows
- 📄 Recruitment and job-offer management
- 🔎 Semantic search and CV matching
- 🧠 AI-assisted matching using embeddings
- 🗄️ Relational data storage with MariaDB
- 🧬 Vector data storage with Qdrant
- 🌍 Multi-environment configuration (`dev`, `test`, `prod`)
- 🐳 Docker-based development environment
- 🌐 React + TypeScript frontend

---

## 🏗️ Architecture

Hiring Forge is organized into three main layers.

```text
┌──────────────────────────────────────────────────────────┐
│                    Presentation Layer                    │
│                 React + TypeScript                       │
│      Recruiters • Candidates • Visitors                  │
└───────────────────────────┬──────────────────────────────┘
                            │ HTTP / API
                            ▼
┌──────────────────────────────────────────────────────────┐
│                    Application Layer                     │
│               Symfony + PHP + Doctrine                   │
│             Business logic • Authentication             │
└───────────────────────────┬──────────────────────────────┘
                            │
                 ┌──────────┴──────────┐
                 ▼                     ▼
┌──────────────────────────┐  ┌────────────────────────────┐
│        MariaDB           │  │           Qdrant           │
│   Relational database    │  │ Vector database / Search   │
└──────────────────────────┘  └────────────────────────────┘
                            │
                            ▼
                  ┌─────────────────────┐
                  │   AI / External     │
                  │      Services       │
                  └─────────────────────┘
```

### Frontend

The presentation layer provides the user interface for recruiters, candidates, and visitors.

Main technologies:

- [React](https://react.dev/)
- [TypeScript](https://www.typescriptlang.org/)
- React i18n
- [React Leaflet](https://react-leaflet.js.org/)
- [Tiptap](https://tiptap.dev/)

### Backend

The application layer contains the business logic and manages the different domain entities.

Main technologies:

- [Symfony](https://symfony.com/)
- [PHP](https://www.php.net/)
- [Doctrine](https://www.doctrine-project.org/)
- JWT authentication
- Other Symfony-compatible packages

### Data & infrastructure

- [MariaDB](https://mariadb.org/) — relational database
- [Qdrant](https://qdrant.tech/) — vector database and semantic search

### AI services

Hiring Forge also relies on AI services for semantic CV matching.

The project currently uses:

- [Ollama](https://ollama.com/) for local model execution
- [BGE-M3](https://ollama.com/library/bge-m3) for embeddings
- A Mistral model through Ollama for conversational/AI operations

The `bge-m3:567m` embedding variant is available in the Ollama model library.

---

## 🚀 Getting Started

Hiring Forge supports three runtime environments:

- `dev`
- `test`
- `prod`

### Environment selection

The runtime environment is controlled through `APP_ENV` in:

```text
backend/.env
```

Example:

```dotenv
APP_ENV=dev
```

### Local environment files

Create the appropriate local configuration file in the project root:

```text
.env.dev.local
.env.test.local
.env.prod.local
```

These files should contain machine-specific configuration such as database credentials, service URLs, mail credentials, and other secrets.

> **Security:** Never commit passwords, API keys, tokens, or other secrets to Git. Keep them in `.local` environment files or another secret-management solution.

---

# 🐳 Option 1 — Run with Docker

Docker is the recommended way to start the complete technical environment because the project already contains the required container configuration.

### Requirements

Install:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/)

Docker Desktop includes Docker Compose on Windows and macOS.

### Start the application

From the project root:

```bash
docker compose up
```

To run the containers in the background:

```bash
docker compose up -d
```

To stop them:

```bash
docker compose down
```

The application will start according to the configuration defined by the selected environment.

> **Tip:** If the project contains multiple Compose files or profiles, use the commands defined by the repository's Docker configuration.

---

# 🛠️ Option 2 — Run without Docker

Running the project without Docker requires installing and configuring each dependency manually.

## 1. PHP

The project currently targets PHP 8.5.x during development.

Official resources:

- [PHP downloads](https://www.php.net/downloads.php)
- [PHP documentation](https://www.php.net/docs.php)

> The repository previously referenced PHP `8.5.3` specifically. Keep that exact version only if the project's dependency constraints require it; otherwise use the supported PHP 8.5 release compatible with the project.

Check your installation:

```bash
php --version
```

---

## 2. Composer

Composer manages the PHP dependencies used by the Symfony backend.

Install it from:

- [Composer — installation](https://getcomposer.org/download/)
- [Composer documentation](https://getcomposer.org/doc/)

Verify the installation:

```bash
composer --version
```

---

## 3. MariaDB

The project currently targets MariaDB `10.11.6` during development.

Official resources:

- [MariaDB — installation and deployment](https://mariadb.com/docs/server/server-management/install-and-upgrade-mariadb)
- [MariaDB quickstart guides](https://mariadb.com/docs/server/mariadb-quickstart-guides)
- [MariaDB 10.11 release notes](https://mariadb.com/docs/release-notes/community-server/10.11)

Check your installation:

```bash
mariadb --version
```

Configure the database connection through:

```dotenv
DATABASE_URL=...
```

---

## 4. Qdrant

Qdrant is used as the project's vector database for semantic search and CV matching.

The project currently recommends:

```text
Qdrant 1.12.0
```

Official resources:

- [Qdrant documentation](https://qdrant.tech/documentation/)
- [Qdrant installation guide](https://qdrant.tech/documentation/installation/)

Check that Qdrant is reachable before starting the semantic-search features.

---

## 5. Ollama

Ollama is used to run AI models locally.

Install Ollama from:

- [Ollama](https://ollama.com/)
- [Ollama downloads](https://ollama.com/download)

Then install the embedding model used by the project:

```bash
ollama pull bge-m3:567m
```

The model is documented in the official Ollama library:

- [BGE-M3 on Ollama](https://ollama.com/library/bge-m3)

For the Mistral model, the project can use a model exposing Ollama's `/api/chat` endpoint.

For example:

```bash
ollama pull mistral
```

Official model page:

- [Mistral on Ollama](https://ollama.com/library/mistral)

Verify that Ollama is running:

```bash
ollama list
```

---

## 6. Node.js

Node.js is required to run the React frontend.

The project currently targets:

```text
Node.js 22.19.0
```

Official resources:

- [Node.js downloads](https://nodejs.org/en/download/)
- [Node.js v22.19.0 archive](https://nodejs.org/en/download/archive/v22.19.0)

Check your installation:

```bash
node --version
npm --version
```

---

# ⚙️ Application Setup

Once the required infrastructure is installed, configure and start the backend and frontend.

## Backend

Navigate to the backend:

```bash
cd backend
```

Install dependencies:

```bash
composer install
```

Start the development server:

```bash
php -S 127.0.0.1:8000 -t public
```

Symfony's official documentation is available at:

- [Symfony Documentation](https://symfony.com/doc)

---

## Frontend

Open another terminal and navigate to the frontend directory.

Install dependencies:

```bash
npm install
```

Start the development server:

```bash
npm run dev
```

The frontend will then run in development mode.

---

# 🗄️ Database Initialization

After starting the backend, initialize the database schema.

## Development

```bash
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
```

## Test

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:update --force --env=test
```

> These commands create/update the schema using the database configuration of the selected environment.

---

# 🌱 Default Data

Hiring Forge requires default data for languages, skills, and contract types.

## Skills

The project provides SQL data under:

```text
backend/src/Infrastructure/docker/db/init
```

Import the provided skills backup into the database used by your current environment.

Example:

```bash
mysql -u root -p ats_recruitment < "path_to/HiringForge/backend/src/Infrastructure/docker/db/init/skills_backup.sql"
```

> The project currently does not provide a single command that automatically imports this SQL backup.

## Languages

Seed the default language data:

```bash
php bin/console app:seed-languages
```

## Contract types

Seed the default contract types:

```bash
php bin/console app:seed:contract-types
```

At the end of the initialization, the database should contain:

- Languages
- Skills
- Contract types

---

# 🧬 Qdrant Initialization

There are two supported approaches depending on the Qdrant version.

## Recommended — Qdrant 1.12.0

For Qdrant `1.12.0`, the project provides a prepared snapshot:

```text
skills_backup_qdrant_v1.12.0.snapshot
```

Download it from the project-maintained Google Drive folder:

https://drive.google.com/drive/folders/1B0x17xCNEDab6Ge6HuxnoYHq-77ETbyrX

Then, with the backend running, execute:

```bash
php bin/console app:init-qdrant-data --path="real_path_to_the_downloaded_snapshot"
```

The snapshot is intended to be synchronized with the corresponding default database data.

## Other Qdrant versions

If you use a Qdrant version other than `1.12.0`, do not import the default skills SQL backup into MariaDB.

Instead, generate the skills data from the O*NET and ESCO repositories:

```bash
php -d memory_limit=-1 bin/console app:seed-skills -s onet
php -d memory_limit=-1 bin/console app:seed-skills -x -s esco
php bin/console cache:clear
```

The `-x` option enables vector/semantic search.

---

# 🔐 Environment Variables

The application requires configuration for the following services:

```dotenv
# Application
APP_ENV=dev

# Database
DATABASE_URL=...

# Qdrant
QDRANT_URL=...

# Ollama
OLLAMA_URL=...
OLLAMA_EMBEDDING_MODEL=bge-m3:567m

# Mail
MAIL_USER_NAME=...
MAIL_USER_PASSWORD=...
```

> The exact variable names and values must match the environment configuration already used by the project.

Do not commit local credentials to the repository.

---

# 🧪 Recommended Startup Checklist

Before starting the application, verify:

- [ ] Docker is running, if using Docker
- [ ] MariaDB is running
- [ ] Qdrant is running
- [ ] Ollama is running
- [ ] The required Ollama models are installed
- [ ] `.env.*.local` is configured
- [ ] `DATABASE_URL` is valid
- [ ] Qdrant URL is valid
- [ ] Ollama URL is valid
- [ ] Mail credentials are configured
- [ ] PHP dependencies are installed
- [ ] Node.js dependencies are installed
- [ ] Database schema has been initialized
- [ ] Default languages, skills, and contract types are available
- [ ] Qdrant contains the required skill vectors

---

# 🧩 Troubleshooting

### Composer dependency issues

Run:

```bash
composer diagnose
```

Then retry:

```bash
composer install
```

See the [Composer troubleshooting guide](https://getcomposer.org/doc/articles/troubleshooting.md).

### Qdrant connection issues

Check that Qdrant is running and that the configured URL matches the service exposed by your environment.

The default HTTP API uses port `6333`.

See the [Qdrant installation documentation](https://qdrant.tech/documentation/installation/).

### Ollama issues

Check installed models:

```bash
ollama list
```

Check the embedding model:

```bash
ollama run bge-m3:567m
```

Check the Mistral model:

```bash
ollama run mistral
```

---

# 🚢 Deployment

Deployment is currently planned for an on-site environment and is not yet completely finalized.

Once the deployment is complete, the application is expected to be accessible at:

**https://vortyx.com/hiring-forge**

---

## 📚 Official Documentation

| Technology | Documentation |
|---|---|
| React | https://react.dev/ |
| TypeScript | https://www.typescriptlang.org/ |
| Symfony | https://symfony.com/doc |
| PHP | https://www.php.net/docs.php |
| Doctrine | https://www.doctrine-project.org/ |
| Composer | https://getcomposer.org/doc/ |
| MariaDB | https://mariadb.com/docs/ |
| Qdrant | https://qdrant.tech/documentation/ |
| Ollama | https://ollama.com/ |
| BGE-M3 | https://ollama.com/library/bge-m3 |
| Mistral | https://ollama.com/library/mistral |
| Node.js | https://nodejs.org/en/docs/ |
| Docker | https://docs.docker.com/ |

---

## 📄 Project Status

> 🚧 **Hiring Forge is currently under active development.**

Some deployment and infrastructure procedures may still evolve as the project moves toward its final production setup.
