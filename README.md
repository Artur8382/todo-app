# Mini Task Manager

A simple Symfony project with **CRUD for tasks** and **basic authentication**, using **PostgreSQL**.

---

## Features

- User authentication (login/logout)
- CRUD operations for tasks (Create, Read, Update, Delete)
- Task status: open / done
- Dockerized PHP + PostgreSQL setup

---

## Default User

The project includes a default user :


Email: junior@test.com

Password: password


## Build
```
docker compose up --build -d
docker compose exec php php bin/console doctrine:migrations:migrate
```
