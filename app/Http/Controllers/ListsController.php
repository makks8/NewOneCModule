<?php

namespace App\Http\Controllers;

use App\Models\Lists\ListElement;
use App\Models\OneC;

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
        return response($element->getAttributes(), 200,)
            ->header('Content-Type', 'application/json');
    }

}
