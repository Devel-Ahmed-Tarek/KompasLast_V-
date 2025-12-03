<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\Type;
use App\Models\TypeDitaliServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminTypeDitaliServicesController extends Controller
{

    private $languages = ['en', 'de', 'fr', 'it'];

    public function show($id)
    {
        $date = TypeDitaliServices::where('type_id', $id)->with('type')->first();
        if ($date == []) {
            return HelperFunc::sendResponse(200, 'done', $date);
        }
        if ($date->small_image != []) {
            $date->small_image = [
                'en' => $date->small_image['en'] ? asset($date->small_image['en']) : null,
                'de' => $date->small_image['de'] ? asset($date->small_image['de']) : null,
                'fr' => $date->small_image['fr'] ? asset($date->small_image['fr']) : null,
                'it' => $date->small_image['it'] ? asset($date->small_image['it']) : null,

            ];
        }
        if ($date->main_image != []) {
            $date->main_image = [
                'en' => $date->main_image['en'] ? asset($date->main_image['en']) : null,
                'de' => $date->main_image['de'] ? asset($date->main_image['de']) : null,
                'fr' => $date->main_image['fr'] ? asset($date->main_image['fr']) : null,
                'it' => $date->main_image['it'] ? asset($date->main_image['it']) : null,

            ];
        }
        if ($date->service_home_icon) {
            $date->service_home_icon = asset($date->service_home_icon);
        }
        return HelperFunc::sendResponse(200, 'done', $date);
    }

    public function store(Request $request)
    {
        // التحقق من البيانات
        $validator = $this->validateData($request);

        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors()->all());
        }

        try {
            $validated = $validator->validated();
            $nameType  = Type::find($validated['type_id'])->name;
            if ($request->hasFile('service_home_icon')) {
                $service_home_icon = HelperFunc::uploadFile('/images', $request->file('service_home_icon'));
            }
            $service = TypeDitaliServices::create([
                'type_id'           => $validated['type_id'],
                'small_image'       => [
                    'en' => HelperFunc::uploadFile('/images', $request->small_image['en']),
                    'de' => HelperFunc::uploadFile('/images', $request->small_image['de']),
                    'it' => HelperFunc::uploadFile('/images', $request->small_image['it']),
                    'fr' => HelperFunc::uploadFile('/images', $request->small_image['fr']),
                ],
                'main_image'        => [
                    'en' => HelperFunc::uploadFile('/images', $request->main_image['en']),
                    'de' => HelperFunc::uploadFile('/images', $request->main_image['de']),
                    'it' => HelperFunc::uploadFile('/images', $request->main_image['it']),
                    'fr' => HelperFunc::uploadFile('/images', $request->main_image['fr']),
                ],

                'short_description' => $validated['short_description'],
                'service_home_icon' => $service_home_icon,
                'feature_header'    => $validated['feature_header'],
                'feature_sub_title' => $validated['feature_sub_title'],
                'body'              => $validated['body'],
                'tips_title'        => $validated['tips_title'],
                'tips_subtitle'     => $validated['tips_subtitle'],
                'meta_keys'         => $validated['meta_keys'],
                'meta_title'        => $validated['meta_title'],
                'meta_description'  => $validated['meta_description'],
                'slug'              => $nameType,
            ]);

            return HelperFunc::sendResponse(201, 'Service created successfully', $service);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the request.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function validateData(Request $request)
    {
        $rules = [
            'type_id'           => 'required',
            'service_home_icon' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
        ];

        foreach ($this->languages as $lang) {
            $rules["small_image.$lang"]       = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules["main_image.$lang"]        = 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048';
            $rules["short_description.$lang"] = 'required|string';
            $rules["feature_header.$lang"]    = 'required|string';
            $rules["feature_sub_title.$lang"] = 'required|string';
            $rules["body.$lang"]              = 'required|string';
            $rules["tips_title.$lang"]        = 'required|string';
            $rules["tips_subtitle.$lang"]     = 'required|string';
            $rules["meta_keys.$lang"]         = 'required|string';
            $rules["meta_description.$lang"]  = 'required|string';
        }

        return Validator::make($request->all(), $rules); // إرجاع Validator بدلاً من البيانات
    }

    public function update(Request $request, $id)
    {
        try {
            // البحث عن الخدمة بواسطة الـ ID
            $service = TypeDitaliServices::findOrFail($id);

            // التحقق من صحة البيانات المرسلة
            $validator = Validator::make($request->all(), [
                'short_description'       => 'nullable|array',
                'short_description.*'     => 'nullable|string',
                'service_home_icon'       => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
                'feature_header.*'        => 'nullable|string',
                'feature_header'          => 'nullable|array',
                'feature_sub_title.*'     => 'nullable|string',
                'feature_sub_title'       => 'nullable|array',
                'body'                    => 'nullable|array',
                'body.*'                  => 'nullable|string',
                'tips_title'              => 'nullable|array',
                'tips_title.*'            => 'nullable|string',
                'tips_subtitle'           => 'nullable|array',
                'tips_subtitle.*'         => 'nullable|string',
                'meta_keys'               => 'nullable|array',
                'meta_keys.*'             => 'nullable|string',
                'meta_title'              => 'nullable|array',
                'meta_title.*'            => 'nullable|string',
                'blog_meta_title'         => 'nullable|array',
                'blog_meta_title.*'       => 'nullable|string',
                'blog_meta_keys'          => 'nullable|array',
                'blog_meta_keys.*'        => 'nullable|string',
                'slug'                    => 'nullable|array',
                'slug.*'                  => 'nullable|string|max:1000',
                'meta_description'        => 'nullable|array',
                'meta_description.*'      => 'nullable|string|max:500',
                'blog_meta_description'   => 'nullable|array',
                'blog_meta_description.*' => 'nullable|string|max:500',
                'small_image'             => 'nullable|array',
                'small_image.*'           => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
                'main_image'              => 'nullable|array',
                'main_image.*'            => 'nullable|file|mimes:jpeg,png,jpg,gif|max:10048',
            ]);

            if ($validator->fails()) {
                return HelperFunc::sendResponse(422, 'Validation errors', $validator->errors());
            }

            // الحصول على البيانات المصدقة
            $validated = $validator->validated();

            // معالجة الصور للغات المختلفة
            $languages = ['en', 'de', 'it', 'fr'];

            // دالة لمعالجة الصور
            $processImages = function ($type) use ($request, $service, $languages) {
                $images = [];
                foreach ($languages as $lang) {
                    if ($request->hasFile("{$type}.{$lang}")) {
                        $images[$lang] = HelperFunc::uploadFile('/images', $request->file("{$type}.{$lang}"));
                    } else {
                        // الاحتفاظ بالصورة القديمة إذا لم يتم تحديثها
                        $images[$lang] = $service->{$type}[$lang] ?? null;
                    }
                }
                return $images;
            };

            if ($request->hasFile('service_home_icon')) {
                $service->service_home_icon = HelperFunc::uploadFile('/images', $request->file('service_home_icon'));
            }
            // تحديث الصور الصغيرة والرئيسية
            $service->small_image = $processImages('small_image');
            $service->main_image  = $processImages('main_image');

            // تحديث الحقول النصية (التي تدعم الترجمة)
            foreach ($service->getTranslatableAttributes() as $field) {
                if (isset($validated[$field])) {
                    foreach ($validated[$field] as $locale => $value) {
                        $service->setTranslation($field, $locale, $value);
                    }
                }
            }

            // حفظ التغييرات في قاعدة البيانات
            $service->save();

            return HelperFunc::sendResponse(200, 'Service updated successfully', $service);

        } catch (\Exception $e) {
            \Log::error('Update Service Error: ' . $e->getMessage());
            return HelperFunc::sendResponse(500, 'An error occurred while updating the service.', [$e->getMessage()]);
        }
    }
}
