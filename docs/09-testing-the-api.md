# Testing the API (Postman / curl)

Build and try an endpoint before you build its screen. If it works in Postman, any problem left is in the React side, and the other way round.

Postman: https://www.postman.com/downloads

---

## Postman

### 1. Sign in

- **POST** `http://localhost:8000/api/auth/login`
- **Body** → **raw** → **JSON**:

```json
{ "email": "admin@example.com", "password": "password" }
```

The answer is the user and a `Welcome back, Admin!` message. The token isn't in the body; it comes as the `auth_token` cookie (see the **Cookies** tab of the response).

**Postman keeps the cookie** and sends it with your next requests to `localhost:8000`, so you are now signed in.

### 2. Call anything

| Try | Request |
| --- | --- |
| who am I | **GET** `http://localhost:8000/api/me` |
| a page of products | **GET** `http://localhost:8000/api/products/list?page=1&per_page=10&sort_by=name&sort_dir=asc` |
| search and filter | **GET** `.../api/products/list?search=chair&filters[category][]=Furniture` |
| one product | **GET** `.../api/products/1` |
| create | **POST** `.../api/products` with a JSON body (below) |
| update | **PUT** `.../api/products/1` with a JSON body |
| delete | **DELETE** `.../api/products/1` |
| sign out | **POST** `.../api/logout` |

```json
{ "code": "PRD-0100", "name": "Test Product", "category": "Cleaning", "price": 99.5, "stock": 3, "is_active": true }
```

Also try the failures your screen must handle:
- leave out `name` to get a **422** with `errors`,
- reuse a code to get a **422**,
- ask for `products/99999` to get a **404**,
- sign out, then call `me` to get a **401**.

> Tip: save the requests in a Postman **collection** called "Simple Boilerplate", and put `http://localhost:8000/api` in a collection variable `{{baseUrl}}`.

## curl (from a terminal)

Use Git Bash (or macOS/Linux). `-c` saves cookies to a file and `-b` sends them.

```bash
# sign in (saves the auth_token cookie in cookies.txt)
curl -c cookies.txt -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  http://localhost:8000/api/auth/login

# signed-in requests
curl -b cookies.txt http://localhost:8000/api/me
curl -b cookies.txt "http://localhost:8000/api/products/list?search=chair"

curl -b cookies.txt -X POST -H "Content-Type: application/json" \
  -d '{"code":"PRD-0100","name":"Test","category":"Cleaning","price":10,"stock":1,"is_active":true}' \
  http://localhost:8000/api/products

curl -b cookies.txt -X DELETE http://localhost:8000/api/products/26
```

Delete `cookies.txt` when you're done.

## Reading the answer

Every answer has the same shape:

```json
{ "success": { "message": "Products found.", "data": { "items": [ ... ], "meta": { "total": 25, "page": 1, "per_page": 10, "last_page": 3 } } } }
{ "error": { "message": "Record not found." } }
{ "message": "The name field is required.", "errors": { "name": ["The name field is required."] } }
```

A **500** means a bug in the PHP code. The details are at the bottom of `storage/logs/laravel.log`.
