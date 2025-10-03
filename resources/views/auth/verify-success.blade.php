<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 { color: #28a745; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Email Verified ✅</h1>
        <p>Your email has been verified successfully.</p>
        <a href="{{ url('/login') }}">Click here to login</a>
    </div>
</body>
</html>
