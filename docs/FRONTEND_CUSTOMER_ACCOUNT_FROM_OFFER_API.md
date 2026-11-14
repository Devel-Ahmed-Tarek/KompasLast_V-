# Frontend API — إنشاء حساب كستمر من الـ Offer

بعد ما الكستمر يقدّم Offer، يظهر Popup: **محتاج تنشئ حساب؟**

- **لأ** → مفيش API
- **أيوه** → استدعاء الـ APIs تحت  
  الباك ينشئ الحساب ويبعت إيميل OTP لتعيين الباسورد، وبعدها الفرونت يحوّله لصفحة **Login** (مش بيرجع token من خطوة الإنشاء).

**Base:** `/api/user`  
**Auth:** مش محتاج Bearer في الخطوات دي

---

## الفلّو

```
Submit Offer
   └── Popup: Create account?
         ├── No  → end
         └── Yes → POST /auth/create-from-offer { offer_id }
                     │
                     ├── status: pending_password
                     │     → صفحة set-password (email + OTP من الإيميل)
                     │     → POST /auth/set-password
                     │     → redirect إلى login_url
                     │
                     └── status: already_exists
                           → redirect إلى login_url مباشرة
```

بعد تعيين الباسورد:

```
POST /auth/login { email, password } → token
```

---

## 1) إنشاء حساب من الـ Offer

```
POST /api/user/auth/create-from-offer
```

```json
{ "offer_id": 479 }
```

### Success `201` — حساب جديد / لسه محتاج باسورد

```json
{
  "status": 201,
  "msg": "Account created — set password via email",
  "data": {
    "status": "pending_password",
    "user_id": 55,
    "offer_id": 479,
    "email": "max@example.com",
    "set_password_url": "https://auftragkompass.de/de/set-password?email=max%40example.com",
    "login_url": "https://auftragkompass.de/de/login"
  }
}
```

### Success `200` — الحساب موجود ومتفعّل قبل كده

```json
{
  "status": 200,
  "msg": "Account already exists",
  "data": {
    "status": "already_exists",
    "user_id": 55,
    "offer_id": 479,
    "email": "max@example.com",
    "login_url": "https://auftragkompass.de/de/login",
    "set_password_url": null
  }
}
```

### Errors

| HTTP | متى |
|------|-----|
| 422 | `offer_id` غلط / الإيميل على الـ offer فاضي |
| 404 | الـ offer مش موجود |
| 409 | الإيميل مستخدم لـ company/admin |

> الإيميل بيتبعت تلقائي فيه OTP (صالح 30 دقيقة) + زر/لينك `set_password_url`.

---

## 2) تعيين الباسورد (من الإيميل / صفحة set-password)

```
POST /api/user/auth/set-password
```

```json
{
  "email": "max@example.com",
  "otp": "123456",
  "password": "Secret123!",
  "password_confirmation": "Secret123!"
}
```

### Success `200`

```json
{
  "status": 200,
  "msg": "Password set — please login",
  "data": {
    "status": "ready",
    "email": "max@example.com",
    "login_url": "https://auftragkompass.de/de/login"
  }
}
```

**مهم:** مفيش token هنا — الفرونت يعمل redirect لـ `login_url`.

---

## 3) إعادة إرسال OTP

```
POST /api/user/auth/resend-setup-otp
```

```json
{ "offer_id": 479 }
```

أو:

```json
{ "email": "max@example.com" }
```

---

## 4) تسجيل الدخول (بعد ما الباسورد يتظبط)

```
POST /api/user/auth/login
```

```json
{
  "email": "max@example.com",
  "password": "Secret123!"
}
```

### Success `200`

```json
{
  "status": 200,
  "msg": "Login success",
  "data": {
    "token": "1|xxxx",
    "user": {
      "id": 55,
      "name": "Max",
      "email": "max@example.com",
      "phone": "...",
      "role": "user"
    },
    "tracking_url": "https://customer-tracking.auftragkompass.de/de?token=1%7Cxxxx"
  }
}
```

**بعد اللوجين من الـ website:** اعمل redirect على `data.tracking_url`  
(أو ابنِ اللينك يدويًا):

```
https://customer-tracking.auftragkompass.de/{lang}?token={TOKEN}
```

بوابة التتبع هتخزّن التوكن وتفتح **آخر offer** للكستمر تلقائيًا.

لو لسه معيّنش باسورد → `403` مع `status: pending_password`.

---

## 5) عروض الكستمر المسجّل (Bearer)

```
GET /api/user/auth/me
GET /api/user/auth/my-offers
Authorization: Bearer {token}
```

`my-offers` بيرجع `latest_track_token` / `latest_track_url` + قائمة العروض.

---

## فرونت — صفحات مطلوبة

1. بعد Submit Offer: modal نعم/لا  
2. `/[lang]/set-password?email=` → فورم: OTP + password + confirm → `set-password` → push لـ login  
3. `/[lang]/login` → `auth/login`

لغة اللينكات بتتاخد من `Accept-Language` أو `?lang=`.

اختياري في `.env` للباك:

```env
CUSTOMER_LOGIN_URL=https://auftragkompass.de/de/login
CUSTOMER_SET_PASSWORD_URL=https://auftragkompass.de/de/set-password
```

---

## ملاحظات أعمال

- الحساب `role = user`
- الـ offer بيتربط بـ `offers.user_id`
- الباسورد المؤقت عشوائي لحد ما العميل يعيّن باسه من الإيميل
- `email_verified_at` بيتعلم بعد نجاح `set-password`
