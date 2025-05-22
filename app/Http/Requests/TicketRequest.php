<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
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
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'attachments.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx,xls,xlsx|max:10240',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre du ticket est obligatoire.',
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'description.required' => 'La description du ticket est obligatoire.',
            'category_id.required' => 'Veuillez sélectionner une catégorie.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'attachments.*.file' => 'Les pièces jointes doivent être des fichiers valides.',
            'attachments.*.mimes' => 'Les pièces jointes doivent être des fichiers de type: jpeg, png, jpg, gif, pdf, doc, docx, xls, xlsx.',
            'attachments.*.max' => 'Les pièces jointes ne doivent pas dépasser 10 Mo.',
        ];
    }
} 