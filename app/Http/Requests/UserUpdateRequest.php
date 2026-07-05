<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $userId = $this->route('user');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $userId . ',uuid',
            'password' => 'nullable|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = \DB::table('roles')
                        ->where('id', $value)
                        ->orWhere('name', $value)
                        ->exists();
                    if (!$exists) {
                        $fail("The selected {$attribute} is invalid.");
                    }
                }
            ],
        ];
    }
}
