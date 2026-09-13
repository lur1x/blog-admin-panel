# Blog API + Admin Panel

API и админка для мобильного приложения «Блог» на Laravel.

## Стек

- PHP 8.5+ 
- Laravel 13
- MySQL 8.4+
- Laravel Sanctum (API-токены)
- Filament 4 (админка)
- Node.js 22+ (для сборки Filament)

## Возможности

### API
- Регистрация и авторизация по email/паролю
- Создание публикаций
- Общая лента публикаций с сортировкой, фильтром по дате и пагинацией
- Список собственных публикаций

### Админка (Filament)
- Вход только для пользователей с `role=admin`
- CRUD пользователей
- CRUD публикаций

## Установка

### 1. Клонировать репозиторий

```bash
git clone https://github.com/lur1x/blog-admin-panel.git
cd blog-admin-panel
```

### 2. Установить зависимости

```bash
composer install
```

### 3. Настроить окружение

```bash
cp .env.example .env
php artisan key:generate
```

Создать базу данных MySQL и пользователя:

```sql
CREATE DATABASE blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'blog_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON blog.* TO 'blog_user'@'localhost';
FLUSH PRIVILEGES;
```

Прописать креды в `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=blog_user
DB_PASSWORD=StrongPassword123!
```

### 4. Миграции и сидеры

```bash
php artisan migrate
php artisan db:seed
```

Создаст админа: `admin@example.com / password`.

### 5. Запустить сервер

```bash
php artisan serve
```

API доступен на `http://127.0.0.1:8000/api`.
Админка - на `http://127.0.0.1:8000/admin`.

## API

Все ответы - JSON. Для API-запросов обязателен заголовок `Accept: application/json`.

### Аутентификация

| Метод | Путь            | Описание    | Авторизация |
| ----- | --------------- | ----------- | ----------- |
| POST  | `/api/register` | Регистрация | Нет         |
| POST  | `/api/login`    | Вход        | Нет         |

**Запрос регистрации:**

```json
{
  "name": "Test User",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Ответ:**

```json
{
  "user": { "id": 1, "name": "Test User", "email": "user@example.com", "role": "user" },
  "access_token": "1|abc...",
  "token_type": "Bearer"
}
```

Дальше токен передаётся в заголовке `Authorization: Bearer <access_token>`.

### Публикации

| Метод | Путь            | Описание           | Авторизация |
| ----- | --------------- | ------------------ | ----------- |
| POST  | `/api/posts`    | Создать публикацию | Да          |
| GET   | `/api/posts`    | Общая лента        | Да          |
| GET   | `/api/my-posts` | Мои публикации     | Да          |

**Параметры запроса для GET /api/posts и GET /api/my-posts:**

- `sort` - `date_asc`, `date_desc` (по умолчанию), `title_asc`, `title_desc`
- `date_from` - фильтр «с» (YYYY-MM-DD)
- `date_to` - фильтр «по»
- `limit` - число записей (1–100, по умолчанию 15)
- `offset` - смещение (по умолчанию 0)

**Пример:**

```
GET /api/posts?sort=title_asc&limit=5&offset=0&date_from=2026-01-01
```

### Формат ошибок

```json
{
  "success": false,
  "message": "Ошибка валидации.",
  "errors": { "email": ["The email field is required."] }
}
```

| Статус | Когда                       |
| ------ | --------------------------- |
| 401    | Не передан/невалидный токен |
| 403    | Нет прав (для Filament)     |
| 404    | Ресурс не найден            |
| 405    | Метод не разрешён           |
| 422    | Ошибка валидации            |

## Админка

`http://127.0.0.1:8000/admin`

- Логин: `admin@example.com`
- Пароль: `password`

**Доступ только для `role=admin`.** Обычный пользователь получает 403.

Разделы:
- **Пользователи** - CRUD, роль (admin/user), смена пароля
- **Публикации** - CRUD, автор выбирается из списка пользователей

## Тесты

```bash
php artisan test
```

Покрытие:
- Auth API (register, login, ошибки)
- Posts API (создание, пагинация, сортировка, фильтр, доступ)
- Единый формат ошибок
- Доступ в Filament по роли

## Структура проекта

```
app/
├── Filament/Resources/        # Ресурсы админки (Users, Posts)
├── Http/
│   ├── Controllers/Api/       # AuthController, PostController
│   ├── Requests/              # Form Requests (валидация)
│   └── Resources/             # PostResource (сериализация)
├── Models/                    # User, Post
└── Services/                  # AuthService, PostService

bootstrap/app.php              # Регистрация middleware и хендлера ошибок
routes/api.php                 # API-маршруты
```