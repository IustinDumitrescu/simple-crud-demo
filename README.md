# Simple CRUD Demo

A small Symfony + PostgreSQL + Dockerized CRUD demo to showcase article management with categories.

---

## 🛠 Tech Stack

- PHP 8.4 + Symfony 8
- PostgreSQL 18
- Nginx
- Docker & Docker Compose
- Webpack Encore for assets
- Bootstrap 5 for UI
- KNP Paginator bundle for pagination

---

## ⚡ Features

- Full CRUD for Articles
- Categories with dynamic creation
- Article search and category filter
- Pagination
- Soft delete for articles
- SEO-friendly article URLs (`slug + id`)
- Fully Dockerized environment for development

---

## 🚀 Getting Started

### Requirements

- Docker & Docker Compose installed

### Run

```bash
git clone https://github.com/IustinDumitrescu/simple-crud-demo.git
cd simple-crud-demo
cp ./server/.env.example ./server/.env
docker-compose up
