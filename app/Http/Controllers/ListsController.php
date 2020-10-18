<?php

namespace App\Http\Controllers;

use App\Models\Lists\ListElement;
use App\Models\OneC;
use Illuminate\Http\Request;
use PHPUnit\Util\Json;

class ListsController extends Controller
{
    public function addElements()
    {
        $elements = OneC::getData();
        foreach ($elements as $elementData) {
            ListElement::create($elementData);
        }
    }

    public function getElement()
    {
        $data = OneC::getData();
        $element = ListElement::get($data);
        return response($element->element_guid, 200,)
            ->header('Content-Type', 'application/json');
    }

}
