<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class HelperFunc
{

    public static function apiResponse($status = true, $statusCode = 200, $data = [])
    {
        $statusMsg = $status == true ? 'success' : 'error';
        $dataType  = $status == true ? 'data' : 'errors';

        return response()->json([
            'status'  => $statusMsg,
            $dataType => $data,
        ], $statusCode);
    }

    public static function uploadFile($path, $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $name      = time() . rand(100, 999) . '.' . $extension;
        $destinationPath = 'uploads/' . $path;

        // إنشاء المجلد إذا لم يكن موجوداً
        if (!file_exists(public_path($destinationPath))) {
            mkdir(public_path($destinationPath), 0755, true);
        }

        // محاولة نقل الملف
        try {
            $moved = $file->move(public_path($destinationPath), $name);
            if ($moved) {
                return $destinationPath . '/' . $name;
            }
        } catch (\Exception $e) {
            // إذا فشل move، نحاول copy
            \Log::warning('File move failed, trying copy: ' . $e->getMessage());
        }

        // محاولة نسخ الملف بدلاً من نقله
        try {
            $realPath = $file->getRealPath();
            if ($realPath && is_readable($realPath)) {
                $destination = public_path($destinationPath . '/' . $name);
                if (copy($realPath, $destination)) {
                    return $destinationPath . '/' . $name;
                }
            }
        } catch (\Exception $e) {
            \Log::error('File copy failed: ' . $e->getMessage());
        }

        // محاولة أخيرة: قراءة المحتوى وكتابته
        try {
            $realPath = $file->getRealPath();
            if ($realPath && is_readable($realPath)) {
                $content = file_get_contents($realPath);
                if ($content !== false) {
                    $destination = public_path($destinationPath . '/' . $name);
                    if (file_put_contents($destination, $content) !== false) {
                        return $destinationPath . '/' . $name;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('File write failed: ' . $e->getMessage());
        }

        throw new \Exception('Failed to upload file: ' . ($file->getClientOriginalName() ?? 'unknown'));
    }

    public static function deleteFile($file)
    {
        if (file_exists(filename: $file) && is_file($file)) {
            unlink($file); // Delete the file
        }
    }

    public static function limit($limit = 10)
    {
        return $limit;
    }

    public static function sendResponse($code = 200, $msg = null, $data = [])
    {
        $response = [
            'status' => $code,
            'msg'    => $msg,
            'data'   => $data,
        ];
        return response()->json($response, $code);
    }

    public static function pagination($itme, $Resource)
    {
        if (count($itme) > 0) {

            $data = [
                'rows'            => $Resource,
                'paginationLinks' => [
                    'currentPages' => $itme->currentPage(),
                    'perPage'      => $itme->lastpage(),
                    'total'        => $itme->total(),
                    'links'        => [
                        'first' => $itme->url(1),
                        'last'  => $itme->url($itme->lastpage()),
                    ],
                ],
            ];

            return HelperFunc::sendResponse(200, 'تم بنجاح', $data);
        }
        return HelperFunc::sendResponse(200, 'لايوجد بيانات ', []);
    }

    public static function paginationNew($item, $data)
    {
        return [
            'data'      => $item,
            'paination' => [
                'currentPages' => $data->currentPage(),
                'perPage'      => $data->lastpage(),
                'total'        => $data->total(),
                'links'        => [
                    'first' => $data->url(1),
                    'last'  => $data->url($data->lastpage()),
                ],
            ],
        ];
    }

    public static function getLinkAfterUploads(string $url): string
    {
        $keyword = 'uploads';

        // البحث عن موضع الكلمة "uploads" في النص
        $position = strpos($url, $keyword);

        if ($position === false) {
            // إذا لم يتم العثور على الكلمة "uploads"، يمكننا إرجاع النص الأصلي أو رسالة فارغة
            return $url;
        }

        // استخراج النص بعد "uploads/"
        $path = substr($url, $position + strlen($keyword) + 1);

        // إضافة "uploads/" في بداية المسار
        return $keyword . '/' . ltrim($path, '/');
    }

    public static function getLocalizedImage(?array $images): ?string
    {
        if (is_null($images)) {
            return null;
        }

        // Get the current application locale
        $locale = app()->getLocale();

        // Return the image for the current locale or a default locale (fallback)
        return $images[$locale] ?? $images['en'] ?? null;
    }

    public function updatefildeslan($model, $validated)
    {
        foreach ($model->getTranslatableAttributes() as $field) {
            if (isset($validated[$field])) {
                foreach ($validated[$field] as $locale => $value) {
                    $model->setTranslation($field, $locale, $value);
                }
            }
        }
    }

    public static function sendMultilangNotification($users, $type, $type_id, $messages)
    {
        Notification::send($users, new \App\Notifications\PaymentNotification([
            'type'    => $type,
            'type_id' => $type_id,
            'mgs'     => [
                'en' => $messages['en'] ?? '',
                'de' => $messages['de'] ?? '',
            ],
        ]));
    }
}
