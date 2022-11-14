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
    <h1>Salut {{ $user->firstname }} {{ $user->lastname }} , voici vos identifiants de connexion : </h1>
    <hr>
    <p style="padding: 10px; background-color:#eee; border-radius: .4rem;">
      Prenom : {{ $user->firstname }} <br>
      Nom : {{ $user->firstname }} <br>
      Email : {{ $user->email }} <br>
      Mot de passe : {{ $password }} <br>
    </p>

    <h3>Connecter vous directement <a class="primary" href="https://gm-smart.vercel.app/">içi</a>!</h3>
  </div>
</body>
</html>