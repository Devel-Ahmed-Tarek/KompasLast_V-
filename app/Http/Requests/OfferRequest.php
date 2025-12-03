<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'anrede' => 'nullable|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'date' => 'nullable|date',
            'adresse' => 'required|string',
            'ort' => 'required|string',
            'zimmer' => 'required|string',
            'etage' => 'required|string',
            'vorhanden' => 'required|string',
            'Nach_Adresse' => 'nullable|string',
            'Nach_Ort' => 'nullable|string',
            'Nach_Zimmer' => 'nullable|string',
            'Nach_Etage' => 'nullable|string',
            'Nach_vorhanden' => 'nullable|string',
            'count' => 'required|integer',
            'Number_of_offers' => 'nullable|integer',
            'cheek' => 'nullable|boolean',
            'Besonderheiten' => 'nullable|string',
        ];
    }
}
