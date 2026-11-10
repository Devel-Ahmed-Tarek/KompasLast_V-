# Frontend API — Company Offer Chat

شات بين **الشركة** والـ **عميل** على الـ offers اللي الشركة اشترتها فقط.

**Base:** `/api/company`  
**Auth:** `Authorization: Bearer {token}` (Sanctum) + role = `company`  
**Response shape:**

```json
{
  "status": 200,
  "msg": "OK",
  "data": {}
}
```

> ملاحظة: `status` رقم HTTP داخل الـ JSON (200 / 201 / 403 / 422…).

---

## الفكرة للفرونت

1. من قائمة العروض المشتراة: زر **Chat** → `POST /chats/open` بـ `offer_id`
2. صفحة Inbox: `GET /chats`
3. داخل المحادثة: `GET /chats/{id}` + polling بـ `after_id`
4. إرسال: `POST /chats/{id}` بـ `{ "body": "..." }`
5. Badge في الهيدر: `GET /chats/unread`

الشات **مش يتفتح** لو الشركة ما اشترتش الـ offer.

`sender_type`:
- `customer` = العميل
- `company` = الشركة (أنت)

---

## 1) قائمة المحادثات (Inbox)

```
GET /api/company/chats
```

### Response `data`
```json
{
  "conversations": [
    {
      "conversation_id": 12,
      "offer": {
        "id": 473,
        "name": "Max Mustermann",
        "email": "max@example.com",
        "phone": "+49123456789",
        "service": "Garten- & Landschaftsbau",
        "adresse": "Möller Straße 17",
        "zipcode": "44137",
        "ort": "Dortmund",
        "date": "2026-10-16",
        "lang": "de"
      },
      "last_message": {
        "id": 88,
        "body": "Hallo, wann können Sie starten?",
        "sender_type": "customer",
        "created_at": "2026-11-08T09:25:15+00:00"
      },
      "unread_count": 1,
      "last_message_at": "2026-11-08T09:25:15+00:00"
    }
  ],
  "unread_total": 3
}
```

---

## 2) عدد الرسائل غير المقروءة (Badge)

```
GET /api/company/chats/unread
```

### Response `data`
```json
{
  "unread_total": 3
}
```

استخدمها في السايدبار / الجرس كل 30–60 ثانية.

---

## 3) فتح / إنشاء شات من صفحة عرض مشتَرى

```
POST /api/company/chats/open
```

### Body
```json
{
  "offer_id": 473
}
```

| Field | Type | Required |
|-------|------|----------|
| `offer_id` | number | نعم |

### Success `data`
```json
{
  "conversation": { "...same shape as inbox item..." },
  "messages": [
    {
      "id": 1,
      "sender_type": "customer",
      "sender_id": null,
      "body": "Hallo, wann können Sie starten?",
      "read_at": null,
      "created_at": "2026-11-08T09:25:15+00:00"
    }
  ]
}
```

### Errors
| HTTP | متى |
|------|-----|
| 403 | الشركة ما اشترتش الـ offer |
| 422 | `offer_id` ناقص/غلط |
| 404 | الـ offer مش موجود |

---

## 4) جلب رسائل محادثة

```
GET /api/company/chats/{conversationId}
GET /api/company/chats/{conversationId}?after_id=88
```

| Query | المعنى |
|-------|--------|
| بدون `after_id` | كل الرسائل + تعليمها كمقروءة |
| `after_id` | الرسائل الأحدث فقط (polling صامت، بدون mark-as-read) |

### Response `data`
```json
{
  "conversation": { "...inbox item shape..." },
  "messages": [
    {
      "id": 89,
      "sender_type": "company",
      "sender_id": 108,
      "body": "Wir können morgen kommen.",
      "read_at": null,
      "created_at": "2026-11-08T09:30:00+00:00"
    }
  ]
}
```

### Polling مقترح
كل 5–10 ثواني وأنت جوه الشات:

```
GET /api/company/chats/12?after_id={lastMessageId}
```

لو رجعت رسائل جديدة: ألحقها في UI وحدّث `lastMessageId`.

---

## 5) إرسال رسالة

```
POST /api/company/chats/{conversationId}
```

### Body
```json
{
  "body": "Wir können morgen kommen."
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `body` | string | نعم | max 5000 حرف |

### Success `data` (201)
```json
{
  "message": {
    "id": 89,
    "sender_type": "company",
    "sender_id": 108,
    "body": "Wir können morgen kommen.",
    "read_at": null,
    "created_at": "2026-11-08T09:30:00+00:00"
  },
  "conversation": { "...updated inbox item..." }
}
```

### Errors
| HTTP | متى |
|------|-----|
| 403 | الشركة مش مشترية العرض |
| 404 | المحادثة مش تابعة للشركة |
| 422 | body فاضي/طويل |

---

## UI Flow مختصر

```
[My Offers] --open chat--> POST /chats/open {offer_id}
                                |
                                v
                         Chat Screen
                     GET /chats/{id}
                     poll ?after_id=
                     POST /chats/{id} {body}

[Inbox / Messages menu] --> GET /chats
[Header badge] ----------> GET /chats/unread
```

---

## رسائل النظام (Notifications)

لما العميل يبعت رسالة، الشركة بيوصلها إشعار نوع:

- `type`: `offer_chat_message`
- `type_id`: `offer_id`

تقدر تفتح منه شاشة الشات بـ `POST /chats/open` بنفس `offer_id`.

---

## مثال fetch

```js
const API = "https://back.auftragkompass.com/api/company";
const headers = {
  Authorization: `Bearer ${token}`,
  Accept: "application/json",
  "Content-Type": "application/json",
};

// Inbox
const inbox = await fetch(`${API}/chats`, { headers }).then(r => r.json());

// Open from purchased offer
const opened = await fetch(`${API}/chats/open`, {
  method: "POST",
  headers,
  body: JSON.stringify({ offer_id: 473 }),
}).then(r => r.json());

// Send
await fetch(`${API}/chats/${opened.data.conversation.conversation_id}`, {
  method: "POST",
  headers,
  body: JSON.stringify({ body: "Wir können morgen kommen." }),
});
```

---

## Customer side (مرجع فقط)

بوابة العميل المنفصلة (Next.js tracking) بتستخدم:

```
GET  /api/user/offer-track/{token}/chats
GET  /api/user/offer-track/{token}/chats/{companyId}
POST /api/user/offer-track/{token}/chats/{companyId}
```

الشركة **مش** محتاجة التوكن ده؛ عندها الـ Sanctum token بس.
