@extends('layout')

@section('title')
    Клиенты
@endsection

@section('links')

@endsection

@section('content')
    @if (count($clients) > 0 )
        <p>Список клиентов:</p>
        <table>
            <tr>
                <th>Клиент</th>
            </tr>
            @foreach ($clients as $count => $client)
                <tr>
                    <td>
                        {{$count + 1}}
                    </td>
                    <td>
                        {{$client->name}}
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        Список клиентов пуст!
    @endif
@endsection
