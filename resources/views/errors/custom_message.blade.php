<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notice' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background-color: #f7f8fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 450px;
            padding: 40px 30px;
            text-align: center;
            animation: fadeIn 0.6s ease;
        }
        h2 {
            color: {{ $color ?? '#333' }};
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            font-size: 1rem;
            margin-bottom: 25px;
        }
        a {
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        a:hover {
            background-color: #0056b3;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>{{ $title ?? 'Notice' }}</h2>
        <p>{{ $message ?? 'Please try again later.' }}</p>
        <a href="{{ url('/') }}">Return to Homepage</a>
    </div>
</body>
</html>
