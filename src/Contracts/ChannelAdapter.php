<?php

namespace DsApps\LaravelCrm\Contracts;

use DsApps\LaravelCrm\Models\Message;

interface ChannelAdapter
{
    public function send(Message $message): SendResult;
    public function capabilities(): array;
}
