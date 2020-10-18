<?php

namespace App\Models\Lists;

use App\Models\Bitrix;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ListElement extends Model
{
    protected $fillable = [
        'client_id',
        'block_code',
        'element_code',
        'element_guid',
        'name'
    ];

    private array $params;
    private array $requestData;
    private static ListBlock $block;

    public static function create($data)
    {
        /** @var ListElement $element */
        $element = ListElement::query()
            ->where([
                'element_guid' => $data['FIELDS']['GUID'],
                'block_code' => $data['IBLOCK_CODE']
            ])
            ->firstOrNew();
        $element->requestData = $data;
        if (!$element->exists) $element->setProperties();
        else $element->setParams();
        self::setBlock($element);
        $element->add();
    }

    public static function get($where)
    {
        return self::query()->where($where)->first();
    }

    public function getParams()
    {
        return $this->params;
    }

    private function add()
    {
        $method = $this->exists ? 'update' : 'add';
        $params = self::$block->mapFieldsWithParams();
        $response = Bitrix::request('lists.element.' . $method, $params);
        if (!$this->exists && $response) {
            $this->element_id = $response;
        }
        $this->save();
    }

    private function getElementData()
    {
        $method = 'lists.element.get';
        $data = $this->params;
        $response = Bitrix::request($method, $data);
        return $response[0];
    }

    private function setProperties()
    {
        $blockCode = $this->requestData['IBLOCK_CODE'];
        $elementsCount = count(ListElement::query()->where(['block_code' => $blockCode])->get());
        $elementsCount++;
        $this->fill([
            'client_id' => Client::getID(),
            'block_code' => $blockCode,
            'element_code' => $blockCode . '_element_' . $elementsCount,
            'element_guid' => $this->requestData['FIELDS']['GUID'],
            'name' => $this->requestData['FIELDS']['NAME']
        ]);

        $this->setParams();
    }

    private function setParams()
    {
        $this->params = array(
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_CODE' => $this->block_code,
            'ELEMENT_CODE' => $this->element_code,
            'FIELDS' => $this->requestData['FIELDS']
        );
    }

    private static function setBlock($element): void
    {
        if (empty(self::$block) || self::$block->block_code != $element->block_code) {
            self::$block = ListBlock::get($element);
        } else {
            self::$block->setElement($element);
        }
    }
}
