<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGoalRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'target_amount' => ['required', 'numeric', 'min:0.01'],
            'deadline' => ['required', 'date', 'after:today'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da meta é obrigatório.',
            'name.max' => 'O nome da meta não pode ter mais de 255 caracteres.',
            'target_amount.required' => 'O valor alvo é obrigatório.',
            'target_amount.numeric' => 'O valor alvo deve ser um número.',
            'target_amount.min' => 'O valor alvo deve ser maior que zero.',
            'deadline.required' => 'A data limite é obrigatória.',
            'deadline.date' => 'A data limite deve ser uma data válida.',
            'deadline.after' => 'A data limite deve ser uma data futura.',
            'category_id.exists' => 'A categoria selecionada não existe.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->category_id) {
                $category = auth()->user()->categories()->find($this->category_id);
                if (!$category) {
                    $validator->errors()->add('category_id', 'A categoria selecionada não pertence ao usuário.');
                }
            }
        });
    }
}