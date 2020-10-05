<?php

namespace App\Models\Lists;

use App\Models\Bitrix;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

class ListsElement extends Model
{
    public $name;
    public $client_id;
    public $element_code;
    public $element_guid;
    public $block_code;

    private $params;


    public function __construct($data = array())
    {
        parent::__construct();
        if (!empty($data)) {
            $blockCode = $data['IBLOCK_CODE'];
            $elementsCount = count(ListsElement::query()->where(['block_code' => $blockCode])->get());
            $elementsCount++;
            $this->client_id = Client::getClientId();
            $this->block_code = $blockCode;
            $this->element_code = $blockCode . '_element_' . $elementsCount;
            $this->element_guid = $data['FIELDS']['GUID'];
            $this->name = $data['FIELDS']['NAME'];
            $this->setFields($data);
        }
    }

    public static function getElement($data)
    {
        $element = ListsElement::query()
            ->where([
                'element_guid' => $data['FIELDS']['GUID'],
                'block_code' => $data['IBLOCK_CODE']])
            ->get();
        if (empty($element)) {
            $element = new ListsElement($data);
        } else {
            $element->setFields($data);
        }
        return $element;
    }

    public static function getElementByID($blockCode, $id)
    {
        return self::query()->where(['bitrix_id' => $id, 'block_code' => $blockCode])->get();
    }

    public function addElement()
    {
        $method = 'lists.element.add';
        if (property_exists($this, 'id')) {
            $method = 'lists.element.update';
        }
        $data = $this->getParams();
        $listsBlock = ListsBlock::getBlock($this);
        $fields = $listsBlock->fields;
        $fields = json_decode($fields, true);

        foreach ($data['FIELDS'] as $fieldName => $value) {
            if (!empty($fields[$fieldName])) {
                $fieldID = $fields[$fieldName];
                $data['FIELDS'][$fieldID] = $value;
                unset($data['FIELDS'][$fieldName]);
            } else {
                continue;
            }
        }

        $response = Bitrix::request($method, $data);
        if (!empty($response)) {
            $this->save();
        }
    }

    public function getParams()
    {
        return $this->params;
    }

    private function setFields($data)
    {
        $this->params = $data['FIELDS'];
        $this->setParams();
    }

    private function getElementData()
    {
        $method = 'lists.element.get';
        $data = $this->params;
        $response = Bitrix::request($method, $data);
        return $response[0];
    }

    private function setParams()
    {
        $this->params = array(
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $this->block_code,
            'ELEMENT_CODE' => $this->element_code,
            'FIELDS' => $this->params
        );
    }
}
