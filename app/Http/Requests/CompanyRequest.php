<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'logo_file_id'         => 'nullable|exists:files,id',
            'display_name'         => 'required|string|max:255',
            'registration_name'    => 'required|string|max:255',
            'registration_no'      => 'required|string|max:255',
            'phone_dial_code'      => 'required|string|max:10',
            'phone_no'             => 'required|string|max:25',
            'email'                => 'nullable|email|max:255',
            'address'              => 'nullable|string',
            'region'               => 'nullable|string|max:255',
            'timezone'             => 'nullable|string|max:255',
            'employer_name'        => 'nullable|string|max:255',
            'employer_file_no'     => 'nullable|string|max:255',
            'employer_designation' => 'nullable|string|max:255',
        ];
    }
}
