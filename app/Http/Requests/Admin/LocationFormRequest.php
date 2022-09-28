<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Http\Requests\Admin;

use river\Models\Location;

class LocationFormRequest extends AdminFormRequest
{
    /**
     * Setup the validation rules to use for these requests.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->method() === 'PATCH') {
            return Location::getRulesForUpdate($this->route()->parameter('location')->id);
        }

        return Location::getRules();
    }
}
