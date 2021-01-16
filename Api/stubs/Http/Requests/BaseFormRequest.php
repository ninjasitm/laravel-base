<?php
namespace App\Http\Requests;

use InfyOm\Generator\Request\APIRequest as FormRequest;
use App\User;

class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(User $user, $model = null)
    {
        if (!$model) {
            return true;
        }

        if ($this->isMethod('post') || $this->isMethod('put')) {
            return $user->can('create', $model) || $user->can('update');
        }

        if ($this->isMethod('delete')) {
            return $user->can('delete', $model);
        }

        return true;
    }

    public function rules()
    {
        return [];
    }
}