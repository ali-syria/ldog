<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ request()->getUri() }}</title>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>Property</th>
                <th>value</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
                <tr>
                    <td>
                        <a href="{{ $result->property->getUri() }}">
                            @if(isset($result->propertyText))
                                {{ $result->propertyText->getValue() }}
                            @else
                                {{ $result->property->getUri() }}
                            @endif
                        </a>
                    </td>
                    <td>
                        @if($result->value instanceof \EasyRdf\Resource)
                        <a href="{{ $result->value->getUri() }}">
                            @if(isset($result->valueText))
                                {{ $result->valueText->getValue() }}
                            @else
                                {{ $result->value->getUri() }}
                            @endif
                        </a>
                        @else
                            {{ $result->value->getValue() }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
