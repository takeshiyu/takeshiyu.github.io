---
title: Api Examples
---

# API Examples

This is an example of API documentation.

## Authentication

```php
POST /api/auth/login
{
    "email": "user@example.com",
    "password": "password"
}
```

## Users

### Get User Profile

```php
GET /api/users/profile
Authorization: Bearer {token}
```