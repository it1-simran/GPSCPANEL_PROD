<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Expired</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            background-color: #f7f8fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff;
            padding: 40px 50px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            max-width: 420px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.6s ease;
        }
        h2 {
            color: #d9534f;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            font-size: 1rem;
            margin-bottom: 25px;
        }
        a {
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
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
    <div class="container">
        <h2>{{ $message ?? 'This link has expired or is invalid.' }}</h2>
        <p>Please request a new link to continue.</p>
    </div>
</body>
</html>

