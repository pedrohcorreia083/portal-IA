<?php

namespace App\Policies;

use App\Models\ServiceOrder;
use App\Models\User;

class ServiceOrderPolicy
{
    public function technicalAnalysis(User $user, ServiceOrder $serviceOrder)
    {
        // Apenas usuários técnicos podem acessar
        return $user->isTechnical() && $serviceOrder->canBeAnalyzed();
    }
}