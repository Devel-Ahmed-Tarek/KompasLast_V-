<?php
namespace App\Http\Controllers\Api\Admin;

use App\Helpers\HelperFunc;
use App\Http\Controllers\Controller;
use App\Models\ConfigApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigAppController extends Controller
{
    public function index()
    {
        $cofing = ConfigApp::first();
        // Check if 'file' exists and then assign the correct asset path or null
        $cofing->file = $cofing->file ? asset($cofing->file) : null;
        // Check if 'file2' exists and then assign the correct asset path or null
        $cofing->file2 = $cofing->file2 ? asset($cofing->file2) : null;
        // Assign 'file3' with the asset path
        $cofing->file3     = $cofing->file3 ? asset($cofing->file3) : null;
        $cofing->logo      = $cofing->logo ? asset($cofing->logo) : null;
        $cofing->logo_dark = $cofing->logo_dark ? asset($cofing->logo_dark) : null;

        $cofing->qrcode = $cofing->qrcode ? asset($cofing->qrcode) : null;
        return HelperFunc::sendResponse(200, 'done', $cofing);
    }

    public function update(Request $request)
    {
        // Validate the input fields
        $validator = Validator::make($request->all(), [
            'add_offer'            => 'boolean',
            'offer_flow'           => 'boolean',
            'add_company'          => 'boolean',
            'on_contact'           => 'boolean',
            'on_shop'              => 'boolean',
            'on_auth_company'      => 'boolean',
            'accept_dynamic_offer' => 'boolean',
            'add_finance_order'    => 'boolean',
            'file'                 => 'nullable|file|mimes:jpeg,png,pdf|max:5048',
            'logo'                 => 'nullable|file|mimes:jpeg,png,pdf|max:5048',
            'logo_dark'            => 'nullable|file|mimes:jpeg,png,pdf|max:5048',
            'qrcode'               => 'nullable|file|mimes:jpeg,png,pdf|max:5048',
            'file2'                => 'nullable|file|mimes:jpeg,png,pdf|max:5048',
            'file3'                => 'nullable|file|mimes:jpeg,png,pdf|max:5048',
            'name'                 => 'nullable|string|max:255',
            'address'              => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'email2'               => 'nullable|email|max:255',
            'website'              => 'nullable|url|max:255',
            'phone'                => 'nullable|string|max:20',
            'number'               => 'nullable|string|max:20',
            'bank_name'            => 'nullable|string|max:255',
            'bank_number'          => 'nullable|string|max:255',
            'bank_ip'              => 'nullable|string|max:255',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return HelperFunc::sendResponse(422, 'Validation failed.', $validator->errors());
        }

        // Retrieve the first ConfigApp record
        $configApp = ConfigApp::first();

        // If no configuration found, return an error
        if (! $configApp) {
            return HelperFunc::sendResponse(404, 'Configuration not found.');
        }

        // Upload files if provided, using HelperFunc::uploadFile
        $files = ['file', 'file2', 'file3', 'logo', 'qrcode', 'logo_dark'];
        foreach ($files as $fileKey) {
            if ($request->hasFile($fileKey)) {
                // Delete the old file if it exists
                HelperFunc::deleteFile(public_path($configApp->$fileKey));
                // Upload the new file using HelperFunc
                $path                = HelperFunc::uploadFile('config', $request->file($fileKey));
                $configApp->$fileKey = $path;
            }
        }

        // Update other fields
        $fields        = $request->except($files);
        $updatedFields = [];

        foreach ($fields as $key => $value) {
            if (in_array($key, [
                'add_offer', 'offer_flow', 'add_company', 'on_contact',
                'on_shop', 'on_auth_company', 'accept_dynamic_offer',
                'add_finance_order', 'name', 'address', 'email',
                'email2', 'website', 'phone', 'number', 'bank_name',
                'bank_number', 'bank_ip', 'threads', 'twiter', 'istagram', 'tiktok', 'linkedin', 'facebook',
            ])) {
                $configApp->$key = $value;
                $updatedFields[] = $key;
            }
        }

        // Save the updated configuration
        $configApp->save();

        // Return success response with updated fields and uploaded files
        return HelperFunc::sendResponse(200, 'Configuration updated successfully.', [
            'updated_fields' => $updatedFields,
            'uploaded_files' => $files,
            'data'           => $configApp,
        ]);
    }

}
