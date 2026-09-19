<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Services\CrmIndicators;
use Illuminate\Routing\Controller;

class ReportController extends Controller
{
    public function summary(CrmIndicators $indicators): array { return $indicators->summary(); }
}
