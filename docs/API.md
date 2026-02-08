# TNMshop API Documentation

Base URL (local): `http://localhost:8000/api`

## Authentication (Admin)
Admin endpoints require:
- Header: `X-Admin-Key: <ADMIN_KEY>`

`ADMIN_KEY` is configured in `backend/.env`.

## Public Endpoints
### List products
- `GET /products`
- Query: `q` (optional search)
Response:
```json
{ "data": [ { "id": 1, "sku": "...", "name": "...", "price": "1290.00", ... } ] }
```

### Product detail
- `GET /products/{id}`

### Checkout (create order + payment intent)
- `POST /checkout`
Body:
```json
{
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "0812345678",
  "shipping_address": "Address...",
  "items": [
    { "product_id": 1, "quantity": 2 }
  ]
}
```
Response:
```json
{
  "data": {
    "order_number": "TNM-XXXXXXXX",
    "payment_intent_id": "pi_xxx",
    "amount": 2580,
    "currency": "THB",
    "redirect_url": "http://localhost:5173/payment?intent=pi_xxx"
  }
}
```

### Get payment intent and order
- `GET /payments/{intentId}`

### Confirm payment (Mock Gateway)
- `POST /payments/{intentId}/confirm`
Body:
```json
{
  "card_number": "4242 4242 4242 4242",
  "exp_month": 12,
  "exp_year": 2030,
  "cvc": "123"
}
```
Optional:
- `simulate`: `"success"` or `"fail"`

Rules:
- If card passes Luhn AND does not end with `0000` => success
- Otherwise => fail

## Admin Endpoints
### Products
- `GET /admin/products`
- `POST /admin/products`
- `PUT /admin/products/{id}`
- `PATCH /admin/products/{id}/toggle`
- `DELETE /admin/products/{id}`

### Orders
- `GET /admin/orders`
- `GET /admin/orders/{id}`
