# Frontend API — User (العميل / الموقع)

توثيق الـ APIs المتعلقة بالموقع (دولة + مدينة) عند إنشاء عرض من الموقع.

**Base:** `/api/user`  
**Auth:** غير مطلوب لمعظم الـ endpoints دي

---

## الفكرة للفرونت

العميل يختار **دولة** ثم **مدينة** لموقع العرض — زي ما هو.

مفيش `radius_km` عند العميل.  
النطاق يخص الشركة فقط (لو الشركة قريبة من مدينة العميل، العرض يوصلها).

---

## 1) جلب الدول

```
GET /api/user/countries?lang=de
```

أو Header: `Accept-Language: de`

**اللغات:** `en` | `ar` | `de` | `fr` | `it`

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

> ملاحظة: الـ response بيرجع `id` و `name` بس (مش lat/lng) — كفاية للـ select في الفورم.

---

## 3) إنشاء عرض (موقع العرض)

عند إرسال العرض (فورم عادي أو questionnaire) لازم تبعت:

| Field | Type | Required |
|-------|------|----------|
| `country_id` | number | نعم |
| `city_id` | number | نعم |
| باقي بيانات العرض | — | حسب الفورم |

**مهم:** `city_id` لازم تبع نفس `country_id`.

### مثال
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

بعد الإرسال: العميل يستلم إيميل تأكيد → يضغط الرابط → العرض يتأكد.

---

## 4) تأكيد العرض

```
GET /api/user/offers/confirm/{token}
```

عادةً بيجي من لينك الإيميل على الموقع:
`https://auftragkompass.de/{lang}/confirm-offer/?token=...`

الفرونت بياخد الـ `token` من الـ query ويضرب الـ API فوق.

---

## Checklist فرونت — User

- [ ] Select دولة من `/countries`
- [ ] بعد اختيار الدولة → تحميل مدنها من `/cities/by-country/{id}`
- [ ] إرسال `country_id` + `city_id` مع فورم العرض
- [ ] صفحة confirm-offer تقرأ `token` وتستدعي confirm API

---

## مفيش تغيير مطلوب بخصوص Radius

العميل مش بيختار نطاق. كل اللي محتاجه: اختيار موقع العرض بدقة (دولة + مدينة).
