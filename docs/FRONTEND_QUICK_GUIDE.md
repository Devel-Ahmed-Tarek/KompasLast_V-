# 🚀 Quick Guide - File Upload Feature

## 📋 **Checklist للفرونت إند**

### ✅ **1. عند جلب السؤال - تحقق من `allows_file_upload`**

```javascript
if (question.allows_file_upload) {
  // عرض file input
  // max_files: question.max_files
  // max_file_size: question.max_file_size (MB)
  // allowed_file_types: question.allowed_file_types
}
```

---

### ✅ **2. إرسال إجابة مع ملفات**

```javascript
POST /api/user/offers/{offer_id}/answer
Content-Type: multipart/form-data

FormData:
- question_id: 1
- option_ids[]: [1, 2]
- files[]: [file1, file2, file3]  // ✅ الملفات هنا
- lang: "ar"
```

---

### ✅ **3. Response Structure**

```json
{
  "next_question": {
    "allows_file_upload": true,
    "allowed_file_types": ["image", "video", "document"],
    "max_files": 5,
    "max_file_size": 10
  }
}
```

---

### ✅ **4. عرض الملفات المرفوعة**

```json
{
  "answers": [
    {
      "files": [
        {
          "id": 1,
          "file_name": "photo.jpg",
          "file_type": "image",
          "file_url": "https://...",
          "file_size": 1024000
        }
      ]
    }
  ]
}
```

---

## 🎯 **Endpoints Summary**

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/offers/{id}/answer` | إرسال إجابة + ملفات |
| POST | `/offers/{id}/answers/{answer_id}/files` | رفع ملفات منفصلة |
| DELETE | `/offers/{id}/answers/{answer_id}/files/{file_id}` | حذف ملف |
| GET | `/offers/{id}/answers` | جلب الإجابات + الملفات |

---

## ⚡ **Quick Example**

```javascript
// رفع ملفات مع الإجابة
const formData = new FormData();
formData.append('question_id', 1);
formData.append('option_ids[]', 1);

// إضافة ملفات
files.forEach(file => {
  formData.append('files[]', file);
});

fetch(`/api/user/offers/123/answer?lang=ar`, {
  method: 'POST',
  body: formData
});
```

---

**📄 الملف الكامل:** `docs/FRONTEND_FILE_UPLOAD_API.md`

