<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userParam = $this->route('user');
        $userId = $userParam instanceof User ? $userParam->uuid : $userParam;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'uuid'),
            ],
            'password' => 'nullable|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Role::where('id', $value)
                        ->orWhere('name', $value)
                        ->exists();
                    if (! $exists) {
                        $fail("The selected {$attribute} is invalid.");
                    }
                },
            ],
        ];
    }
}
