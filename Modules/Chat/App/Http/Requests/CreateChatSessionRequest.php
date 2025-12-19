<?php
namespace Modules\Chat\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'meta' => 'required|array',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
