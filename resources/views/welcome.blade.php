<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to DevAmirix</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1c92d2, #f2fcfe);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #333;
            padding: 20px;
        }

        header {
            margin-bottom: 40px;
        }
        header img {
            max-width: 300px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.125rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #1c92d2;
            color: #fff;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn:hover {
            background-color: #147ab8;
            transform: translateY(-3px);
        }

        footer {
            margin-top: 40px;
            font-size: 0.875rem;
            color: #555;
            text-align: center;
        }
    </style>
</head>
<body>
<header>
    <img src="{{ url('logo.png') }}" alt="DevAmirix Logo">
</header>

<div class="container">
    <h1>Welcome to DevAmirix</h1>
    <p>
        DevAmirix is a modern PHP framework designed for innovative and scalable web development.<br>
        Empower your projects with speed, security, and simplicity.
    </p>
    <a href="https://github.com/DevAmiri-iran/DevAmirix" class="btn">Get Started</a>
</div>

<footer>
    &copy; 2025 DevAmirix. All Rights Reserved.
</footer>
</body>
</html>
