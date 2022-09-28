<?php

namespace river\Http\Requests\Admin\Settings;

use Illuminate\Validation\Rule;
use river\Traits\Helpers\AvailableLanguages;
use river\Http\Requests\Admin\AdminFormRequest;

class BaseSettingsFormRequest extends AdminFormRequest
{
    use AvailableLanguages;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'app:name' => 'required|string|max:191',
            'river:auth:2fa_required' => 'required|integer|in:0,1,2',
            'app:locale' => ['required', 'string', Rule::in(array_keys($this->getAvailableLanguages()))],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'app:name' => 'Company Name',
            'river:auth:2fa_required' => 'Require 2-Factor Authentication',
            'app:locale' => 'Default Language',
        ];
    }
}
