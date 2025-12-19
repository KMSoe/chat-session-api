<?php
namespace Modules\Chat\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Chat\App\Enums\ChatSessionStates;

class UpdateChatSessionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $this->merge([
            'session_uuid' => $this->route('session_uuid'),
        ]);

        return [
            'session_uuid' => 'required|exists:chat_sessions,session_uuid',
            'state'        => ['required', Rule::in(ChatSessionStates::values())],
            'meta'         => 'required|array|min:1',
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
