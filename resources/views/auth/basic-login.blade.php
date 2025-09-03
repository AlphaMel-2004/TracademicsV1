<!DOCTYPE html>
<html>
<head>
    <title>Basic Login Test</title>
</head>
<body>
    <h1>Basic Login Test</h1>
    
    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        
        <p>
            <label>Email:</label><br>
            <input type="email" name="email" value="{{ old('email') }}" style="width: 300px; height: 30px; font-size: 16px;">
        </p>
        
        <p>
            <label>Password:</label><br>
            <input type="password" name="password" style="width: 300px; height: 30px; font-size: 16px;">
        </p>
        
        <p>
            <button type="submit" style="padding: 10px 20px; font-size: 16px;">Login</button>
        </p>
    </form>
    
    <hr>
    
    <p><strong>Debug Info:</strong></p>
    <p>CSRF Token: {{ csrf_token() }}</p>
    <p>Session ID: {{ session()->getId() }}</p>
    <p>Time: {{ now() }}</p>
</body>
</html>
