# دليل إضافة خدمة جديدة مع فورم الأسئلة الديناميكية

## 📋 الخطوات الكاملة

---

## ✅ **الخطوة 1: إنشاء الخدمة (Type)**

### API Endpoint:
```
POST /api/admin/types
```

### Request Body:
```json
{
  "name": {
    "en": "Moving Service",
    "de": "Umzugsservice",
    "fr": "Service de déménagement",
    "it": "Servizio di trasloco",
    "ar": "خدمة النقل"
  },
  "price": 500
}
```

### Response:
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "name": {
      "en": "Moving Service",
      "de": "Umzugsservice",
      "fr": "Service de déménagement",
      "it": "Servizio di trasloco",
      "ar": "خدمة النقل"
    },
    "price": 500,
    "created_at": "2025-12-02T18:00:00.000000Z"
  }
}
```

**ملاحظة:** احفظ `id` الخاص بالخدمة (مثلاً: `1`) - ستحتاجه في الخطوات التالية.

---

## ✅ **الخطوة 2: إضافة تفاصيل الخدمة (Type Details)**

### API Endpoint:
```
POST /api/admin/details/types
```

### Request Body (Form Data):
```
type_id: 1
service_home_icon: [file]
small_image[en]: [file]
small_image[de]: [file]
small_image[fr]: [file]
small_image[it]: [file]
main_image[en]: [file]
main_image[de]: [file]
main_image[fr]: [file]
main_image[it]: [file]
short_description[en]: "Professional moving service"
short_description[de]: "Professioneller Umzugsservice"
short_description[fr]: "Service de déménagement professionnel"
short_description[it]: "Servizio di trasloco professionale"
short_description[ar]: "خدمة نقل احترافية"
feature_header[en]: "Why Choose Us"
feature_header[de]: "Warum uns wählen"
... (جميع الحقول المطلوبة)
```

---

## ✅ **الخطوة 3: إضافة الأسئلة الديناميكية**

### 3.1 إنشاء السؤال الأول (سؤال رئيسي)

#### API Endpoint:
```
POST /api/admin/types/{type_id}/questions
```

#### مثال: سؤال "هل لديك مصعد؟"

**Request Body:**
```json
{
  "question_text": {
    "en": "Do you have an elevator?",
    "de": "Haben Sie einen Aufzug?",
    "fr": "Avez-vous un ascenseur?",
    "it": "Hai un ascensore?",
    "ar": "هل لديك مصعد؟"
  },
  "question_type": "single_choice",
  "is_required": true,
  "order": 1
}
```

**Response:**
```json
{
  "status": 201,
  "msg": "Question created successfully",
  "data": {
    "id": 1,
    "type_id": 1,
    "question_text": {...},
    "question_type": "single_choice",
    "order": 1
  }
}
```

**احفظ `id` السؤال (مثلاً: `1`)**

---

### 3.2 إضافة اختيارات للسؤال

#### API Endpoint:
```
POST /api/admin/questions/{question_id}/options
```

#### مثال: إضافة اختيارات "نعم" و "لا"

**Request Body:**
```json
{
  "option_text": {
    "en": "Yes",
    "de": "Ja",
    "fr": "Oui",
    "it": "Sì",
    "ar": "نعم"
  },
  "order": 1
}
```

**كرر العملية لإضافة "لا":**
```json
{
  "option_text": {
    "en": "No",
    "de": "Nein",
    "fr": "Non",
    "it": "No",
    "ar": "لا"
  },
  "order": 2
}
```

**احفظ `id` كل اختيار:**
- اختيار "نعم" → `id: 1`
- اختيار "لا" → `id: 2`

---

### 3.3 إنشاء سؤال متفرع (Branching)

#### مثال: إذا اختار المستخدم "نعم" → يظهر سؤال "ما نوع المصعد؟"

**API Endpoint:**
```
POST /api/admin/types/{type_id}/questions
```

**Request Body:**
```json
{
  "question_text": {
    "en": "What type of elevator?",
    "de": "Welche Art von Aufzug?",
    "fr": "Quel type d'ascenseur?",
    "it": "Che tipo di ascensore?",
    "ar": "ما نوع المصعد؟"
  },
  "question_type": "single_choice",
  "is_required": true,
  "order": 2,
  "parent_question_id": 1,
  "parent_option_id": 1
}
```

**شرح:**
- `parent_question_id: 1` → السؤال الأب (هل لديك مصعد؟)
- `parent_option_id: 1` → الاختيار الذي يسبب ظهور هذا السؤال (نعم)

---

### 3.4 إضافة اختيارات للسؤال المتفرع

**API Endpoint:**
```
POST /api/admin/questions/{question_id}/options
```

**مثال: إضافة "كهربائي" و "يدوي"**

```json
{
  "option_text": {
    "en": "Electric",
    "de": "Elektrisch",
    "fr": "Électrique",
    "it": "Elettrico",
    "ar": "كهربائي"
  },
  "order": 1
}
```

```json
{
  "option_text": {
    "en": "Manual",
    "de": "Manuell",
    "fr": "Manuel",
    "it": "Manuale",
    "ar": "يدوي"
  },
  "order": 2
}
```

---

### 3.5 إضافة سؤال آخر (سؤال رئيسي)

#### مثال: سؤال "كم عدد الطوابق؟"

**API Endpoint:**
```
POST /api/admin/types/{type_id}/questions
```

**Request Body:**
```json
{
  "question_text": {
    "en": "How many floors?",
    "de": "Wie viele Stockwerke?",
    "fr": "Combien d'étages?",
    "it": "Quanti piani?",
    "ar": "كم عدد الطوابق؟"
  },
  "question_type": "number",
  "is_required": true,
  "order": 3
}
```

**ملاحظة:** هذا سؤال رئيسي (بدون `parent_question_id`) لأنه يظهر بعد انتهاء السؤال الأول.

---

## ✅ **الخطوة 4: عرض شجرة الأسئلة (Flow Tree)**

### API Endpoint:
```
GET /api/admin/types/{type_id}/questions/flow-tree
```

### Response:
```json
{
  "status": 200,
  "msg": "Flow tree retrieved successfully",
  "data": [
    {
      "id": 1,
      "question_text": {
        "en": "Do you have an elevator?",
        "ar": "هل لديك مصعد؟"
      },
      "question_type": "single_choice",
      "order": 1,
      "options": [
        {
          "id": 1,
          "option_text": {
            "en": "Yes",
            "ar": "نعم"
          }
        },
        {
          "id": 2,
          "option_text": {
            "en": "No",
            "ar": "لا"
          }
        }
      ],
      "child_questions": [
        {
          "id": 2,
          "question_text": {
            "en": "What type of elevator?",
            "ar": "ما نوع المصعد؟"
          },
          "options": [...],
          "child_questions": []
        }
      ]
    },
    {
      "id": 3,
      "question_text": {
        "en": "How many floors?",
        "ar": "كم عدد الطوابق؟"
      },
      "order": 3,
      "child_questions": []
    }
  ]
}
```

---

## ✅ **الخطوة 5: اختبار الفورم (من جانب المستخدم)**

### 5.1 إنشاء Offer

**API Endpoint:**
```
POST /api/user/add-offer
```

**Request Body:**
```json
{
  "type_id": 1,
  "email": "user@example.com",
  "name": "Ahmed",
  "phone": "123456789",
  "lang": "ar"
}
```

**Response:**
```json
{
  "status": 201,
  "msg": "Offer created successfully",
  "data": {
    "id": 123,
    "completion_status": "draft"
  }
}
```

**احفظ `offer_id` (مثلاً: `123`)**

---

### 5.2 جلب أول سؤال

**API Endpoint:**
```
GET /api/user/offers/{offer_id}/questions/first?lang=ar
```

**Response:**
```json
{
  "status": 200,
  "msg": "First question retrieved successfully",
  "data": {
    "question": {
      "id": 1,
      "question_text": "هل لديك مصعد؟",
      "question_type": "single_choice",
      "is_required": true,
      "options": [
        {
          "id": 1,
          "option_text": "نعم"
        },
        {
          "id": 2,
          "option_text": "لا"
        }
      ]
    },
    "total_questions": 2,
    "answered_count": 0,
    "progress": {
      "answered": 0,
      "total": 2,
      "percentage": 0
    }
  }
}
```

---

### 5.3 إرسال إجابة

**API Endpoint:**
```
POST /api/user/offers/{offer_id}/answer?lang=ar
```

**Request Body:**
```json
{
  "question_id": 1,
  "option_ids": [1]
}
```

**Response:**
```json
{
  "status": 200,
  "msg": "Answer submitted successfully",
  "data": {
    "is_completed": false,
    "progress": {
      "answered": 1,
      "total": 2,
      "percentage": 50
    },
    "next_question": {
      "id": 2,
      "question_text": "ما نوع المصعد؟",
      "question_type": "single_choice",
      "options": [
        {
          "id": 3,
          "option_text": "كهربائي"
        },
        {
          "id": 4,
          "option_text": "يدوي"
        }
      ]
    }
  }
}
```

**ملاحظة:** لأن المستخدم اختار "نعم"، ظهر السؤال المتفرع "ما نوع المصعد؟"

---

### 5.4 إرسال إجابة أخرى

**Request Body:**
```json
{
  "question_id": 2,
  "option_ids": [3]
}
```

**Response:**
```json
{
  "status": 200,
  "msg": "Answer submitted successfully",
  "data": {
    "is_completed": false,
    "progress": {
      "answered": 2,
      "total": 2,
      "percentage": 100
    },
    "next_question": {
      "id": 3,
      "question_text": "كم عدد الطوابق؟",
      "question_type": "number"
    }
  }
}
```

---

### 5.5 إرسال إجابة نهائية

**Request Body:**
```json
{
  "question_id": 3,
  "answer": "5"
}
```

**Response:**
```json
{
  "status": 200,
  "msg": "Answer submitted successfully",
  "data": {
    "is_completed": true,
    "progress": {
      "answered": 3,
      "total": 3,
      "percentage": 100
    }
  }
}
```

---

### 5.6 عرض كل الإجابات

**API Endpoint:**
```
GET /api/user/offers/{offer_id}/answers?lang=ar
```

**Response:**
```json
{
  "status": 200,
  "msg": "Answers retrieved successfully",
  "data": {
    "offer_id": 123,
    "completion_status": "completed",
    "answers": [
      {
        "question_id": 1,
        "question_text": "هل لديك مصعد؟",
        "question_type": "single_choice",
        "answer_text": null,
        "selected_options": [
          {
            "id": 1,
            "option_text": "نعم"
          }
        ]
      },
      {
        "question_id": 2,
        "question_text": "ما نوع المصعد؟",
        "question_type": "single_choice",
        "answer_text": null,
        "selected_options": [
          {
            "id": 3,
            "option_text": "كهربائي"
          }
        ]
      },
      {
        "question_id": 3,
        "question_text": "كم عدد الطوابق؟",
        "question_type": "number",
        "answer_text": "5",
        "selected_options": []
      }
    ],
    "progress": {
      "answered": 3,
      "total": 3,
      "percentage": 100
    }
  }
}
```

---

## 📝 **ملخص الخطوات**

1. ✅ إنشاء الخدمة (Type) → احفظ `type_id`
2. ✅ إضافة تفاصيل الخدمة (Type Details)
3. ✅ إضافة الأسئلة:
   - إنشاء سؤال رئيسي
   - إضافة اختيارات للسؤال
   - إنشاء سؤال متفرع (Branching) إذا لزم
4. ✅ اختبار الفورم من جانب المستخدم
5. ✅ عرض شجرة الأسئلة للتأكد من البنية

---

## 🎯 **نصائح مهمة**

1. **الترتيب (Order):** استخدم `order` لترتيب الأسئلة (1, 2, 3...)
2. **Branching:** استخدم `parent_question_id` و `parent_option_id` لإنشاء التفرع
3. **الترجمة:** تأكد من إضافة جميع اللغات الخمس (en, de, fr, it, ar)
4. **أنواع الأسئلة:** 
   - `text` - نص
   - `single_choice` - اختيار واحد
   - `multi_choice` - اختيارات متعددة
   - `number` - رقم
   - `date` - تاريخ
   - `email` - بريد إلكتروني
   - `phone` - رقم هاتف

---

## 🔗 **روابط سريعة**

- إنشاء خدمة: `POST /api/admin/types`
- إضافة سؤال: `POST /api/admin/types/{type_id}/questions`
- إضافة اختيار: `POST /api/admin/questions/{question_id}/options`
- عرض الشجرة: `GET /api/admin/types/{type_id}/questions/flow-tree`
- جلب أول سؤال: `GET /api/user/offers/{offer_id}/questions/first`
- إرسال إجابة: `POST /api/user/offers/{offer_id}/answer`

