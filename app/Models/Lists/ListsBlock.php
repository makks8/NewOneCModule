<?php

namespace App\Models\Lists;

use App\Models\Bitrix;
use Illuminate\Database\Eloquent\Model;

class ListsBlock extends Model
{
    public $block_id;
    public $block_code;
    public $fields;

    private $params;

    /**
     * ListsBlock constructor.
     * @param ListsElement $element
     */
    public function __construct($element = null)
    {
        parent::__construct();
        if (!empty($element)) {
            $this->block_code = $element->block_code;
            $this->params = $element->getParams();
        }
    }

    public static function tableName()
    {
        return 'lists_block';
    }

    public static function getBlock($element)
    {
        $listsBlock = self::query()->where(['block_code' => $element->block_code])->get();
        if (empty($listsBlock)) {
            $listsBlock = new ListsBlock($element);
            $listsBlock->createList();
            return $listsBlock;
        } else {
            return $listsBlock;
        }
    }

    private function createList()
    {
        $method = 'lists.add';
        $params = $this->params;
        $params['FIELDS']['NAME'] = $this->block_code;
        $blockID = Bitrix::request($method, $params);
        $this->block_id = $blockID;
        $fields = $params['FIELDS'];
        unset($fields['NAME']);
        foreach ($fields as $fieldName => $value) {
            $fieldID = $this->addField($fieldName, $value);
            $this->fields[$fieldName] = $fieldID;
        }
        $this->fields = json_encode($this->fields);
        $this->save();
    }

    private function addField($fieldName, $value)
    {
        $method = 'lists.field.add';
        $type = 'S';
        if (is_numeric($value)) {
            $type = 'N';
        }
        $params = $this->params;
        $params['FIELDS'] = [
            'NAME' => $fieldName,
            'TYPE' => $type,
            'SORT' => '500',
            'CODE' => $fieldName
        ];
        return Bitrix::request($method, $params);
    }
}
