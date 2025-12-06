# 📎 API Documentation - File Upload Feature

## نظرة عامة
نظام رفع الملفات للأسئلة الديناميكية. المستخدم يمكنه رفع صور، فيديوهات، أو ملفات عند الإجابة على سؤال معين.

---

## 🔑 **المتغيرات المهمة في السؤال**

عند جلب أي سؤال، ستجد هذه الحقول:

```json
{
  "id": 1,
  "question_text": "هل لديك صور؟",
  "question_type": "single_choice",
  "allows_file_upload": true,        // ✅ هل يسمح برفع الملفات؟
  "allowed_file_types": ["image", "video", "document"],  // ✅ أنواع الملفات المسموحة
  "max_files": 5,                    // ✅ عدد الملفات الأقصى
  "max_file_size": 10                // ✅ الحجم الأقصى لكل ملف (بالـ MB)
}
```

---

## 📡 **API Endpoints**

### 1️⃣ **إرسال إجابة مع ملفات**

```
POST /api/user/offers/{offer_id}/answer
Content-Type: multipart/form-data
```

**Parameters:**
- `question_id` (required) - ID السؤال
- `answer` (optional) - نص الإجابة (للأسئلة النصية)
- `option_ids[]` (optional) - array من IDs الاختيارات
- `files[]` (optional) - array من الملفات (إذا كان `allows_file_upload = true`)
- `lang` (optional) - اللغة: `en`, `de`, `fr`, `it`, `ar`

**Example Request:**
```javascript
const formData = new FormData();
formData.append('question_id', 1);
formData.append('option_ids[]', 1);
formData.append('files[]', file1); // صورة
formData.append('files[]', file2); // فيديو
formData.append('files[]', file3); // ملف PDF

fetch(`/api/user/offers/123/answer?lang=ar`, {
  method: 'POST',
  body: formData
});
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
      "total": 10,
      "percentage": 10
    },
    "next_question": {
      "id": 2,
      "question_text": "...",
      "allows_file_upload": false
    }
  }
}
```

---

### 2️⃣ **رفع ملفات منفصلة (بعد الإجابة)**

```
POST /api/user/offers/{offer_id}/answers/{answer_id}/files
Content-Type: multipart/form-data
```

**Parameters:**
- `files[]` (required) - array من الملفات

**Example Request:**
```javascript
const formData = new FormData();
formData.append('files[]', file1);
formData.append('files[]', file2);

fetch(`/api/user/offers/123/answers/456/files`, {
  method: 'POST',
  body: formData
});
```

**Response:**
```json
{
  "status": 200,
  "msg": "Files uploaded successfully",
  "data": {
    "uploaded_files": [
      {
        "id": 1,
        "file_name": "photo.jpg",
        "file_type": "image",
        "file_url": "https://domain.com/uploads/offer-answers/photo.jpg",
        "file_size": 1024000
      }
    ],
    "total_files": 2
  }
}
```

---

### 3️⃣ **حذف ملف**

```
DELETE /api/user/offers/{offer_id}/answers/{answer_id}/files/{file_id}
```

**Response:**
```json
{
  "status": 200,
  "msg": "File deleted successfully",
  "data": []
}
```

---

### 4️⃣ **جلب الإجابات مع الملفات**

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
        "question_text": "هل لديك صور؟",
        "question_type": "single_choice",
        "answer_text": null,
        "selected_options": [
          {
            "id": 1,
            "option_text": "نعم"
          }
        ],
        "files": [
          {
            "id": 1,
            "file_name": "photo1.jpg",
            "file_type": "image",
            "file_url": "https://domain.com/uploads/offer-answers/photo1.jpg",
            "file_size": 1024000
          },
          {
            "id": 2,
            "file_name": "video1.mp4",
            "file_type": "video",
            "file_url": "https://domain.com/uploads/offer-answers/video1.mp4",
            "file_size": 5242880
          }
        ]
      }
    ]
  }
}
```

---

## 🎯 **Flow Example**

### السيناريو: سؤال "هل لديك صور؟"

```javascript
// 1. جلب السؤال
const question = await fetch(`/api/user/offers/123/questions/first?lang=ar`);

// Response:
{
  "question": {
    "id": 1,
    "question_text": "هل لديك صور؟",
    "allows_file_upload": true,  // ✅ يسمح برفع الملفات
    "allowed_file_types": ["image", "video"],
    "max_files": 5,
    "max_file_size": 10
  }
}

// 2. المستخدم يختار "نعم" ويرفع ملفات
const formData = new FormData();
formData.append('question_id', 1);
formData.append('option_ids[]', 1); // "نعم"
formData.append('files[]', photo1);
formData.append('files[]', photo2);

const response = await fetch(`/api/user/offers/123/answer?lang=ar`, {
  method: 'POST',
  body: formData
});

// 3. الحصول على السؤال التالي
const nextQuestion = await response.json();
```

---

## ⚠️ **Validation Rules**

1. **عدد الملفات:**
   - لا يمكن رفع أكثر من `max_files`
   - إذا كان لديك ملفات موجودة، سيتم حسابها مع الجديدة

2. **حجم الملف:**
   - كل ملف لا يجب أن يتجاوز `max_file_size` (بالـ MB)

3. **نوع الملف:**
   - فقط الأنواع المذكورة في `allowed_file_types` مسموحة:
     - `image` - صور (jpg, png, gif, etc.)
     - `video` - فيديوهات (mp4, avi, mov, etc.)
     - `document` - مستندات (pdf, doc, docx, etc.)

---

## 💡 **Tips للفرونت إند**

### 1. التحقق قبل الرفع:
```javascript
if (question.allows_file_upload) {
  // عرض file input
  // التحقق من max_files و max_file_size
}
```

### 2. عرض الملفات المرفوعة:
```javascript
answer.files.forEach(file => {
  if (file.file_type === 'image') {
    // عرض صورة
  } else if (file.file_type === 'video') {
    // عرض فيديو
  } else {
    // عرض رابط تحميل
  }
});
```

### 3. Progress Bar:
```javascript
const progress = (answered / total) * 100;
// عرض progress bar
```

---

## 📝 **Error Responses**

### 400 - Bad Request:
```json
{
  "status": 400,
  "msg": "Maximum files limit exceeded. You can upload up to 5 files.",
  "data": []
}
```

### 422 - Validation Error:
```json
{
  "status": 422,
  "msg": "Validation errors",
  "data": {
    "files.0": ["The files.0 must not be greater than 10240 kilobytes."]
  }
}
```

---

## 🔗 **Quick Links**

- جلب أول سؤال: `GET /api/user/offers/{offer_id}/questions/first`
- إرسال إجابة: `POST /api/user/offers/{offer_id}/answer`
- رفع ملفات: `POST /api/user/offers/{offer_id}/answers/{answer_id}/files`
- حذف ملف: `DELETE /api/user/offers/{offer_id}/answers/{answer_id}/files/{file_id}`
- جلب الإجابات: `GET /api/user/offers/{offer_id}/answers`

