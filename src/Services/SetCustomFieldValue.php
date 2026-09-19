<?php

namespace DsApps\LaravelCrm\Services;

use DsApps\LaravelCrm\Models\CustomField;
use DsApps\LaravelCrm\Models\CustomFieldValue;
use InvalidArgumentException;

final class SetCustomFieldValue
{
    private const TYPES = ['text', 'number', 'date', 'boolean', 'select'];

    public function execute(CustomField $field, string $entityType, int $entityId, mixed $value): CustomFieldValue
    {
        if (! in_array($field->type, self::TYPES, true)) throw new InvalidArgumentException('Tipo de campo personalizado inválido.');
        if ($field->type === 'number' && ! is_numeric($value)) throw new InvalidArgumentException('O campo numérico aceita apenas números.');
        if ($field->type === 'boolean' && ! is_bool($value)) throw new InvalidArgumentException('O campo booleano aceita apenas true/false.');
        if ($field->type === 'select' && ! in_array($value, $field->options ?? [], true)) throw new InvalidArgumentException('Valor não permitido para o campo de seleção.');
        return CustomFieldValue::updateOrCreate(['custom_field_id' => $field->id, 'entity_type' => $entityType, 'entity_id' => $entityId], ['value' => ['value' => $value]]);
    }
}
