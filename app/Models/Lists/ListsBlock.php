<?php

namespace App\Models\Lists;

use App\Models\Bitrix;
use Illuminate\Database\Eloquent\Model;

class ListsBlock extends Model
{
    private ListsElement $element;

    public static function get($element)
    {
        /** @var ListsBlock $listsBlock */
        $listsBlock = self::query()->where(['block_code' => $element->block_code])->firstOrNew();
        $listsBlock->element = $element;
        if (!$listsBlock->exists) $listsBlock->create();
        return $listsBlock;
    }

    public function mapFieldsWithParams(): array
    {
        $fields = json_decode($this->fields, true);
        $params = $this->element->getParams();

        foreach ($params['FIELDS'] as $fieldName => $value) {
            if (!empty($fields[$fieldName])) {
                $fieldID = $fields[$fieldName];
                $params['FIELDS'][$fieldID] = $value;
                unset($params['FIELDS'][$fieldName]);
            } else {
                continue;
            }
        }
        return $params;
    }

    private function create()
    {
        $method = 'lists.add';

        $blockCode = $this->element->block_code;
        $params = $this->element->getParams();
        $params['FIELDS']['NAME'] = $blockCode;

        $blockID = Bitrix::request($method, $params);

        $fields = $params['FIELDS'];
        unset($fields['NAME']);
        foreach ($fields as $fieldName => $value) {
            $fieldID = $this->addField($fieldName, $value);
            $fields[$fieldName] = $fieldID;
        }

        $this->fill([
            'fields' => json_encode($fields),
            'block_id' => $blockID,
            'block_code' => $blockCode
        ]);
        $this->save();
    }

    private function addField($fieldName, $value)
    {
        $method = 'lists.field.add';
        $type = 'S';
        if (is_numeric($value)) {
            $type = 'N';
        }
        $params = $this->element->getParams();
        $params['FIELDS'] = [
            'NAME' => $fieldName,
            'TYPE' => $type,
            'SORT' => '500',
            'CODE' => $fieldName
        ];
        return Bitrix::request($method, $params);
    }


}
