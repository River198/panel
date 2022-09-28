<?php

namespace river\Http\Requests\Admin\Settings;

use river\Http\Requests\Admin\AdminFormRequest;

class AdvancedSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return all of the rules to apply to this request's data.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'recaptcha:enabled' => 'required|in:true,false',
            'recaptcha:secret_key' => 'required|string|max:191',
            'recaptcha:website_key' => 'required|string|max:191',
            'river:guzzle:timeout' => 'required|integer|between:1,60',
            'river:guzzle:connect_timeout' => 'required|integer|between:1,60',
            'river:client_features:allocations:enabled' => 'required|in:true,false',
            'river:client_features:allocations:range_start' => [
                'nullable',
                'required_if:river:client_features:allocations:enabled,true',
                'integer',
                'between:1024,65535',
            ],
            'river:client_features:allocations:range_end' => [
                'nullable',
                'required_if:river:client_features:allocations:enabled,true',
                'integer',
                'between:1024,65535',
                'gt:river:client_features:allocations:range_start',
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'recaptcha:enabled' => 'reCAPTCHA Enabled',
            'recaptcha:secret_key' => 'reCAPTCHA Secret Key',
            'recaptcha:website_key' => 'reCAPTCHA Website Key',
            'river:guzzle:timeout' => 'HTTP Request Timeout',
            'river:guzzle:connect_timeout' => 'HTTP Connection Timeout',
            'river:client_features:allocations:enabled' => 'Auto Create Allocations Enabled',
            'river:client_features:allocations:range_start' => 'Starting Port',
            'river:client_features:allocations:range_end' => 'Ending Port',
        ];
    }
}
