# Question Types Documentation

## نظرة عامة

نظام الأسئلة الديناميكية يدعم **7 أنواع** من الأسئلة، بالإضافة إلى إمكانية رفع الملفات. كل نوع له طريقة معالجة مختلفة في الـ Frontend والـ Backend.

---

## أنواع الأسئلة المتاحة

### 1. **text** - نص حر
**الوصف:** سؤال يحتاج إجابة نصية حرة من المستخدم.

**الخصائص:**
- ✅ يدعم رفع الملفات (`allows_file_upload`)
- ✅ يمكن أن يكون إجباري (`is_required`)
- ❌ لا يحتاج `options` (خيارات)

**مثال الاستخدام:**
```json
{
  "question_id": 1,
  "question_text": {
    "en": "What is your address?",
    "de": "Wie lautet Ihre Adresse?",
    "fr": "Quelle est votre adresse?",
    "it": "Qual è il tuo indirizzo?",
    "ar": "ما هو عنوانك؟"
  },
  "question_type": "text",
  "is_required": true,
  "allows_file_upload": false
}
```

**Request Body للإجابة:**
```json
{
  "question_id": 1,
  "answer": "123 Main Street, New York, NY 10001"
}
```

**Response Example:**
```json
{
  "id": 1,
  "question_text": "What is your address?",
  "question_type": "text",
  "is_required": true,
  "allows_file_upload": false,
  "options": []
}
```

---

### 2. **single_choice** - اختيار واحد
**الوصف:** سؤال يحتاج اختيار خيار واحد فقط من قائمة الخيارات.

**الخصائص:**
- ✅ يحتاج `options` (خيارات)
- ✅ يدعم رفع الملفات (`allows_file_upload`)
- ✅ يمكن أن يكون إجباري (`is_required`)
- ✅ يدعم Branching (التفرع) - يمكن أن يظهر سؤال فرعي بناءً على الخيار المختار

**مثال الاستخدام:**
```json
{
  "question_id": 2,
  "question_text": {
    "en": "Do you have an elevator?",
    "de": "Haben Sie einen Aufzug?",
    "fr": "Avez-vous un ascenseur?",
    "it": "Hai un ascensore?",
    "ar": "هل لديك مصعد؟"
  },
  "question_type": "single_choice",
  "is_required": true,
  "allows_file_upload": false,
  "options": [
    {
      "id": 1,
      "option_text": {
        "en": "Yes",
        "de": "Ja",
        "fr": "Oui",
        "it": "Sì",
        "ar": "نعم"
      },
      "order": 1
    },
    {
      "id": 2,
      "option_text": {
        "en": "No",
        "de": "Nein",
        "fr": "Non",
        "it": "No",
        "ar": "لا"
      },
      "order": 2
    }
  ]
}
```

**Request Body للإجابة:**
```json
{
  "question_id": 2,
  "option_ids": [1]  // اختيار واحد فقط
}
```

**Response Example:**
```json
{
  "id": 2,
  "question_text": "Do you have an elevator?",
  "question_type": "single_choice",
  "is_required": true,
  "options": [
    {
      "id": 1,
      "option_text": "Yes",
      "order": 1
    },
    {
      "id": 2,
      "option_text": "No",
      "order": 2
    }
  ],
  "child_questions": [
    {
      "id": 3,
      "question_text": "What floor is the elevator on?",
      "parent_option_id": 1,
      "triggered_by_option": {
        "id": 1,
        "option_text": "Yes"
      }
    }
  ]
}
```

---

### 3. **multi_choice** - اختيارات متعددة
**الوصف:** سؤال يحتاج اختيار أكثر من خيار واحد من قائمة الخيارات.

**الخصائص:**
- ✅ يحتاج `options` (خيارات)
- ✅ يدعم رفع الملفات (`allows_file_upload`)
- ✅ يمكن أن يكون إجباري (`is_required`)
- ✅ يمكن اختيار أكثر من خيار واحد

**مثال الاستخدام:**
```json
{
  "question_id": 3,
  "question_text": {
    "en": "What services do you need?",
    "de": "Welche Dienstleistungen benötigen Sie?",
    "fr": "Quels services avez-vous besoin?",
    "it": "Di quali servizi hai bisogno?",
    "ar": "ما هي الخدمات التي تحتاجها؟"
  },
  "question_type": "multi_choice",
  "is_required": true,
  "options": [
    {
      "id": 3,
      "option_text": {
        "en": "Packing",
        "de": "Verpackung",
        "fr": "Emballage",
        "it": "Imballaggio",
        "ar": "التعبئة"
      },
      "order": 1
    },
    {
      "id": 4,
      "option_text": {
        "en": "Moving",
        "de": "Umzug",
        "fr": "Déménagement",
        "it": "Trasloco",
        "ar": "النقل"
      },
      "order": 2
    },
    {
      "id": 5,
      "option_text": {
        "en": "Storage",
        "de": "Lagerung",
        "fr": "Stockage",
        "it": "Magazzinaggio",
        "ar": "التخزين"
      },
      "order": 3
    }
  ]
}
```

**Request Body للإجابة:**
```json
{
  "question_id": 3,
  "option_ids": [3, 4, 5]  // اختيارات متعددة
}
```

**Response Example:**
```json
{
  "id": 3,
  "question_text": "What services do you need?",
  "question_type": "multi_choice",
  "is_required": true,
  "options": [
    {
      "id": 3,
      "option_text": "Packing",
      "order": 1
    },
    {
      "id": 4,
      "option_text": "Moving",
      "order": 2
    },
    {
      "id": 5,
      "option_text": "Storage",
      "order": 3
    }
  ]
}
```

---

### 4. **number** - رقم
**الوصف:** سؤال يحتاج إدخال رقم فقط.

**الخصائص:**
- ✅ يدعم رفع الملفات (`allows_file_upload`)
- ✅ يمكن أن يكون إجباري (`is_required`)
- ❌ لا يحتاج `options` (خيارات)
- ✅ يجب التحقق من أن القيمة رقم في الـ Frontend

**مثال الاستخدام:**
```json
{
  "question_id": 4,
  "question_text": {
    "en": "How many rooms do you have?",
    "de": "Wie viele Zimmer haben Sie?",
    "fr": "Combien de pièces avez-vous?",
    "it": "Quante stanze hai?",
    "ar": "كم عدد الغرف لديك؟"
  },
  "question_type": "number",
  "is_required": true,
  "allows_file_upload": false
}
```

**Request Body للإجابة:**
```json
{
  "question_id": 4,
  "answer": "5"  // أو 5 (رقم)
}
```

**Response Example:**
```json
{
  "id": 4,
  "question_text": "How many rooms do you have?",
  "question_type": "number",
  "is_required": true,
  "options": []
}
```

---

### 5. **date** - تاريخ
**الوصف:** سؤال يحتاج إدخال تاريخ.

**الخصائص:**
- ✅ يدعم رفع الملفات (`allows_file_upload`)
- ✅ يمكن أن يكون إجباري (`is_required`)
- ❌ لا يحتاج `options` (خيارات)
- ✅ يجب التحقق من صحة التاريخ في الـ Frontend

**مثال الاستخدام:**
```json
{
  "question_id": 5,
  "question_text": {
    "en": "When do you need the service?",
    "de": "Wann benötigen Sie den Service?",
    "fr": "Quand avez-vous besoin du service?",
    "it": "Quando hai bisogno del servizio?",
    "ar": "متى تحتاج الخدمة؟"
  },
  "question_type": "date",
  "is_required": true,
  "allows_file_upload": false
}
```

**Request Body للإجابة:**
```json
{
  "question_id": 5,
  "answer": "2025-12-31"  // YYYY-MM-DD format
}
```

**Response Example:**
```json
{
  "id": 5,
  "question_text": "When do you need the service?",
  "question_type": "date",
  "is_required": true,
  "options": []
}
```

---

### 6. **email** - بريد إلكتروني
**الوصف:** سؤال يحتاج إدخال بريد إلكتروني صحيح.

**الخصائص:**
- ✅ يدعم رفع الملفات (`allows_file_upload`)
- ✅ يمكن أن يكون إجباري (`is_required`)
- ❌ لا يحتاج `options` (خيارات)
- ✅ يجب التحقق من صحة البريد الإلكتروني في الـ Frontend

**مثال الاستخدام:**
```json
{
  "question_id": 6,
  "question_text": {
    "en": "What is your email address?",
    "de": "Wie lautet Ihre E-Mail-Adresse?",
    "fr": "Quelle est votre adresse e-mail?",
    "it": "Qual è il tuo indirizzo email?",
    "ar": "ما هو عنوان بريدك الإلكتروني؟"
  },
  "question_type": "email",
  "is_required": true,
  "allows_file_upload": false
}
```

**Request Body للإجابة:**
```json
{
  "question_id": 6,
  "answer": "user@example.com"
}
```

**Response Example:**
```json
{
  "id": 6,
  "question_text": "What is your email address?",
  "question_type": "email",
  "is_required": true,
  "options": []
}
```

---

### 7. **phone** - رقم هاتف
**الوصف:** سؤال يحتاج إدخال رقم هاتف.

**الخصائص:**
- ✅ يدعم رفع الملفات (`allows_file_upload`)
- ✅ يمكن أن يكون إجباري (`is_required`)
- ❌ لا يحتاج `options` (خيارات)
- ✅ يجب التحقق من صحة رقم الهاتف في الـ Frontend

**مثال الاستخدام:**
```json
{
  "question_id": 7,
  "question_text": {
    "en": "What is your phone number?",
    "de": "Wie lautet Ihre Telefonnummer?",
    "fr": "Quel est votre numéro de téléphone?",
    "it": "Qual è il tuo numero di telefono?",
    "ar": "ما هو رقم هاتفك؟"
  },
  "question_type": "phone",
  "is_required": true,
  "allows_file_upload": false
}
```

**Request Body للإجابة:**
```json
{
  "question_id": 7,
  "answer": "+1234567890"
}
```

**Response Example:**
```json
{
  "id": 7,
  "question_text": "What is your phone number?",
  "question_type": "phone",
  "is_required": true,
  "options": []
}
```

---

## رفع الملفات (File Upload)

### الإعدادات

يمكن تفعيل رفع الملفات لأي نوع سؤال باستخدام الإعدادات التالية:

```json
{
  "allows_file_upload": true,
  "allowed_file_types": ["image", "video", "document"],  // أو "image,video,document"
  "max_files": 5,
  "max_file_size": 10  // بالـ MB
}
```

### أنواع الملفات المدعومة

- **image**: الصور (jpg, png, gif, etc.)
- **video**: الفيديوهات (mp4, avi, mov, etc.)
- **document**: المستندات (pdf, doc, docx, etc.)

### مثال سؤال مع رفع ملفات

```json
{
  "question_id": 8,
  "question_text": {
    "en": "Do you have photos of your items?",
    "de": "Haben Sie Fotos Ihrer Gegenstände?",
    "fr": "Avez-vous des photos de vos articles?",
    "it": "Hai foto dei tuoi oggetti?",
    "ar": "هل لديك صور لأغراضك؟"
  },
  "question_type": "text",
  "is_required": false,
  "allows_file_upload": true,
  "allowed_file_types": ["image", "video"],
  "max_files": 5,
  "max_file_size": 10
}
```

### Request Body مع ملفات

#### طريقة 1: رفع الملفات مع الإجابة (multipart/form-data)

**JavaScript (Fetch API):**
```javascript
const formData = new FormData();
formData.append('question_id', 8);
formData.append('answer', 'Yes, I have photos');
formData.append('option_ids[]', 1);
formData.append('option_ids[]', 2); // إذا كان multi_choice
formData.append('files[]', file1);
formData.append('files[]', file2);

fetch(`/api/user/offers/${offerId}/answer?lang=en`, {
  method: 'POST',
  body: formData
  // لا تضيف Content-Type header، المتصفح سيفعل ذلك تلقائياً
});
```

**JavaScript (Axios):**
```javascript
const formData = new FormData();
formData.append('question_id', 8);
formData.append('answer', 'Yes, I have photos');
formData.append('option_ids[]', 1);
formData.append('files[]', file1);
formData.append('files[]', file2);

axios.post(`/api/user/offers/${offerId}/answer?lang=en`, formData, {
  headers: {
    'Content-Type': 'multipart/form-data'
  }
});
```

**React Example:**
```javascript
const handleSubmit = async (e) => {
  e.preventDefault();
  
  const formData = new FormData();
  formData.append('question_id', question.id);
  
  // إضافة الإجابة النصية
  if (textAnswer) {
    formData.append('answer', textAnswer);
  }
  
  // إضافة الخيارات المختارة
  if (selectedOptions.length > 0) {
    selectedOptions.forEach(optionId => {
      formData.append('option_ids[]', optionId);
    });
  }
  
  // إضافة الملفات
  if (files.length > 0) {
    files.forEach(file => {
      formData.append('files[]', file);
    });
  }
  
  try {
    const response = await fetch(`/api/user/offers/${offerId}/answer?lang=en`, {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    console.log('Success:', data);
  } catch (error) {
    console.error('Error:', error);
  }
};
```

**Postman:**
1. اختر **POST** method
2. اختر **Body** tab
3. اختر **form-data**
4. أضف الحقول:
   - `question_id`: `8` (Type: Text)
   - `answer`: `Yes, I have photos` (Type: Text)
   - `option_ids[]`: `1` (Type: Text)
   - `option_ids[]`: `2` (Type: Text) - إذا كان multi_choice
   - `files[]`: اختر ملف (Type: File)
   - `files[]`: اختر ملف آخر (Type: File)

**cURL:**
```bash
curl -X POST "https://backend.kompassumzug.ch/api/user/offers/1/answer?lang=en" \
  -F "question_id=8" \
  -F "answer=Yes, I have photos" \
  -F "option_ids[]=1" \
  -F "option_ids[]=2" \
  -F "files[]=@/path/to/file1.jpg" \
  -F "files[]=@/path/to/file2.jpg"
```

#### طريقة 2: رفع الملفات منفصلة (POST /api/user/offers/{offer_id}/answers/{answer_id}/files)

**JavaScript:**
```javascript
const formData = new FormData();
files.forEach(file => {
  formData.append('files[]', file);
});

fetch(`/api/user/offers/${offerId}/answers/${answerId}/files`, {
  method: 'POST',
  body: formData
});
```

**React Example:**
```javascript
const uploadFiles = async (answerId, files) => {
  const formData = new FormData();
  
  files.forEach(file => {
    formData.append('files[]', file);
  });
  
  try {
    const response = await fetch(
      `/api/user/offers/${offerId}/answers/${answerId}/files`,
      {
        method: 'POST',
        body: formData
      }
    );
    
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error uploading files:', error);
    throw error;
  }
};
```

### Response مع ملفات

```json
{
  "id": 8,
  "question_text": "Do you have photos of your items?",
  "question_type": "text",
  "allows_file_upload": true,
  "allowed_file_types": ["image", "video"],
  "max_files": 5,
  "max_file_size": 10,
  "files": [
    {
      "id": 1,
      "file_name": "photo1.jpg",
      "file_type": "image",
      "file_url": "https://example.com/uploads/photo1.jpg",
      "file_size": 1024000
    },
    {
      "id": 2,
      "file_name": "photo2.jpg",
      "file_type": "image",
      "file_url": "https://example.com/uploads/photo2.jpg",
      "file_size": 2048000
    }
  ]
}
```

---

## Branching (التفرع)

### ما هو Branching؟

Branching هو إمكانية إظهار سؤال فرعي بناءً على إجابة سؤال سابق. على سبيل المثال:
- إذا أجاب المستخدم "نعم" على سؤال "هل لديك مصعد؟"، يظهر سؤال "في أي طابق المصعد؟"
- إذا أجاب "لا"، لا يظهر السؤال الفرعي

### كيفية إعداد Branching

```json
{
  "question_id": 9,
  "question_text": {
    "en": "What floor is the elevator on?",
    "de": "In welchem Stockwerk ist der Aufzug?",
    "fr": "À quel étage est l'ascenseur?",
    "it": "A quale piano è l'ascensore?",
    "ar": "في أي طابق المصعد؟"
  },
  "question_type": "number",
  "is_required": true,
  "parent_question_id": 2,  // السؤال الأب
  "parent_option_id": 1     // الخيار الذي يظهر هذا السؤال (Yes)
}
```

### Response مع Branching

```json
{
  "id": 2,
  "question_text": "Do you have an elevator?",
  "question_type": "single_choice",
  "options": [
    {
      "id": 1,
      "option_text": "Yes"
    },
    {
      "id": 2,
      "option_text": "No"
    }
  ],
  "child_questions": [
    {
      "id": 9,
      "question_text": "What floor is the elevator on?",
      "question_type": "number",
      "parent_question_id": 2,
      "parent_option_id": 1,
      "triggered_by_option": {
        "id": 1,
        "option_text": "Yes"
      }
    }
  ]
}
```

---


## كيفية إرسال Form-Data (Multipart/Form-Data)

### نظرة عامة

عند إرسال البيانات مع الملفات، يجب استخدام **multipart/form-data** بدلاً من JSON. هذا القسم يشرح كيفية إرسال form-data لكل API endpoint.

---

### 1. إرسال إجابة مع ملفات

**Endpoint:** `POST /api/user/offers/{offer_id}/answer`

#### JavaScript (Fetch API)
```javascript
const formData = new FormData();

// البيانات الأساسية
formData.append('question_id', questionId);
formData.append('answer', textAnswer); // إذا كان السؤال يحتاج إجابة نصية

// الخيارات (إذا كان single_choice أو multi_choice)
if (selectedOptions && selectedOptions.length > 0) {
  selectedOptions.forEach(optionId => {
    formData.append('option_ids[]', optionId);
  });
}

// الملفات (إذا كان السؤال يسمح برفع الملفات)
if (files && files.length > 0) {
  files.forEach(file => {
    formData.append('files[]', file);
  });
}

// إرسال الطلب
fetch(`/api/user/offers/${offerId}/answer?lang=en`, {
  method: 'POST',
  body: formData
  // لا تضيف Content-Type header - المتصفح سيفعل ذلك تلقائياً
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));
```

#### JavaScript (Axios)
```javascript
const formData = new FormData();
formData.append('question_id', questionId);
formData.append('answer', textAnswer);

selectedOptions.forEach(optionId => {
  formData.append('option_ids[]', optionId);
});

files.forEach(file => {
  formData.append('files[]', file);
});

axios.post(`/api/user/offers/${offerId}/answer?lang=en`, formData, {
  headers: {
    'Content-Type': 'multipart/form-data'
  }
})
.then(response => console.log('Success:', response.data))
.catch(error => console.error('Error:', error));
```

#### React Component Example
```javascript
import React, { useState } from 'react';

function QuestionForm({ question, offerId }) {
  const [textAnswer, setTextAnswer] = useState('');
  const [selectedOptions, setSelectedOptions] = useState([]);
  const [files, setFiles] = useState([]);

  const handleSubmit = async (e) => {
    e.preventDefault();

    const formData = new FormData();
    formData.append('question_id', question.id);

    // إضافة الإجابة النصية
    if (textAnswer) {
      formData.append('answer', textAnswer);
    }

    // إضافة الخيارات
    selectedOptions.forEach(optionId => {
      formData.append('option_ids[]', optionId);
    });

    // إضافة الملفات
    files.forEach(file => {
      formData.append('files[]', file);
    });

    try {
      const response = await fetch(
        `/api/user/offers/${offerId}/answer?lang=en`,
        {
          method: 'POST',
          body: formData
        }
      );

      const data = await response.json();
      console.log('Success:', data);
    } catch (error) {
      console.error('Error:', error);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields here */}
    </form>
  );
}
```

#### Postman Configuration
1. اختر **POST** method
2. URL: `/api/user/offers/{offer_id}/answer?lang=en`
3. اختر **Body** tab
4. اختر **form-data**
5. أضف الحقول:
   - `question_id`: `1` (Type: **Text**)
   - `answer`: `My answer text` (Type: **Text**) - Optional
   - `option_ids[]`: `1` (Type: **Text**) - Optional
   - `option_ids[]`: `2` (Type: **Text**) - Optional (لـ multi_choice)
   - `files[]`: اختر ملف (Type: **File**) - Optional
   - `files[]`: اختر ملف آخر (Type: **File**) - Optional

#### cURL Command
```bash
curl -X POST "https://backend.kompassumzug.ch/api/user/offers/1/answer?lang=en" \
  -F "question_id=1" \
  -F "answer=My answer text" \
  -F "option_ids[]=1" \
  -F "option_ids[]=2" \
  -F "files[]=@/path/to/file1.jpg" \
  -F "files[]=@/path/to/file2.jpg"
```

---

### 2. رفع ملفات منفصلة

**Endpoint:** `POST /api/user/offers/{offer_id}/answers/{answer_id}/files`

#### JavaScript
```javascript
const uploadFiles = async (offerId, answerId, files) => {
  const formData = new FormData();
  
  files.forEach(file => {
    formData.append('files[]', file);
  });

  try {
    const response = await fetch(
      `/api/user/offers/${offerId}/answers/${answerId}/files`,
      {
        method: 'POST',
        body: formData
      }
    );

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error uploading files:', error);
    throw error;
  }
};
```

#### Postman Configuration
1. اختر **POST** method
2. URL: `/api/user/offers/{offer_id}/answers/{answer_id}/files`
3. اختر **Body** tab
4. اختر **form-data**
5. أضف الحقول:
   - `files[]`: اختر ملف (Type: **File**)
   - `files[]`: اختر ملف آخر (Type: **File**)

---

### 3. إرسال الفورم الكامل (Submit Offer Form)

**Endpoint:** `POST /api/user/offers/submit-form`

**ملاحظة:** هذا الـ API يدعم **JSON** فقط، وليس form-data. إذا كنت تريد إرسال ملفات، يجب إرسالها منفصلة بعد إنشاء الـ Offer.

#### JavaScript (JSON)
```javascript
const submitOfferForm = async (formData) => {
  const response = await fetch('/api/user/offers/submit-form?lang=en', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      type_id: 1,
      name: 'John Doe',
      email: 'john@example.com',
      phone: '+1234567890',
      answers: [
        {
          question_id: 1,
          answer: 'My answer',
          option_ids: [1, 2]
        }
      ]
    })
  });

  const data = await response.json();
  return data;
};
```

---

### 4. أمثلة عملية لكل نوع سؤال

#### مثال 1: سؤال نصي (text) بدون ملفات
```javascript
const formData = new FormData();
formData.append('question_id', 1);
formData.append('answer', '123 Main Street');

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

#### مثال 2: سؤال نصي (text) مع ملفات
```javascript
const formData = new FormData();
formData.append('question_id', 8);
formData.append('answer', 'Yes, I have photos');
formData.append('files[]', file1);
formData.append('files[]', file2);

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

#### مثال 3: سؤال اختيار واحد (single_choice)
```javascript
const formData = new FormData();
formData.append('question_id', 2);
formData.append('option_ids[]', 1); // اختيار واحد فقط

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

#### مثال 4: سؤال اختيارات متعددة (multi_choice)
```javascript
const formData = new FormData();
formData.append('question_id', 3);
formData.append('option_ids[]', 1);
formData.append('option_ids[]', 2);
formData.append('option_ids[]', 3);

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

#### مثال 5: سؤال رقم (number)
```javascript
const formData = new FormData();
formData.append('question_id', 4);
formData.append('answer', '5');

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

#### مثال 6: سؤال تاريخ (date)
```javascript
const formData = new FormData();
formData.append('question_id', 5);
formData.append('answer', '2025-12-31'); // YYYY-MM-DD format

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

#### مثال 7: سؤال بريد إلكتروني (email)
```javascript
const formData = new FormData();
formData.append('question_id', 6);
formData.append('answer', 'user@example.com');

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

#### مثال 8: سؤال رقم هاتف (phone)
```javascript
const formData = new FormData();
formData.append('question_id', 7);
formData.append('answer', '+1234567890');

fetch(`/api/user/offers/${offerId}/answer`, {
  method: 'POST',
  body: formData
});
```

---

### 5. معالجة الملفات في React

```javascript
import React, { useState } from 'react';

function FileUpload({ question, offerId }) {
  const [selectedFiles, setSelectedFiles] = useState([]);

  const handleFileChange = (e) => {
    const files = Array.from(e.target.files);
    
    // التحقق من عدد الملفات
    if (files.length > question.max_files) {
      alert(`Maximum ${question.max_files} files allowed`);
      return;
    }
    
    // التحقق من حجم الملفات
    const maxSize = question.max_file_size * 1024 * 1024; // تحويل إلى bytes
    const invalidFiles = files.filter(file => file.size > maxSize);
    
    if (invalidFiles.length > 0) {
      alert(`Some files exceed maximum size of ${question.max_file_size}MB`);
      return;
    }
    
    setSelectedFiles(files);
  };

  const handleUpload = async () => {
    const formData = new FormData();
    formData.append('question_id', question.id);
    
    selectedFiles.forEach(file => {
      formData.append('files[]', file);
    });

    try {
      const response = await fetch(
        `/api/user/offers/${offerId}/answer`,
        {
          method: 'POST',
          body: formData
        }
      );

      const data = await response.json();
      console.log('Files uploaded:', data);
    } catch (error) {
      console.error('Error uploading files:', error);
    }
  };

  return (
    <div>
      <input
        type="file"
        multiple
        accept={question.allowed_file_types?.join(',')}
        onChange={handleFileChange}
      />
      <button onClick={handleUpload}>Upload Files</button>
    </div>
  );
}
```

---

### 6. ملاحظات مهمة

1. **لا تضيف Content-Type header يدوياً** عند استخدام Fetch API - المتصفح سيفعل ذلك تلقائياً مع الحدود الصحيحة
2. **استخدم `option_ids[]`** (مع الأقواس المربعة) لإرسال array من الخيارات
3. **استخدم `files[]`** (مع الأقواس المربعة) لإرسال array من الملفات
4. **التحقق من حجم الملفات** قبل الإرسال لتجنب الأخطاء
5. **التحقق من عدد الملفات** قبل الإرسال
6. **استخدم `FormData`** عند إرسال ملفات، و **JSON** عند إرسال بيانات فقط

---

## API Endpoints

### 1. جلب الأسئلة حسب Type ID
**GET** `/api/user/types/{type_id}/questions?lang=en`

**Response:**
```json
{
  "status": 200,
  "msg": "Questions retrieved successfully",
  "data": [
    {
      "id": 1,
      "question_text": "What is your address?",
      "question_type": "text",
      "is_required": true,
      "allows_file_upload": false,
      "options": [],
      "child_questions": []
    }
  ]
}
```

### 2. جلب أول سؤال للـ Offer
**GET** `/api/user/offers/{offer_id}/questions/first?lang=en`

### 3. جلب سؤال محدد
**GET** `/api/user/offers/{offer_id}/questions/{question_id}?lang=en`

### 4. إرسال إجابة
**POST** `/api/user/offers/{offer_id}/answer?lang=en`

**Request Body:**
```json
{
  "question_id": 1,
  "answer": "My answer",
  "option_ids": [1, 2],
  "files": []  // Optional
}
```

### 5. إرسال الفورم الكامل
**POST** `/api/user/offers/submit-form?lang=en`

**Request Body:**
```json
{
  "type_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "answers": [
    {
      "question_id": 1,
      "answer": "My answer",
      "option_ids": [1]
    }
  ]
}
```

### 6. جلب جميع الإجابات
**GET** `/api/user/offers/{offer_id}/answers?lang=en`

### 7. رفع ملفات للإجابة
**POST** `/api/user/offers/{offer_id}/answers/{answer_id}/files`

**Request Body (multipart/form-data):**
```
files[]: [file1, file2, ...]
```

### 8. حذف ملف
**DELETE** `/api/user/offers/{offer_id}/answers/{answer_id}/files/{file_id}`

---

## Frontend Implementation Guide

### 1. عرض السؤال حسب النوع

```javascript
function renderQuestion(question) {
  switch(question.question_type) {
    case 'text':
      return <TextInput 
        placeholder={question.question_text}
        required={question.is_required}
      />;
      
    case 'single_choice':
      return <RadioGroup 
        options={question.options}
        required={question.is_required}
      />;
      
    case 'multi_choice':
      return <CheckboxGroup 
        options={question.options}
        required={question.is_required}
      />;
      
    case 'number':
      return <NumberInput 
        placeholder={question.question_text}
        required={question.is_required}
        type="number"
      />;
      
    case 'date':
      return <DateInput 
        placeholder={question.question_text}
        required={question.is_required}
        type="date"
      />;
      
    case 'email':
      return <EmailInput 
        placeholder={question.question_text}
        required={question.is_required}
        type="email"
      />;
      
    case 'phone':
      return <PhoneInput 
        placeholder={question.question_text}
        required={question.is_required}
        type="tel"
      />;
  }
}
```

### 2. معالجة رفع الملفات

```javascript
function handleFileUpload(question, files) {
  if (!question.allows_file_upload) {
    return;
  }
  
  // التحقق من عدد الملفات
  if (files.length > question.max_files) {
    alert(`Maximum ${question.max_files} files allowed`);
    return;
  }
  
  // التحقق من حجم الملفات
  const maxSize = question.max_file_size * 1024 * 1024; // تحويل إلى bytes
  for (let file of files) {
    if (file.size > maxSize) {
      alert(`File ${file.name} exceeds maximum size of ${question.max_file_size}MB`);
      return;
    }
  }
  
  // رفع الملفات
  const formData = new FormData();
  files.forEach(file => formData.append('files[]', file));
  
  // إرسال الطلب
  fetch(`/api/user/offers/${offerId}/answers/${answerId}/files`, {
    method: 'POST',
    body: formData
  });
}
```

### 3. معالجة Branching

```javascript
function handleAnswer(question, answer, selectedOptions) {
  // حفظ الإجابة
  saveAnswer(question.id, answer, selectedOptions);
  
  // التحقق من وجود أسئلة فرعية
  if (question.child_questions && question.child_questions.length > 0) {
    // عرض الأسئلة الفرعية بناءً على الخيارات المختارة
    const childQuestions = question.child_questions.filter(child => {
      return selectedOptions.includes(child.parent_option_id);
    });
    
    // عرض الأسئلة الفرعية
    showChildQuestions(childQuestions);
  }
}
```

---

## Validation Rules

### Backend Validation

```php
// في AdminTypeQuestionController
'question_type' => 'required|in:text,single_choice,multi_choice,number,date,email,phone',
'is_required' => 'nullable|boolean',
'allows_file_upload' => 'nullable|boolean',
'allowed_file_types' => 'nullable', // string أو array
'max_files' => 'nullable|integer|min:1',
'max_file_size' => 'nullable|integer|min:1', // بالـ MB
```

### Frontend Validation

```javascript
function validateAnswer(question, answer, selectedOptions) {
  // التحقق من الإجبارية
  if (question.is_required) {
    if (question.question_type === 'single_choice' || question.question_type === 'multi_choice') {
      if (!selectedOptions || selectedOptions.length === 0) {
        return 'This question is required';
      }
    } else {
      if (!answer || answer.trim() === '') {
        return 'This question is required';
      }
    }
  }
  
  // التحقق من صحة البريد الإلكتروني
  if (question.question_type === 'email') {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(answer)) {
      return 'Please enter a valid email address';
    }
  }
  
  // التحقق من صحة التاريخ
  if (question.question_type === 'date') {
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(answer)) {
      return 'Please enter a valid date (YYYY-MM-DD)';
    }
  }
  
  // التحقق من single_choice (اختيار واحد فقط)
  if (question.question_type === 'single_choice') {
    if (selectedOptions && selectedOptions.length > 1) {
      return 'Please select only one option';
    }
  }
  
  return null; // صحيح
}
```

---

## أمثلة كاملة

### مثال 1: سؤال نصي بسيط
```json
{
  "question_id": 1,
  "answer": "My address is 123 Main Street"
}
```

### مثال 2: سؤال اختيار واحد مع branching
```json
{
  "question_id": 2,
  "option_ids": [1]
}
// سيظهر سؤال فرعي إذا كان parent_option_id = 1
```

### مثال 3: سؤال اختيارات متعددة
```json
{
  "question_id": 3,
  "option_ids": [3, 4, 5]
}
```

### مثال 4: سؤال مع رفع ملفات
```json
{
  "question_id": 8,
  "answer": "Yes, I have photos",
  "files": [file1, file2, file3]
}
```

### مثال 5: الفورم الكامل
```json
{
  "type_id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "answers": [
    {
      "question_id": 1,
      "answer": "123 Main Street"
    },
    {
      "question_id": 2,
      "option_ids": [1]
    },
    {
      "question_id": 3,
      "option_ids": [3, 4]
    },
    {
      "question_id": 8,
      "answer": "Yes",
      "files": [file1, file2]
    }
  ]
}
```

---

## ملاحظات مهمة

1. **الترجمة:** جميع الأسئلة والخيارات تدعم 5 لغات (en, de, fr, it, ar)
2. **الترتيب:** استخدم `order` لترتيب الأسئلة والخيارات
3. **الإجبارية:** استخدم `is_required` لتحديد ما إذا كان السؤال إجبارياً
4. **رفع الملفات:** يمكن تفعيل رفع الملفات لأي نوع سؤال
5. **Branching:** استخدم `parent_question_id` و `parent_option_id` لإنشاء أسئلة فرعية
6. **Validation:** تأكد من التحقق من صحة البيانات في الـ Frontend قبل الإرسال

---

## الدعم والمساعدة

إذا واجهت أي مشاكل أو لديك أسئلة، يرجى التواصل مع فريق التطوير.

