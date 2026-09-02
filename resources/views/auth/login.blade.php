<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SIGAP</title>
</head>

<body>

    <h1>LOGIN SIGAP</h1>

    <form action="{{ route('login.process') }}" method="POST">
        @csrf

        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <br>

        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <br>

        <button type="submit">LOGIN</button>
    </form>

</body>

</html>