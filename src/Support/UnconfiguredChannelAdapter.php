<?php

namespace DsApps\LaravelCrm\Support;

use DsApps\LaravelCrm\Contracts\ChannelAdapter;
use DsApps\LaravelCrm\Contracts\SendResult;
use DsApps\LaravelCrm\Models\Message;

final class UnconfiguredChannelAdapter implements ChannelAdapter
{
    public function send(Message $message): SendResult { throw new \RuntimeException('Nenhum provedor de canal foi configurado.'); }
    public function capabilities(): array { return []; }
}
