<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\Tagging;
use InvalidArgumentException;

final class TagEntity
{
    private const TYPES = ['contact', 'company', 'opportunity', 'task'];

    public function attach(int $tagId, string $entityType, int $entityId): Tagging
    {
        if (! in_array($entityType, self::TYPES, true)) throw new InvalidArgumentException('Tipo de entidade não permitido.');
        return Tagging::firstOrCreate(['tag_id' => $tagId, 'entity_type' => $entityType, 'entity_id' => $entityId]);
    }
}
