<?php
namespace App\Http\Requests;

use App\Rules\MatchOldPassword;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_password'      => ['required', new MatchOldPassword],
            'new_password'          => ['required', 'min:6'],
            'password_confirmation' => ['same:new_password'],
        ];
    }
}
