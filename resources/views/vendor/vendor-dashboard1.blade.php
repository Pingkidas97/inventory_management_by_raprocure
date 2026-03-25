<!DOCTYPE html>
<html>

<head>
    <title>Vendor Dashboard</title>
</head>

<body>
    <h1>Welcome to Vendor Dashboard</h1>
    <p>Hello, {{ $user->name }}</p>

    <!-- Logout form -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>

</html>