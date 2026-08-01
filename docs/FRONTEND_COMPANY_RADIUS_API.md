# Frontend API — Company (الشركة)

توثيق الـ APIs المتعلقة بالمدن + نصف القطر (`radius_km`) والشوب.

**Base:** `/api/company`  
**Auth:** `Bearer {token}` (Sanctum) + role = company

---

## الفكرة للفرونت

الشركة تشترك في مدينة وتحدد **نصف قطر تغطية بالكيلومتر**.

| `radius_km` | المعنى |
|-------------|--------|
| `0` (افتراضي) | المدينة دي بس |
| `25` / `50` / `100` … | المدينة + ما حولها لحد الرقم |
| Max | `500` |

### مثال
- شركة: Berlin + `radius_km = 100`
- عرض عميل في Brandenburg (~60 كم)
- → العرض يظهر في الشوب وتقدر تشتريه

---

## 1) المدن المتاحة للاشتراك

```
GET /api/company/cities/available
GET /api/company/cities/available?country_id=1
```

### Response
قائمة مدن لسه مش مشترك فيها (مع `country`).

---

## 2) إضافة مدينة + نطاق

```
POST /api/company/cities/add
```

### Body
```json
{
  "city_id": 5,
  "radius_km": 100
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `city_id` | number | نعم | لازم تكون مشترك في دولة المدينة أولاً |
| `radius_km` | number | لا | افتراضي `0` — المدى `0..500` |

### Response
```json
{
  "status": "success",
  "data": {
    "city_id": 5,
    "radius_km": 100
  }
}
```

---

## 3) تحديث نصف القطر لمدينة موجودة

```
POST /api/company/cities/update-radius
```

### Body
```json
{
  "city_id": 5,
  "radius_km": 50
}
```

### Response
```json
{
  "status": "success",
  "data": {
    "city_id": 5,
    "radius_km": 50
  }
}
```

---

## 4) قائمة المدن المشترك فيها

```
GET /api/company/cities/all
GET /api/company/cities/all?country_id=1
```

### Response — كل عنصر
```json
{
  "id": 5,
  "name": {
    "de": "Berlin",
    "en": "Berlin",
    "ar": "برلين"
  },
  "country_id": 1,
  "latitude": 52.52,
  "longitude": 13.405,
  "radius_km": 100,
  "country": { ... }
}
```

### UI مقترح
- عرض اسم المدينة + `radius_km`
- زر تعديل يفتح input/dropdown ويرسل `update-radius`
- عند الإضافة: حقل `radius_km` جنب اختيار المدينة

---

## 5) حذف مدينة

```
POST /api/company/cities/remove
```

### Body
```json
{ "city_id": 5 }
```

---

## 6) مدن حسب دولة (للسيلكت)

```
GET /api/company/cities/by-country/{country_id}
```

---

## 7) الشوب

```
GET /api/company/shop
```

### شكل الـ Response (حقول الموقع في كل عرض)
```json
{
  "id": 123,
  "country_id": 1,
  "city_id": 6,
  "country": { "id": 1, "name": "Deutschland" },
  "city": { "id": 6, "name": "Brandenburg" },
  "type_id": { "id": 1, "name": "Umzug", "price": 150 },
  "zipcode": "14467",
  "description": "...",
  "answers": [ ... ]
}
```

### مهم للفرونت
- **مفيش تغيير في شكل الـ JSON**
- الفرق: هتظهر عروض من مدن جوّه الـ `radius_km`، مش بس المدن المشترك فيها حرفياً
- اعرض `city.name` و `country.name` في كارت العرض

---

## 8) شراء عرض من الشوب

```
POST /api/company/by-Offer
```

### Body
```json
{
  "offer_id": 123,
  "user_id": 10,
  "coupon_id": null
}
```

لو العرض برّه نطاق تغطية الشركة:
```json
{
  "status": "error",
  "message": "Offer location is outside your coverage area"
}
```

---

## Checklist فرونت — Company

- [ ] عند إضافة مدينة: حقل `radius_km` (0–500)
- [ ] في قائمة المدن: عرض `radius_km`
- [ ] زر تعديل → `POST cities/update-radius`
- [ ] في الشوب: عرض `city` / `country` من الـ response
- [ ] فهم إن العروض ممكن تيجي من مدن مجاورة حسب الـ radius

---

## Related — الدول (بدون تغيير)

| Method | Endpoint |
|--------|----------|
| GET | `/api/company/countries/available` |
| POST | `/api/company/countries/add` |
| POST | `/api/company/countries/remove` |
| GET | `/api/company/countries/all` |

لازم تشترك في الدولة قبل إضافة مدنها.
