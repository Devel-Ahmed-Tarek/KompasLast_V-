# 📸 نظام رفع الصور الديناميكي

## نظرة عامة

النظام الجديد يسمح برفع صور **ديناميكياً** لأي حقل بأي لغة، بدون الحاجة لتحديد الحقول مسبقاً في الكود.

---

## 🎯 المميزات

1. **ديناميكي بالكامل**: رفع صور لأي حقل جديد بدون تعديل الكود
2. **دعم 5 لغات**: en, de, fr, it, ar
3. **صور متعددة**: يمكن رفع أكثر من صورة لنفس الحقل واللغة
4. **metadata**: إمكانية إضافة معلومات إضافية لكل صورة
5. **التوافق مع النظام القديم**: النظام القديم لا يزال يعمل

---

## 📋 البنية

### جدول `type_service_media`

```sql
- id
- type_ditali_service_id
- field_name          (مثل: small_image, main_image, feature_image, etc.)
- language            (en, de, fr, it, ar)
- file_path
- file_name
- file_type           (image, video, document)
- file_size           (بالـ KB)
- metadata            (JSON - معلومات إضافية)
- order               (للترتيب لو في أكثر من صورة)
```

---

## 🚀 كيفية الاستخدام

### 1️⃣ رفع صورة واحدة لحقل معين

**FormData:**
```
media[field_name][language] = file
```

**مثال:**
```javascript
const formData = new FormData();
formData.append('media[small_image][en]', fileEn);
formData.append('media[main_image][de]', fileDe);
formData.append('media[feature_image][fr]', fileFr);
formData.append('media[banner_image][ar]', fileAr);
```

---

### 2️⃣ رفع أكثر من صورة لنفس الحقل واللغة

**FormData:**
```
media[field_name][language][0] = file1
media[field_name][language][1] = file2
media[field_name][language][2] = file3
```

**مثال:**
```javascript
const formData = new FormData();
formData.append('media[gallery][en][0]', image1);
formData.append('media[gallery][en][1]', image2);
formData.append('media[gallery][en][2]', image3);
```

---

### 3️⃣ مثال كامل (React/Vue)

```javascript
// مثال: رفع صور متعددة
const uploadImages = async (serviceId, images) => {
  const formData = new FormData();
  
  // صورة واحدة
  formData.append('media[small_image][en]', images.smallImageEn);
  formData.append('media[main_image][de]', images.mainImageDe);
  
  // صور متعددة
  images.galleryEn.forEach((file, index) => {
    formData.append(`media[gallery][en][${index}]`, file);
  });
  
  // إرسال الطلب
  const response = await fetch(`/api/admin/update/details/types/${serviceId}`, {
    method: 'POST',
    body: formData,
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  return response.json();
};
```

---

## 📥 Response من API

### GET `/api/admin/details/types/{id}`

```json
{
  "status": 200,
  "msg": "done",
  "data": {
    "id": 1,
    "type_id": 16,
    "small_image": {
      "en": "http://example.com/uploads/images/...",
      "de": "http://example.com/uploads/images/...",
      ...
    },
    "main_image": {
      ...
    },
    "dynamic_media": {
      "small_image": {
        "en": [
          {
            "id": 1,
            "file_path": "http://example.com/uploads/images/...",
            "file_name": "image.jpg",
            "file_type": "image",
            "file_size": 250,
            "order": 0,
            "metadata": null
          }
        ],
        "de": [...],
        "fr": [...]
      },
      "gallery": {
        "en": [
          {
            "id": 2,
            "file_path": "...",
            "order": 0
          },
          {
            "id": 3,
            "file_path": "...",
            "order": 1
          }
        ]
      }
    }
  }
}
```

---

## 🔄 التوافق مع النظام القديم

النظام القديم لا يزال يعمل! يمكنك استخدام:

**الطريقة القديمة:**
```
small_image[en] = file
main_image[de] = file
```

**الطريقة الجديدة:**
```
media[small_image][en] = file
media[main_image][de] = file
```

**أو كلاهما معاً!** ✅

---

## 🎨 أمثلة على الحقول الديناميكية

يمكنك رفع صور لأي حقل تريده:

```
media[banner_image][en] = file
media[hero_image][de] = file
media[feature_image][fr] = file
media[gallery][en][0] = file1
media[gallery][en][1] = file2
media[testimonial_image][ar] = file
media[logo][en] = file
media[icon][de] = file
... إلخ
```

---

## ⚠️ ملاحظات مهمة

1. **اللغات المدعومة**: en, de, fr, it, ar
2. **أنواع الملفات**: jpeg, png, jpg, gif (يمكن تعديلها في Validation)
3. **حجم الملف**: max 10MB (يمكن تعديله)
4. **الترتيب**: `order` يبدأ من 0
5. **الحذف**: عند رفع صورة جديدة لنفس الحقل + اللغة + الترتيب، يتم حذف القديمة تلقائياً

---

## 🔧 التطوير المستقبلي

- إضافة metadata مترجم (alt, title, description)
- دعم الفيديو والملفات
- إمكانية حذف صورة محددة
- إعادة ترتيب الصور

---

## 📞 الدعم

لأي استفسارات، راجع الكود في:
- `app/Http/Controllers/Api/Admin/AdminTypeDitaliServicesController.php`
- `app/Models/TypeServiceMedia.php`

