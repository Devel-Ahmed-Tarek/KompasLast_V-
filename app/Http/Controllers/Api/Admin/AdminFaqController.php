<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminFaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index()
    {
        $faqs = Faq::all();

        return response()->json([
            'status' => 200,
            'message' => 'FAQs retrieved successfully',
            'data' => $faqs,
        ]);
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|array',
            'answer' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]);
        }

        $faq = Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'FAQ created successfully',
            'data' => $faq,
        ]);
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'question' => 'sometimes|array',
            'answer' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ]);
        }

        $validatedData = $validator->validated();

        // Update translations for 'question' if present
        if (isset($validatedData['question'])) {
            foreach ($validatedData['question'] as $locale => $text) {
                $faq->setTranslation('question', $locale, $text);
            }
        }

        // Update translations for 'answer' if present
        if (isset($validatedData['answer'])) {
            foreach ($validatedData['answer'] as $locale => $text) {
                $faq->setTranslation('answer', $locale, $text);
            }
        }

        // Save the updated FAQ
        $faq->save();

        return response()->json([
            'status' => 200,
            'message' => 'FAQ updated successfully',
            'data' => $faq,
        ]);
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);

        $faq->delete();

        return response()->json([
            'status' => 200,
            'message' => 'FAQ deleted successfully',
        ]);
    }
}
