<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparql Endpoint</title>
    <link href="https://unpkg.com/@triply/yasgui/build/yasgui.min.css" rel="stylesheet" type="text/css" />
    <script src="https://unpkg.com/@triply/yasgui/build/yasgui.min.js"></script>
    <style>
        .yasgui .autocompleteWrapper {
            display: none !important;
        }
    </style>
</head>
<body>
    <div id="yasgui"></div>
    <script>
        const yasgui = new Yasgui(document.getElementById("yasgui"), {
            requestConfig: { endpoint: "{{ route('ldog.sparql') }}" },
            copyEndpointOnNewTab: false
        });
    </script>
</body>
</html>

