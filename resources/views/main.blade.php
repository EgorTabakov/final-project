<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

</head>

<body>
    <h1>{{ __('Attention') }}!</h1>
    <p>{{ __('There is no visual view for this site') }}
    </p>
</body>

</html>
