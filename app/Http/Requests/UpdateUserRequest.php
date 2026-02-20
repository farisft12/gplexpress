<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        return $this->user()->can('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/', 'unique:users,phone,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()],
<<<<<<< HEAD
            'role' => ['required', 'in:owner,admin,manager,kurir,user'],
=======
            'role' => ['required', 'in:super_admin,manager_cabang,admin_cabang,courier_cabang,admin,manager,kurir,user'],
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
            'status' => ['required', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan oleh user lain.',
            'phone.unique' => 'Nomor telepon sudah digunakan oleh user lain.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }
}
