# Frontend API — User (العميل / الموقع)

توثيق الـ APIs المتعلقة بالموقع عند إنشاء عرض.

**Base:** `/api/user`  
**Auth:** غير مطلوب لمعظم الـ endpoints دي

---

## الفكرة للفرونت (مهم)

العميل يقدر يحدد موقع العرض بـ **واحد من مسارين**:

| المسار | إيه اللي بيتبعت | إيه اللي بيحصل |
|--------|------------------|----------------|
| **A — Select** | `country_id` + `city_id` | المدينة أو البلدية تتحفظ زي ما اختارها |
| **B — GPS / إحداثيات** | `latitude` + `longitude` | النظام يلاقي **أقرب مدينة/بلدية** ويحط `country_id` + `city_id` |

**Select الموصى به:** دولة → ولاية (Bundesland) → مدينة أو بلدية.

```
GET /api/user/states?country_id=1&lang=de
GET /api/user/states/{state_id}/places?place_type=city
GET /api/user/states/{state_id}/places?place_type=municipality
GET /api/user/cities/by-country/{country_id}?state_id={id}&place_type=municipality
```

`place_type`:
- `city` = Stadt
- `municipality` = Gemeinde

بدون `state_id`، `/cities/by-country` بيرجع **المدن فقط** مش كل البلديات.

### قواعد إلزامية

1. لو **مفيش** `latitude` و `longitude` → لازم `country_id` + `city_id` من الـ select
2. لو بعت `latitude` لازم تبعت `longitude` معاها (والعكس)
3. تقدر كمان تبعت **الاتنين معاً**: select + إحداثيات  
   - المدينة من الـ select تُحفظ  
   - الإحداثيات تُحفظ لتحسين التوزيع والشوب والبيع الديناميكي

---

## 1) جلب الدول

```
GET /api/user/countries?lang=de
```

### Response
```json
{
  "status": "success",
  "data": [
    { "id": 1, "name": "Deutschland" }
  ]
}
```

---

## 2) جلب المدن حسب الدولة

```
GET /api/user/cities/by-country/{country_id}?lang=de
```

### Response
```json
{
  "status": "success",
  "data": {
    "country": { "id": 1, "name": "Deutschland" },
    "cities": [
      { "id": 5, "name": "Berlin" },
      { "id": 6, "name": "Brandenburg" }
    ]
  }
}
```

---

## 3) إنشاء عرض — حقول الموقع الجديدة

عند إرسال العرض (فورم عادي أو questionnaire) أضف:

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `country_id` | number | لو مفيش lat/lng | من الـ select |
| `city_id` | number | لو مفيش lat/lng | لازم تبع نفس الدولة |
| `latitude` | number | لو مفيش city select | `-90` … `90` |
| `longitude` | number | لو مفيش city select | `-180` … `180` |

### مثال A — Select فقط
```json
{
  "type_id": 1,
  "country_id": 1,
  "city_id": 6,
  "name": "Max Mustermann",
  "email": "max@example.com",
  "phone": "+49123456789",
  "lang": "de"
}
```

### مثال B — إحداثيات فقط (النظام يختار أقرب مدينة)
```json
{
  "type_id": 1,
  "latitude": 52.5200,
  "longitude": 13.4050,
  "name": "Max Mustermann",
  "email": "max@example.com",
  "phone": "+49123456789",
  "lang": "de"
}
```

### مثال C — Select + إحداثيات (أدق للتوزيع)
```json
{
  "type_id": 1,
  "country_id": 1,
  "city_id": 5,
  "latitude": 52.5100,
  "longitude": 13.3900,
  "name": "Max Mustermann",
  "email": "max@example.com",
  "phone": "+49123456789",
  "lang": "de"
}
```

### أخطاء محتملة (422)
```json
{
  "location": ["country_id and city_id are required when coordinates are not provided."]
}
```
```json
{
  "location": ["Both latitude and longitude are required together."]
}
```
```json
{
  "location": ["No nearby city with coordinates was found."]
}
```

---

## 4) UI مقترح للعميل

**خيار 1 (كلاسيك):**  
Select دولة → Select مدينة

**خيار 2 (GPS):**  
زر "استخدم موقعي الحالي" → المتصفح يديك `latitude` / `longitude` → تبعتهم بدون ما تختاره مدينة

**خيار 3 (مختلط):**  
Select مدينة + زر GPS يضيف الإحداثيات الدقيقة (أفضل للتوزيع على الشركات)

---

## 5) تأكيد العرض

```
GET /api/user/offers/confirm/{token}
```

من لينك الإيميل — زي ما هو، مفيش تغيير.

---

## Checklist فرونت — User

- [ ] مسار Select: `country_id` + `city_id` إجباري لو مفيش GPS
- [ ] مسار GPS: حقول `latitude` + `longitude` (أو زر geolocation)
- [ ] Validation فرونت: إما select كامل أو lat+lng مع بعض
- [ ] (اختياري) السماح بالاتنين معاً لدقة أعلى
- [ ] صفحة confirm-offer زي ما هي

---

## أثر الميزة على العميل

- واجهة إضافية اختيارية (GPS)
- لو استخدم GPS: النظام يحدد أقرب مدينة لوحده
- توزيع أفضل على الشركات (شوب + شراء تلقائي) لما الإحداثيات موجودة
