# Bari Books — Laravel REST API

A simple book management REST API built with **Laravel 13** and **JWT authentication** (`tymon/jwt-auth`). Supports user registration/login, JWT-protected book CRUD with search, pagination, cover image upload, and soft delete.

---

## Tech Stack

- **PHP** ^8.3
- **Laravel** ^13.8
- **tymon/jwt-auth** ^2.3 (JWT authentication)
- **MySQL** 5.7+ / 8.0+
- **Composer**

---

## Project Structure (key files)

```
app/
├── Http/
│   ├── Controllers/API/
│   │   ├── UserAuthController.php
│   │   └── BookController.php
│   ├── Middleware/
│   │   └── JwtMiddleware.php          # custom JWT middleware (returns JSON 401)
│   ├── Requests/
│   │   ├── Auth/                      # UserRegisterRequest, UserLoginRequest
│   │   └── Book/                      # StoreBookRequest, UpdateBookRequest
│   └── Resources/
│       ├── UserResource.php
│       ├── BookResource.php
│       └── BookCollection.php
├── Models/
│   ├── User.php
│   └── Book.php
├── Repositories/
│   ├── BaseRepository.php
│   ├── UserAuthRepository.php
│   └── BookRepository.php
└── Traits/
    ├── ApiResponser.php               # successResponse / errorResponse helpers
    └── FileUpload.php                 # single file upload helper
routes/
└── api.php
bootstrap/
└── app.php                            # registers `jwt` middleware alias
```

---

## Installation

### 1. Clone the repository

```bash
git clone <your-repo-url> bari_books_assignment
cd bari_books_assignment
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment file

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Create the storage symlink (for cover image URLs to work)

Cover images are uploaded to `public/uploads/books`. The directory is created automatically on first upload, but make sure `public/` is writable:

```bash
chmod -R 775 public storage bootstrap/cache
```

---

## Database Setup

### 1. Create a MySQL database

```sql
CREATE DATABASE bari_books CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Configure `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bari_books
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run migrations

```bash
php artisan migrate
```

This creates:

- `users` — for registration / login
- `book` — books table with `_deleted` soft-delete column (tinyint, default `0`)

> **Note:** This project uses a custom `_deleted` flag on the `book` table (not Laravel's `SoftDeletes` trait). Deleting a book sets `_deleted = 1` — rows are never actually removed. A global scope on the `Book` model filters out `_deleted = 1` records automatically.

---

## JWT Setup

`tymon/jwt-auth` is already installed via Composer. You only need a JWT secret.

### 1. Generate the JWT secret

```bash
php artisan jwt:secret
```

This adds `JWT_SECRET=...` to your `.env`. A token lifetime is also configured:

```env
JWT_SECRET=<generated>
JWT_TTL=60              # token validity in minutes
```

### 2. Auth guard config (`config/auth.php`)

Already configured — the `api` guard uses the `jwt` driver:

```php
'guards' => [
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

### 3. User model

`App\Models\User` implements `Tymon\JWTAuth\Contracts\JWTSubject` with `getJWTIdentifier()` and `getJWTCustomClaims()` already defined.

### 4. Custom JWT middleware

This project uses a custom middleware `App\Http\Middleware\JwtMiddleware` (registered as alias **`jwt`** in `bootstrap/app.php`) that returns JSON 401 responses for token errors instead of redirecting:

| Scenario | Response |
|---|---|
| No `Authorization` header | `{"status": false, "message": "Token not provided."}` |
| Expired token | `{"status": false, "message": "Token has expired."}` |
| Invalid token | `{"status": false, "message": "Token is invalid."}` |

> Do **not** use `jwt.auth` as the middleware name — that alias is reserved by the `tymon/jwt-auth` package and will run its built-in middleware instead.

---

## Run the App

```bash
php artisan serve
```

Server starts at `http://127.0.0.1:8000`. All API routes are under `/api`.

---

## API Documentation

**Base URL:** `http://127.0.0.1:8000/api`

All responses follow this envelope:

```json
{
  "status": true | false,
  "message": "Human-readable message",
  "data": { ... } | [] | null
}
```

### Auth Endpoints (public)

#### `POST /auth/register`

Register a new user and receive a JWT.

**Body (JSON):**

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Response `200`:**

```json
{
  "status": true,
  "message": "User registered successfully",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com", ... }
  }
}
```

#### `POST /auth/login`

**Body (JSON):**

```json
{
  "email": "john@example.com",
  "password": "secret123"
}
```

**Response `200`:**

```json
{
  "status": true,
  "message": "Login successful.",
  "data": {
    "token": "eyJhbGciOi...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com", ... }
  }
}
```

**Errors:**

- `401` — `Invalid email or password.`
- `422` — Validation failed.

---

### Protected Endpoints

All endpoints below require the header:

```
Authorization: Bearer <token>
Accept: application/json
```

#### `GET /auth/profile`

Returns the authenticated user.

#### `GET /books`

List books (paginated, supports search).

**Query params:**

| Param | Type | Default | Notes |
|---|---|---|---|
| `search` | string | — | Matches `title` OR `author` (LIKE %search%) |
| `per_page` | int | 10 | Page size |
| `page` | int | 1 | Page number |

**Response `200`:**

```json
{
  "status": true,
  "message": "Books fetched successfully.",
  "data": {
    "books": [
      {
        "id": 1,
        "title": "The Hobbit",
        "author": "J.R.R. Tolkien",
        "coverImage": "http://127.0.0.1:8000/uploads/books/abc.jpg",
        "price": "19.99",
        "publishedDate": "1937-09-21",
        "createdAt": "29-05-2026 11:00:00",
        "updatedAt": "29-05-2026 11:00:00"
      }
    ],
    "links": {
      "has-pages": false,
      "next": "",
      "items": 1,
      "total": 1,
      "current_page": 1,
      "last_page": 1,
      "per_page": 10
    }
  }
}
```

#### `POST /books`

Create a book. Supports `multipart/form-data` for cover image upload.

**Body (multipart/form-data):**

| Field | Type | Required | Rules |
|---|---|---|---|
| `title` | string | yes | max 255 |
| `author` | string | yes | max 255 |
| `price` | numeric | yes | min 0 |
| `published_date` | date | yes | format `Y-m-d` |
| `cover_image` | file | no | jpeg/jpg/png/webp, max 2 MB |

**Response `201`:** the created book wrapped in `BookResource`.

#### `GET /books/{id}`

Fetch a single book. Returns `404` if not found or soft-deleted.

#### `PUT /books/{id}`

Update a book. All fields are optional (`sometimes` validation). Same rules as POST.

> For file upload on `PUT`, use `POST` with `_method=PUT` form field, OR send JSON with `Content-Type: application/json`.

#### `DELETE /books/{id}`

Soft-deletes the book (sets `_deleted = 1`). Returns `200` on success, `404` if not found.

```json
{
  "status": true,
  "message": "Book deleted successfully.",
  "data": []
}
```

---

### Error Responses

| Status | When |
|---|---|
| `400` | Generic bad request |
| `401` | Missing/invalid/expired JWT, wrong credentials |
| `404` | Resource not found (or soft-deleted) |
| `422` | Validation failed (returns first error message) |
| `500` | Server error (exception message in `message`) |

Example `422`:

```json
{
  "status": false,
  "message": "Validation failed.",
  "data": "The title field is required."
}
```

---

## Postman Collection

A ready-to-import Postman collection is included at the project root:

```
docs/Bari-Books.postman_collection.json
```

### Import steps

1. Open Postman → **Import** → **File** → select `docs/Bari-Books.postman_collection.json`.
2. The collection includes a `{{base_url}}` variable (defaults to `http://127.0.0.1:8000/api`) and a `{{token}}` variable.
3. Run **Auth → Login** first — the test script automatically saves `data.token` into the `{{token}}` collection variable, so all subsequent protected requests authenticate automatically.

### Suggested test flow

1. `Auth → Register` (or skip and use an existing user)
2. `Auth → Login` → token auto-saved
3. `Books → Create Book` (multipart with cover image)
4. `Books → List Books` (try `?search=` and `?per_page=`)
5. `Books → Show / Update / Delete`

---

## Common Commands

```bash
php artisan serve                    # start dev server
php artisan migrate:fresh            # rebuild DB
php artisan optimize:clear           # clear all caches (do this after editing bootstrap/app.php)
php artisan route:list --path=api    # list registered API routes
php artisan jwt:secret               # regenerate JWT secret
```

---

## Troubleshooting

- **`Route [login] not defined.`** — Your `Accept` header isn't `application/json` and the request fell through Laravel's default auth redirect. The custom `jwt` middleware avoids this; make sure protected routes use `Route::middleware('jwt')` (not `auth:api`).
- **Custom middleware not running** — Don't name your alias `jwt.auth`; the `tymon/jwt-auth` package overrides it. Use `jwt` instead.
- **`Class "Illuminate\Database\Eloquent\Builder" not found` in `Book` model** — Ensure `use Illuminate\Database\Eloquent\Builder;` is at the top of `app/Models/Book.php`.
- **Cover image not visible** — Check that `public/uploads/books` exists and is writable. URLs are built using `asset('uploads/books/{filename}')`.
- **After editing `bootstrap/app.php`** — restart `php artisan serve` and run `php artisan optimize:clear`.

---

## License

MIT
