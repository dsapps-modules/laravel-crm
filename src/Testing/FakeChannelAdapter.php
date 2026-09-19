<?php

namespace DsApps\LaravelCrm\Testing;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Contracts\SendResult;
use DsApps\LaravelCrm\Models\Message;

class FakeChannelAdapter implements ChannelAdapter
{
    /** @var list<int> */
    public array $sent = [];
    public string $nextStatus = 'sent';

    public function send(Message $message): SendResult
    {
        $this->sent[] = $message->id;
        return new SendResult($this->nextStatus, 'fake-'.$message->id);
    }

    public function capabilities(): array { return ['delivery_status' => true, 'read_status' => false]; }
}
