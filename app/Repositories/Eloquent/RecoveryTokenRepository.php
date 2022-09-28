<?php

namespace river\Repositories\Eloquent;

use river\Models\RecoveryToken;

class RecoveryTokenRepository extends EloquentRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return RecoveryToken::class;
    }
}
