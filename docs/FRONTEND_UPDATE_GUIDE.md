# Frontend Update Guide — تنفيذ كل تحديثات الـ APIs

ملف واحد للفرونت. نفّذ بالترتيب. الـ Base: `/api`

لغة النصوص: `?lang=de` أو هيدر `Accept-Language: de`  
اللغات: `de` | `en` | `ar` | `fr` | `it`

Auth الشركة/الأدمن: `Authorization: Bearer {token}`

---

## 0) إيه اللي اتغيّر (اقرأ ده الأول)

### الجغرافيا
قبل كده الـ 16 ولاية الألمانية كانت متعاملة كـ «مدن».  
دلوقتي الهرمية:

```
دولة (Country)
  └── ولاية (State)  = Bundesland   ← 16 ولاية
        ├── مدينة (place_type = city)           = Stadt      ← 2,056
        └── بلدية (place_type = municipality)   = Gemeinde   ← 8,893
```

المدينة والبلدية **إخوة** تحت نفس الولاية. البلدية مش جوه المدينة.

- الاشتراك والنطاق يكون على **مدينة أو بلدية** (`city_id` في الـ body = ID المكان ده).
- **ممنوع** اختيار ولاية كأنها مدينة (`place_type = region`).
- `radius_km` بالكيلومتر فقط (0 … 500). مش متر.

### الشوب
العرض يظهر للشركة لو مكان العرض جوّه نصف قطر تغطيتها. كمان الـ JSON بقى فيه `city` و `country` و `latitude` / `longitude`.

### تأكيد العميل
العرض مش بيتوزع على الشركات غير بعد تأكيد الإيميل. الأدمن يفلتر بـ `confirm_status`.

---

## 1) قواعد UI إلزامية (عشان الـ 10 آلاف مكان متكسرش السيليكت)

**متجيبش كل ألمانيا في select واحد.**

الفلوه الصح:

1. دولة  
2. ولاية (`GET .../states?country_id=`)  
3. تاب/فلتر: مدينة أو بلدية  
4. أماكن الولاية فقط (`GET .../states/{id}/places?place_type=`)

بدون `state_id`، `cities/by-country` و `cities/available` بيرجعوا **المدن فقط** مش البلديات.

ابحث/autocomplete جوه الولاية لأن بايرن وحدها فيها 2000+ مكان.

`place_type` المسموح في السيليكت: `city` | `municipality` فقط.

---

## 2) User / الموقع — قوائم الموقع

Auth: مش مطلوب

### 2.1 دول
```
GET /api/user/countries?lang=de
```
```json
{ "status": "success", "data": [{ "id": 1, "name": "Deutschland" }] }
```

### 2.2 ولايات
```
GET /api/user/states?country_id=1&lang=de
```
```json
{
  "status": "success",
  "data": [
    {
      "id": 4,
      "country_id": 1,
      "code": "BY",
      "name": "Bayern",
      "latitude": 48.7904,
      "longitude": 11.4979
    }
  ]
}
```

### 2.3 أماكن الولاية (المسار الموصى به)
```
GET /api/user/states/{state_id}/places?place_type=city&lang=de
GET /api/user/states/{state_id}/places?place_type=municipality&lang=de
```
بدون `place_type` → المدن + البلديات مع بعض.

```json
{
  "status": "success",
  "data": {
    "state": { "id": 4, "country_id": 1, "code": "BY", "name": "Bayern" },
    "places": [
      { "id": 88, "state_id": 4, "place_type": "city", "name": "München", "latitude": 48.13, "longitude": 11.57 }
    ]
  }
}
```

استخدم `places[].id` كـ `city_id` عند إنشاء العرض.

### 2.4 مدن حسب الدولة (فلتر إضافي)
```
GET /api/user/cities/by-country/{country_id}?lang=de
GET /api/user/cities/by-country/{country_id}?state_id=4&place_type=municipality&lang=de
```

Response: `{ country, cities[] }` وكل عنصر فيه `id, state_id, place_type, name, latitude, longitude`.

---

## 3) User — إنشاء عرض (موقع)

ينطبق على:

```
POST /api/user/add-offer
POST /api/user/offers/submit-form
```

لازم **واحد** من المسارين (أو الاتنين مع بعض):

| المسار | Body | السلوك |
|--------|------|--------|
| A Select | `country_id` + `city_id` | يحفظ المكان المختار |
| B GPS | `latitude` + `longitude` | أقرب مدينة/بلدية تتحدد أوتوماتيك |
| C Mixed | الاتنين | المدينة من السيليكت + الإحداثيات للتوزيع الأدق |

| Field | Type | Required |
|-------|------|----------|
| `country_id` | number | لو مفيش lat/lng |
| `city_id` | number | لو مفيش lat/lng — ID مدينة **أو** بلدية |
| `latitude` | number `-90..90` | لو مفيش select |
| `longitude` | number `-180..180` | لازم مع latitude |

`city_id` لازم يكون مكان حقيقي (Stadt/Gemeinde) مش ولاية.

### مثال A — Select
```json
{
  "type_id": 1,
  "country_id": 1,
  "city_id": 88,
  "name": "Max Mustermann",
  "email": "max@example.com",
  "phone": "+49123456789",
  "lang": "de"
}
```

### مثال B — GPS فقط
```json
{
  "type_id": 1,
  "latitude": 52.52,
  "longitude": 13.405,
  "name": "Max Mustermann",
  "email": "max@example.com",
  "phone": "+49123456789",
  "lang": "de"
}
```

### أخطاء 422
```json
{ "location": ["country_id and city_id are required when coordinates are not provided."] }
{ "location": ["Both latitude and longitude are required together."] }
{ "location": ["No nearby city with coordinates was found."] }
```

تأكيد الإيميل زي ما هو:

```
GET /api/user/offers/confirm/{token}
```

UI مقترح: دولة → ولاية → مدينة/بلدية + زر «استخدم موقعي».

---

## 4) Company — تغطية + Radius

Auth: company

الشركة تشترك في دولة أولاً، بعدين مدينة أو بلدية + `radius_km`.

| `radius_km` | المعنى |
|-------------|--------|
| `0` (افتراضي) | المكان ده بس |
| `25` / `50` / `100` | المكان + اللي حواليه لحد الرقم بالكم |
| Max | `500` |

مثال: Bernau + `radius_km = 20` → عروض البلدية وما حولها لحد 20 كم في الشوب والشراء التلقائي.

### 4.1 ولايات / أماكن (للسيليكت)
```
GET /api/company/states?country_id=1&lang=de
GET /api/company/states/{state_id}/places?place_type=municipality&lang=de
GET /api/company/cities/by-country/{country_id}?state_id=4&place_type=city
```

نفس شكل الـ User.

### 4.2 متاح للاشتراك
```
GET /api/company/cities/available
GET /api/company/cities/available?country_id=1
GET /api/company/cities/available?country_id=1&state_id=4&place_type=municipality
```

Query: `country_id?` `state_id?` `place_type=city|municipality`

بدون `state_id` → مدن فقط.

### 4.3 إضافة مكان + نطاق
```
POST /api/company/cities/add
```
```json
{ "city_id": 88, "radius_km": 25 }
```

| Field | Required | Notes |
|-------|----------|-------|
| `city_id` | نعم | مدينة أو بلدية. لازم اشتراك الدولة أولاً |
| `radius_km` | لا | integer `0..500` افتراضي `0` |

400 لو المكان ولاية (`region`) أو الدولة مش مشترك فيها أو المكان مضاف قبل كده.

### 4.4 تعديل النطاق
```
POST /api/company/cities/update-radius
```
```json
{ "city_id": 88, "radius_km": 50 }
```

### 4.5 القائمة المشترك فيها
```
GET /api/company/cities/all
GET /api/company/cities/all?country_id=1&state_id=4
```

كل عنصر:

```json
{
  "id": 88,
  "name": { "de": "München", "en": "München" },
  "country_id": 1,
  "state_id": 4,
  "place_type": "city",
  "latitude": 48.13,
  "longitude": 11.57,
  "radius_km": 25,
  "country": {},
  "state": {}
}
```

اعرض الاسم + `place_type` + `radius_km` + زر تعديل النطاق.

### 4.6 حذف
```
POST /api/company/cities/remove
```
```json
{ "city_id": 88 }
```

### 4.7 دول (من غير تغيير)
```
GET  /api/company/countries/available
POST /api/company/countries/add
POST /api/company/countries/remove
GET  /api/company/countries/all
```

---

## 5) Company — الشوب والشراء

```
GET /api/company/shop
GET /api/company/favorites
GET /api/company/singel-offer/{id}
```

كل عرض بقى فيه موقع:

```json
{
  "id": 123,
  "country_id": 1,
  "city_id": 88,
  "latitude": 52.51,
  "longitude": 13.39,
  "country": { "id": 1, "name": "Deutschland" },
  "city": { "id": 88, "name": "München" },
  "type_id": { "id": 1, "name": "Umzug", "price": 150 },
  "zipcode": "80331",
  "description": "...",
  "answers": []
}
```

- `city` / `country` ممكن يكونوا مش موجودين لو العلاقة مش متحمّلة؛ اعرض الاسم لو موجود.
- `latitude` / `longitude` ممكن `null` لو العميل عمل select من غير GPS.
- `answers` = أسئلة `show_before_purchase = true` فقط (قبل الشراء). بعد الشراء باقي الإجابات في `get-my-offer`.

الشراء:

```
POST /api/company/by-Offer
```
```json
{ "offer_id": 123, "user_id": 10, "coupon_id": null }
```

لو برّه التغطية:

```json
{ "status": "error", "message": "Offer location is outside your coverage area" }
```

الفلتر في الشوب أوتوماتيك حسب `radius_km`. الفرونت مش بيحسب المسافة.

---

## 6) Admin — ولايات / مدن / بلديات

Auth: admin

```
GET /api/admin/states?country_id=1&lang=de
GET /api/admin/states/{state_id}/places?place_type=city
GET /api/admin/cities?country_id=1&state_id=4&place_type=municipality
GET /api/admin/cities/by-country/{country_id}
GET /api/admin/cities/{id}
POST /api/admin/cities
PUT /api/admin/cities/{id}
DELETE /api/admin/cities/{id}
```

إنشاء مدينة/بلدية:

```json
{
  "country_id": 1,
  "state_id": 4,
  "place_type": "city",
  "name": { "en": "Munich", "de": "München", "ar": "ميونخ", "fr": "Munich", "it": "Monaco" },
  "latitude": 48.137,
  "longitude": 11.576
}
```

| Field | Required |
|-------|----------|
| `name.en` | نعم |
| `country_id` | نعم |
| `state_id` | لا (مستحسن) |
| `place_type` | لا — `city` \| `municipality` \| `region` — افتراضي `city` |
| `latitude` / `longitude` | لا |

بدون إحداثيات: الـ radius للشركات مش هيغطي الجيران (المكان نفسه فقط).

---

## 7) Admin — تغطية شركة

نفس الشركة، مع `company_id` إجباري.

```
GET  /api/admin/company/cities/available?company_id=10&country_id=1&state_id=4&place_type=municipality
POST /api/admin/company/cities/add
POST /api/admin/company/cities/update-radius
GET  /api/admin/company/cities/all?company_id=10
POST /api/admin/company/cities/remove
```

Add:

```json
{ "company_id": 10, "city_id": 88, "radius_km": 100 }
```

Update radius:

```json
{ "company_id": 10, "city_id": 88, "radius_km": 50 }
```

Remove:

```json
{ "company_id": 10, "city_id": 88 }
```

الدول زي ما هي: `/api/admin/company/countries/*` + `company_id`.

UI: جدول الاسم | نوع | ولاية | lat/lng | `radius_km` | تعديل النطاق.

---

## 8) Admin — تأكيد العروض

```
GET /api/admin/offers
GET /api/admin/offers?confirm_status=pending
GET /api/admin/offers?confirm_status=confirmed
GET /api/admin/offers/{id}
```

حقول جديدة في كل عرض:

```json
{
  "id": 123,
  "status": 1,
  "confirm_status": "confirmed",
  "confirmed_at": "2026-08-16T14:30:00.000000Z",
  "is_confirmed": true,
  "country_id": 1,
  "city_id": 88,
  "latitude": 52.51,
  "longitude": 13.39
}
```

| Field | قيم | UI |
|-------|------|-----|
| `is_confirmed` | boolean | Badge أخضر / أصفر |
| `confirm_status` | `pending` \| `confirmed` | فلتر |
| `confirmed_at` | ISO \| null | في التفاصيل |

`status` (نشر الأدمن) منفصل عن تأكيد العميل.

---

## 9) Admin — أسئلة قبل الشراء

عند إنشاء/تعديل سؤال الخدمة:

```
POST /api/admin/types/{type_id}/questions
PUT  /api/admin/types/{type_id}/questions/{id}
```

حقل: `show_before_purchase` (boolean)

- `true` → الإجابة تظهر في الشوب قبل ما الشركة تشتري
- `false` → تظهر بعد الشراء فقط

السيدر الحالي مخلّي الأسئلة `true` — لو الأدمن عايز يخفي سؤال من الشوب يقفله.

---

## 10) Checklist تنفيذ سريع

### User
- [ ] سيليكت: دولة → ولاية → مدينة/بلدية (مش ولاية كمدينة)
- [ ] `city_id` = `places[].id`
- [ ] GPS اختياري: `latitude` + `longitude` مع بعض
- [ ] Validation: select كامل **أو** lat+lng
- [ ] متجيبش كل البلديات من غير `state_id`

### Company
- [ ] نفس السيليكت المتدرج + فلتر `place_type`
- [ ] اشتراك الدولة قبل المكان
- [ ] حقل `radius_km` 0–500 عند الإضافة
- [ ] عرض وتعديل `radius_km` من `cities/all`
- [ ] الشوب: اعرض `city.name` و `country.name`
- [ ] رسالة خارج التغطية عند الشراء

### Admin
- [ ] فلتر مدن بـ `state_id` / `place_type`
- [ ] إنشاء/تعديل: `state_id` + `place_type` + lat/lng
- [ ] تغطية الشركة: `radius_km` + `company_id`
- [ ] جدول عروض: Badge تأكيد + `?confirm_status=`
- [ ] Toggle `show_before_purchase` على الأسئلة

---

## 11) أخطاء شائعة

| غلط | الصح |
|-----|------|
| تحميل 10,949 مكان في select واحد | ولاية أولاً ثم أماكنها |
| اعتبار الولاية مدينة | الولاية من `/states` فقط |
| `radius` بالمتر | `radius_km` بالكيلومتر |
| إضافة مدينة قبل الدولة | `countries/add` أولاً |
| إرسال latitude من غير longitude | الاتنين مع بعض |
| الاعتماد على قائمة المدن القديمة (16 صف) | القوائم الجديدة بعد السيدر |

---

## جدول Endpoints الجديدة/المحدّثة

| من | Method | Path |
|----|--------|------|
| User | GET | `/api/user/states` |
| User | GET | `/api/user/states/{state_id}/places` |
| User | GET | `/api/user/cities/by-country/{country_id}` (+ `state_id`, `place_type`) |
| User | POST | `/api/user/add-offer` (+ lat/lng أو city) |
| User | POST | `/api/user/offers/submit-form` (نفس حقول الموقع) |
| Company | GET | `/api/company/states` |
| Company | GET | `/api/company/states/{state_id}/places` |
| Company | GET | `/api/company/cities/available` (+ `state_id`, `place_type`) |
| Company | POST | `/api/company/cities/add` (+ `radius_km`) |
| Company | POST | `/api/company/cities/update-radius` |
| Company | GET | `/api/company/cities/all` (`radius_km`, `place_type`, `state`) |
| Company | GET | `/api/company/shop` (`city`, `country`, lat/lng) |
| Admin | GET | `/api/admin/states` |
| Admin | GET | `/api/admin/states/{state_id}/places` |
| Admin | GET/POST/PUT | `/api/admin/cities` (`state_id`, `place_type`, lat/lng) |
| Admin | GET/POST | `/api/admin/company/cities/*` (`radius_km`, `state_id`) |
| Admin | GET | `/api/admin/offers?confirm_status=pending\|confirmed` |
