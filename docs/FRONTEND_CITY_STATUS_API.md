# Frontend API — حالة المدن (City Status)

الأدمن يتحكم في ظهور كل مدينة بـ 3 أزرار. اليوزر يشوف الشغالة فقط. الشركة تشوف الشغالة + «قريبًا» (معطّلة).

**Auth الأدمن:** `Bearer {token}` + role = admin  
**Auth الشركة:** `Bearer {token}` + role = company  
**اليوزر / السايت:** بدون تسجيل في endpoints المدن

---

## القيم

| `status` | `status_label` | `disabled` | المعنى | اليوزر | الشركة |
|----------|----------------|------------|--------|--------|---------|
| `0` | `inactive` | `false` | مقفولة | مختفية | مختفية |
| `1` | `active` | `false` | شغالة | ظاهرة ويقدر يختارها | ظاهرة ويقدر يشترك / ياخد عقد |
| `2` | `coming_soon` | `true` | قريبًا | مختفية | ظاهرة **معطّلة** — يشوفها ومش يقدر يشترك ولا ياخد عقد |

- المدن الحالية كلها `1` بشكل افتراضي.

---

## 1) داشبورد الأدمن — التحكم

اعرض لكل مدينة 3 أزرار، والزر النشط = `city.status` الحالي.

### قائمة المدن (كل الحالات تظهر هنا)

```
GET /api/admin/cities
GET /api/admin/cities?country_id=1
GET /api/admin/cities?state_id=5
GET /api/admin/cities?status=2
```

`status` اختياري للفلترة داخل الداشبورد.

### Response — حقول مهمة في كل مدينة

```json
{
  "id": 1333,
  "name": { "de": "Berlin", "en": "Berlin", "ar": "برلين" },
  "country_id": 1,
  "state_id": 3,
  "place_type": "city",
  "latitude": 52.52,
  "longitude": 13.405,
  "status": 1,
  "status_label": "active",
  "disabled": false
}
```

نفس الحقلين موجودين في:

```
GET  /api/admin/cities/{id}
GET  /api/admin/cities/by-country/{country_id}
GET  /api/admin/states/{state_id}/places
PUT  /api/admin/cities/{id}
PATCH /api/admin/cities/{id}/status
```

### تغيير الحالة (مفضّل للأزرار)

```
PATCH /api/admin/cities/{id}/status
```

```json
{ "status": 0 }
```

أو `1` أو `2`.

### Response

```json
{
  "status": 200,
  "msg": "City status updated successfully",
  "data": {
    "id": 1333,
    "status": 0,
    "status_label": "inactive"
  }
}
```

بعد النجاح حدّث الصف محليًا من `data.status` — من غير ما تعيد تحميل القائمة كلها.

### بديل: تحديث مع باقي بيانات المدينة

```
PUT /api/admin/cities/{id}
```

```json
{ "status": 2 }
```

ينفع تبعت `status` لوحده أو مع `name` / `latitude` / …

### اقتراح UI

```
[ شغالة ]   [ قريبًا ]   [ مقفولة ]
    1            2            0
```

- الزر المختار حسب `status`
- `inactive` / `coming_soon`: Badge في الجدول (أحمر / برتقالي)
- `active`: أخضر
- فلتر أعلى الصفحة: الكل / شغالة / قريبًا / مقفولة → `?status=`

عند إنشاء مدينة جديدة:

```
POST /api/admin/cities
```

`status` اختياري، الافتراضي `1`.

---

## 2) سايت اليوزر — اختيار مكان العرض

مفيش شغل إضافي غير إن المدن المختفية مش هتيجي في الـ API.

استخدم نفس الـ endpoints:

```
GET /api/user/states?country_id=1&lang=de
GET /api/user/states/{state_id}/places?place_type=city
GET /api/user/states/{state_id}/places?place_type=municipality
GET /api/user/cities/by-country/{country_id}?state_id={id}&lang=de
```

- الرد فيه المدن الـ `status = 1` فقط.
- متتعرضش `status` لليوزر، ومفيش شاشة «قريبًا» على السايت.
- لو اليوزر بعت `city_id` مقفولة/قريبًا مع إنشاء العرض:

```
POST /api/user/offers/submit-form
```

الرد:

```json
{
  "status": 422,
  "msg": "Validation Error",
  "data": {
    "location": ["This city is not available."]
  }
}
```

اعمل refresh لقائمة المدن لو حصل الخطأ ده (المدينة اتقفلت بعد ما فتح الفورم).

---

## 3) لوحة الشركة — اشتراك المدن

«قريبًا» **تظهر** للشركة، لكن كعنصر disabled: من غير اختيار / إضافة / عقد.

```
GET /api/company/cities/available
GET /api/company/cities/all
GET /api/company/cities/by-country/{country_id}
GET /api/company/states/{state_id}/places
POST /api/company/cities/add
```

كل مدينة في رد الشركة فيها:

```json
{
  "id": 1333,
  "name": "Berlin",
  "status": 2,
  "status_label": "coming_soon",
  "disabled": true
}
```

### UI مطلوب

```js
if (city.disabled || city.status === 2) {
  // disabled: رمادي + Badge «قريبًا» + مفيش زر Add / Subscribe
  // متبعتش POST /cities/add
}
```

- `status = 1`: زر إضافة شغال.
- `status = 2`: ظاهرة ومعطّلة. الباكند هيرفض الإضافة كمان:

```json
{ "status": 400, "msg": "This city is coming soon" }
```

- `status = 0`: مش هتيجي في القائمة أصلًا.
- لو المدينة كانت مشتركة وبعدين بقت قريبًا: تظهر في `cities/all` بـ `disabled: true` ومش هتاخد عقود.

---

## 4) أدمن يضيف مدن لشركة

``` 
GET  /api/admin/company/cities/available?company_id=10
POST /api/admin/company/cities/add
GET  /api/admin/company/cities/all?company_id=10
```

- `available` = شغالة + قريبًا. اللي `disabled: true` متتضافش.
- `all` = اشتراكات الشركة مع `status` / `disabled`.

لو `disabled === true`: Badge «قريبًا» ومن غير إضافة أو تعديل radius.

---

## ملخص سريع للفرونت

1. الأدمن: 3 أزرار → `PATCH /api/admin/cities/{id}/status` بقيمة `0` أو `1` أو `2`.
2. اليوزر: مدن `status = 1` فقط.
3. الشركة: `status = 1` شغالة، `status = 2` ظاهرة و `disabled: true` (مفيش اشتراك ولا عقد).
4. متبعتوش `city_id` من كاش قديم؛ لو 400/422 حدّث القائمة.
