<?php

namespace App\Http\Controllers;

use App\Models\Lists\ListsElement;
use Illuminate\Http\Request;

class ListsController extends Controller
{
    public function addElements(Request $request)
    {
        $elements = $request->post(['data']);
        foreach ($elements as $elementData) {
            ListsElement::create($elementData);
        }
    }

    public function getElement(Request $request)
    {
        $list = ListsElement::get($request->post(['data']));
        echo $list->element_guid;
    }
}
