<?php
// On démarre une session
session_start();
$page = 'connectaccount';
$titre = "Connexion à mon compte";
?>
<!doctype html>
<html lang="fr-BE">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/marketplace/uploads/icon.png" type="image/png">
    <title><?php if(isset($titre)){echo $titre;} else {echo 'market';} ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="css/connect.css" rel="stylesheet">
  </head>
<body>
<a href="accueil.php" class="btn btn-link position-absolute top-0 start-0 m-3" style="color:darkorange; text-decoration: none;">
  <i class="fas fa-arrow-left me-1"></i> Accueil
</a>
<div class="d-flex justify-content-center align-items-center vh-100">

    <!-- From Uiverse.io by alexruix --> 
    <div class="form-box">
        <form class="form" action="connect/login.php" method="post">
            <span class="title">Se Connecter</span>
            <span class="subtitle">Connecter vous à votre compte si vous en avez un.</span>
            <div class="form-container">
		    	<input type="email" class="input" name="mail_user" placeholder="Email" value="<?php echo (isset($_GET['mail_user']))?$_GET['mail_user']:"" ?>">
		    	<input type="password" class="input" name="password_user" placeholder="Password" value="<?php echo (isset($_GET['password_user']))?$_GET['password_user']:"" ?>">
            </div>
            <button type="submit">Se Connecter</button>
            <p><a class="forgot-password" style="color:black" href="connect/forgot.php">Mot de passe oublié ?</a></p>

            <?php if(isset($_GET['error']) && !empty($_GET['error'])){ ?>
              <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
              </div>
            <?php } ?>

            
		        <?php if(isset($_GET['success'])){ ?>
    		      <div class="alert alert-success" role="alert">
			          <?php echo $_GET['success']; ?>
			        </div>
		          <?php } ?>
        </form>
        <div class="form-section d-flex justify-content-center">
            <p>Pas de compte? <a href="connect/index.php">S'inscrire</a></p>
        </div>
    </div>
<?php include('connect/footer.php'); ?>