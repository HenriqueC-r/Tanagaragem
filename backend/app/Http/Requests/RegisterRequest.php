<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => Document::cpf($this->input('cpf')),
            'cnpj' => $this->filled('cnpj') ? Document::cnpj($this->input('cnpj')) : null,
            'state' => strtoupper((string) $this->input('state')),
        ]);
    }

    public function rules(): array
    {
        $isStore = $this->input('type') === User::TYPE_STORE;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'type' => ['required', Rule::in([User::TYPE_PERSON, User::TYPE_STORE])],
            'cpf' => [
                'bail', 'required', 'size:11', 'unique:users,cpf',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! Document::isValidCpf($value)) {
                        $fail('CPF inválido.');
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'size:2'],
            'company_name' => [Rule::requiredIf($isStore), 'nullable', 'string', 'max:255'],
            'cnpj' => [
                'bail',
                Rule::requiredIf($isStore),
                'nullable',
                'prohibited_if:type,person',
                'unique:users,cnpj',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! Document::isValidCnpj($value)) {
                        $fail('CNPJ inválido.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf.unique' => 'Já existe uma conta com este CPF.',
            'cnpj.unique' => 'Já existe uma conta com este CNPJ.',
            'email.unique' => 'Já existe uma conta com este e-mail.',
            'cnpj.prohibited_if' => 'Conta de pessoa física não usa CNPJ.',
        ];
    }
}
