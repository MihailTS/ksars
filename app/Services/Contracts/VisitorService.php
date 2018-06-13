<?php
namespace App\Services\Contracts;

use App\Http\Requests\VisitorReceiveRequest;
use App\Http\Requests\VisitorReceiveTimeRequest;
use App\Visitor;
use Illuminate\Support\Collection;

interface VisitorService
{
    public function receiveVisit(VisitorReceiveRequest $request);
    public function receiveTimeVisit(VisitorReceiveTimeRequest $request);
}