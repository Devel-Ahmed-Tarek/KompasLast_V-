# Frontend API — Admin (الأدمن / السوبر أدمن)

توثيق الـ APIs المتعلقة بالمدن، الإحداثيات، نطاق الشركات، وحالة تأكيد العروض.

**Base:** `/api/admin`  
**Auth:** `Bearer {token}` (Sanctum) + role = admin

---

## الفكرة للفرونت

1. إدارة المدن (مع `latitude` / `longitude`)
2. إدارة اشتراك الشركات في المدن مع `radius_km`
3. عرض هل العميل أكّد العرض ولا لأ (`confirm_status`)

---

# A) إدارة المدن (Coordinates)

## 1) قائمة المدن

```
GET /api/admin/cities
GET /api/admin/cities?country_id=1
```

### Response — كل مدينة فيها
```json
{
  "id": 5,
  "country_id": 1,
  "name": { "de": "Berlin", "en": "Berlin", "ar": "برلين" },
  "latitude": 52.52,
  "longitude": 13.405,
  "country": { ... }
}
```

---

## 2) إنشاء مدينة

```
POST /api/admin/cities
```

### Body
```json
{
  "country_id": 1,
  "name": {
    "en": "Berlin",
    "de": "Berlin",
    "ar": "برلين",
    "fr": "Berlin",
    "it": "Berlino"
  },
  "latitude": 52.52,
  "longitude": 13.405
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `name.en` | نعم | |
| `country_id` | نعم | |
| `latitude` | لا | `-90` … `90` |
| `longitude` | لا | `-180` … `180` |

> بدون lat/lng: الـ radius للشركات مش هيغطي المدن المجاورة (fallback = نفس المدينة فقط).

---

## 3) تحديث مدينة

```
PUT /api/admin/cities/{id}
```

### Body (جزئي مسموح)
```json
{
  "latitude": 52.52,
  "longitude": 13.405
}
```

---

## 4) مدن حسب دولة

```
GET /api/admin/cities/by-country/{country_id}
```

---

# B) نطاق تغطية الشركة (radius_km)

كل الـ endpoints محتاجة `company_id`.

## 1) مدن متاحة للشركة

```
GET /api/admin/company/cities/available?company_id=10
GET /api/admin/company/cities/available?company_id=10&country_id=1
```

---

## 2) إضافة مدينة + نطاق لشركة

```
POST /api/admin/company/cities/add
```

### Body
```json
{
  "company_id": 10,
  "city_id": 5,
  "radius_km": 100
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `company_id` | نعم | |
| `city_id` | نعم | الشركة لازم تكون مشترك في دولة المدينة |
| `radius_km` | لا | افتراضي `0` — المدى `0..500` |

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

## 3) تحديث نطاق مدينة للشركة

```
POST /api/admin/company/cities/update-radius
```

### Body
```json
{
  "company_id": 10,
  "city_id": 5,
  "radius_km": 50
}
```

---

## 4) قائمة مدن الشركة

```
GET /api/admin/company/cities/all?company_id=10
```

### Response — كل عنصر
```json
{
  "id": 5,
  "name": { "de": "Berlin", "en": "Berlin" },
  "country_id": 1,
  "latitude": 52.52,
  "longitude": 13.405,
  "radius_km": 100,
  "country": { ... }
}
```

---

## 5) حذف مدينة من شركة

```
POST /api/admin/company/cities/remove
```

### Body
```json
{
  "company_id": 10,
  "city_id": 5
}
```

---

### UI مقترح — صفحة شركة
- جدول مدن الشركة: الاسم | lat/lng | `radius_km` | إجراءات
- عند الإضافة: اختيار مدينة + input `radius_km`
- زر تعديل الـ radius فقط بدون حذف/إعادة إضافة

---

# C) حالة تأكيد العرض (Confirm)

## قائمة العروض

```
GET /api/admin/offers
GET /api/admin/offers?confirm_status=pending
GET /api/admin/offers?confirm_status=confirmed
GET /api/admin/offers?confirm_status=confirmed&filter=new
```

### حقول جديدة في كل عرض
```json
{
  "id": 123,
  "name": "Max",
  "status": 1,
  "confirm_status": "confirmed",
  "confirmed_at": "2026-06-05T14:30:00.000000Z",
  "is_confirmed": true,
  "type": { "id": 1, "name": "Umzug" },
  "country_id": 1,
  "city_id": 6,
  "latitude": 52.51,
  "longitude": 13.39
}
```

| Field | Type | الاستخدام في UI |
|-------|------|------------------|
| `is_confirmed` | boolean | Badge سريع: مؤكد / منتظر |
| `confirm_status` | `pending` \| `confirmed` | فلتر / نص |
| `confirmed_at` | string \| null | تاريخ التأكيد |

---

## تفاصيل عرض واحد

```
GET /api/admin/offers/{id}
```

نفس الحقول:
- `confirm_status`
- `confirmed_at`
- `is_confirmed`

---

### UI مقترح — جدول العروض
- عمود: **تأكيد العميل** → أخضر لو `is_confirmed`، أصفر لو pending
- فلتر/تاب: الكل | مؤكد | في انتظار التأكيد
- في التفاصيل: عرض `confirmed_at` لو موجود

---

# Checklist فرونت — Admin

### مدن
- [ ] عرض `latitude` / `longitude` في قائمة المدن
- [ ] عند إنشاء/تعديل مدينة: حقول lat/lng

### شركات
- [ ] عند إضافة مدينة لشركة: حقل `radius_km`
- [ ] عرض وتعديل `radius_km` من `cities/all` و `update-radius`

### عروض
- [ ] Badge `is_confirmed` / `confirm_status`
- [ ] فلتر `?confirm_status=pending|confirmed`
- [ ] عرض `confirmed_at` في التفاصيل

---

# ملخص سريع للـ Radius

```
radius_km = 0     → المدينة فقط
radius_km = 100   → المدينة + كل المدن في نفس الدولة ضمن 100 كم
```

لازم المدن يكون لها إحداثيات (من السيدر أو من الأدمن) عشان النطاق يشتغل.
