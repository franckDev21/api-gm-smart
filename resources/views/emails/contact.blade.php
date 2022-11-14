<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
  <style>
    .container{
      margin: 0 auto;
      width: calc(100% - 45px);
      max-width: 900px;
    }
    .primary{
      color: #ac3265;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="{{ asset('logo3.png') }}" alt="">
    <h2>NOUVEAU MESSAGE DE CONTACT !</h2>
    <hr>
    <h1>Bonjour ALL Heigth corp, <span class="primary">{{ $data['name'] }}</span></h1>
    <p style="padding: 10px; background-color:#eee; border-radius: .4rem;">
      {{ $data['content'] }}
    </p>
  </div>
</body>
</html>