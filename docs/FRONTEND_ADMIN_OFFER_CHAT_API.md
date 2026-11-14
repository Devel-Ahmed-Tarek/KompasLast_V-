# Frontend API — Admin Offer Chat (Monitoring)

الأدمن يقدر **يشوف** كل الرسايل بين العميل والشركات على كل offer، وكمان **يبعت** رسالة كـ `admin` (AuftragKompass).

**Base:** `/api/admin`  
**Auth:** `Authorization: Bearer {token}` (Sanctum) + role = `admin`

**Response shape:**
```json
{
  "status": 200,
  "msg": "OK",
  "data": {}
}
```

---

## الفكرة للفرونت

1. من تفاصيل الـ Offer → تبويب **Chats**  
   `GET /api/admin/offers/{offerId}/chats`
2. اختيار شركة → عرض الرسايل  
   `GET /api/admin/offers/{offerId}/chats/{companyId}`
3. Inbox عام (كل المحادثات في السيستم)  
   `GET /api/admin/chats`
4. فتح محادثة بالـ `conversation_id`  
   `GET /api/admin/chats/{conversationId}`

---

## 1) Inbox عام لكل المحادثات

```
GET /api/admin/chats
GET /api/admin/chats?offer_id=473
GET /api/admin/chats?company_id=108
GET /api/admin/chats?q=Max
GET /api/admin/chats?per_page=20
```

| Query | المعنى |
|-------|--------|
| `offer_id` | فلتر بعرض معيّن |
| `company_id` | فلتر بشركة |
| `q` | بحث في اسم/إيميل العميل أو الشركة أو رقم الـ offer |
| `per_page` | افتراضي 20، أقصى 100 |

### Response
نفس شكل pagination المعتاد في الأدمن:

```json
{
  "status": 200,
  "msg": "تم بنجاح",
  "data": {
    "rows": [
      {
        "conversation_id": 12,
        "offer": {
          "id": 473,
          "name": "Max Mustermann",
          "email": "max@example.com",
          "phone": "+49123...",
          "service": "Garten- & Landschaftsbau",
          "adresse": "...",
          "zipcode": "44137",
          "ort": "Dortmund",
          "date": "2026-10-16",
          "lang": "de"
        },
        "company": {
          "id": 108,
          "name": "Nassar",
          "email": "firma@example.com",
          "phone": "...",
          "img": null
        },
        "last_message": {
          "id": 88,
          "body": "Hallo, wann können Sie starten?",
          "sender_type": "customer",
          "created_at": "2026-11-08T09:25:15+00:00"
        },
        "unread_count": 0,
        "messages_count": 4,
        "last_message_at": "2026-11-08T09:25:15+00:00"
      }
    ],
    "paginationLinks": {
      "currentPages": 1,
      "perPage": 1,
      "total": 12
    }
  }
}
```

`sender_type`: `customer` | `company`

---

## 2) كل شركات الـ Offer + حالة الشات

من صفحة تفاصيل العرض:

```
GET /api/admin/offers/{offerId}/chats
```

يرجّع **كل الشركات اللي اشترت** العرض، حتى لو لسه ما فيش رسايل.

### Response `data`
```json
{
  "offer": {
    "id": 473,
    "name": "Max Mustermann",
    "email": "max@example.com",
    "phone": "+49123...",
    "service": "Garten- & Landschaftsbau",
    "ort": "Dortmund",
    "lang": "de"
  },
  "conversations": [
    {
      "conversation_id": 12,
      "has_messages": true,
      "messages_count": 4,
      "company": {
        "id": 108,
        "name": "Nassar",
        "email": "firma@example.com",
        "phone": "...",
        "img": null
      },
      "last_message": {
        "id": 88,
        "body": "Hallo...",
        "sender_type": "customer",
        "created_at": "2026-11-08T09:25:15+00:00"
      },
      "last_message_at": "2026-11-08T09:25:15+00:00"
    },
    {
      "conversation_id": null,
      "has_messages": false,
      "messages_count": 0,
      "company": {
        "id": 120,
        "name": "Other GmbH",
        "email": "...",
        "phone": "...",
        "img": null
      },
      "last_message": null,
      "last_message_at": null
    }
  ],
  "total": 2
}
```

---

## 3) رسايل شركة معيّنة على Offer

```
GET /api/admin/offers/{offerId}/chats/{companyId}
GET /api/admin/offers/{offerId}/chats/{companyId}?after_id=88
```

### Response `data`
```json
{
  "offer": { "id": 473, "name": "Max Mustermann", "...": "..." },
  "company": { "id": 108, "name": "Nassar", "...": "..." },
  "conversation_id": 12,
  "messages_count": 4,
  "messages": [
    {
      "id": 1,
      "sender_type": "customer",
      "sender_id": null,
      "body": "Hallo, wann können Sie starten?",
      "read_at": null,
      "created_at": "2026-11-08T09:25:15+00:00"
    },
    {
      "id": 2,
      "sender_type": "company",
      "sender_id": 108,
      "body": "Wir können morgen kommen.",
      "read_at": "2026-11-08T09:40:00+00:00",
      "created_at": "2026-11-08T09:30:00+00:00"
    }
  ]
}
```

---

## 4) فتح محادثة بـ conversation_id

```
GET /api/admin/chats/{conversationId}
GET /api/admin/chats/{conversationId}?after_id=88
```

### Response `data`
```json
{
  "conversation": {
    "conversation_id": 12,
    "offer": { "...": "..." },
    "company": { "...": "..." },
    "last_message": { "...": "..." },
    "messages_count": 4,
    "last_message_at": "..."
  },
  "messages": [ { "...": "..." } ]
}
```

---

## 5) إحصائيات الشات

```
GET /api/admin/chats/stats
```

```json
{
  "conversations_total": 40,
  "conversations_with_messages": 18,
  "messages_last_24h": 7
}
```

---

## 6) إرسال رسالة من الأدمن

```
POST /api/admin/chats/{conversationId}
```

```json
{ "body": "Wir melden uns gleich…" }
```

Success `201` — نفس شكل الرسالة، مع `sender_type: "admin"`.

---

## 7) Estimated quotes (قراءة)

```
GET /api/admin/estimated-quotes?offer_id=&company_id=&per_page=20
GET /api/admin/offers/{offerId}/estimated-quotes
```

---

## 8) Credit notes (إنشاء + قائمة)

```
GET  /api/admin/credit-notes?company_id=&status=all&per_page=20
POST /api/admin/credit-notes
GET  /api/admin/credit-notes/{id}
```

Body لإنشاء:

```json
{
  "company_id": 108,
  "type": "refund",
  "amount": 25,
  "reason": "Partial refund",
  "offer_id": 473,
  "status": "completed",
  "apply_to_wallet": true
}
```

---

## UI Flow مقترح

```
Admin Offer Details
   └── Tab: Chats
         GET /offers/{id}/chats
              │
              ├── Company A → GET /offers/{id}/chats/{companyA}
              │                 └── POST /chats/{conversationId} (admin reply)
              └── Company B → empty state

Admin Menu: All Chats
   └── GET /chats?q=...
         └── click row → GET /chats/{conversationId}
                       → POST /chats/{conversationId}
```

---

## ملاحظات

- `sender_type` صار: `customer` | `company` | `admin`.
- لو شركة اشترت ومافيش شات لسه: `conversation_id = null` و `messages = []` — لازم الشركة/العميل يفتحوا الشات أول مرة، أو الأدمن يفتح محادثة موجودة فقط.
- `after_id` للـ polling.
