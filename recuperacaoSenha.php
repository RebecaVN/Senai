<?php
//envio do email com phpMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
$form2=false;
if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['email'])){
    include('conexao.php');
    $email = $_GET['email'];
    $email = mysqli_real_escape_string($mysqli, $email); // Importante escapar o valor para evitar SQL injection

    $tabela = identificaTipoUsuario($email);
    // echo $tabela;
    if($tabela != 'nao_encontrado'){

        $codigoRec = substr(password_hash(time(), PASSWORD_DEFAULT), -7, -1);


        $sqlUpRec = "UPDATE $tabela SET codigo_recuperacao='$codigoRec' WHERE email = '$email';";
        

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                    
            $mail->isSMTP();                                           
            $mail->Host       = 'smtp-mail.outlook.com';                    
            $mail->SMTPAuth   = true;                                   
            $mail->Username   = 'EMAIL';              
            $mail->Password   = 'SENHA';                                
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
            $mail->Port       = 587;                                    

            $mail->SMTPSecure = 'tls'; 
            
            //Recipients
            $mail->setFrom('EMAIL', 'Monitoramento IOT');
            $mail->addAddress($email, 'Usuario'); 
            $mail->addReplyTo('EMAIL', 'Monitoramento IOT');

            //Content
            $mail->isHTML(true);                                  
            $mail->Subject = 'Recuperacao de Senha';
            $mail->Body    = ' <html>
                                <head>
                                    <meta charset="UTF-8">
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                    <title>Confirmação de email</title>
                                </head>
                                <body>
                                    <h3>Recuperação de senha</h3>
                                    <p>Seu código para recuperação de senha é: '.$codigoRec.'</p>
                                    <p>Atenção: não compartilhe o código acima com ninguém</p>
                                    <a href="recuperacaoSenha.php?emailRec='.$email.'">Acesse o site</a>
                                </body>
                            </html>';
            $mail->AltBody = 'Confirme seu email';

            if($mysqli->query($sqlUpRec)){
                $mail->send();
                $form2 = true;
            }else{
                $form2 = false;
                echo "Erro na inserção: ";
            }
        } catch (Exception $e) {
            $form2 = false;
        }
    }else{
        header('Location: login.php?e=6');
    }
}
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codigo']) && isset($_POST['senha']) && isset($_POST['email'])){
    // echo '<script>console.log("aqui")</script>';
    include('conexao.php');
    $email = $_POST['email'];
    $email = mysqli_real_escape_string($mysqli, $email); 

    $tabela = identificaTipoUsuario($email);
    
    if($tabela != 'nao_encontrado'){
        
        
        $sqlUser = "SELECT * FROM $tabela WHERE email = '$email'";
        $preparaSqlUser = $mysqli->query($sqlUser);
        $codigoUser;
        $idUser;
        while($busca = mysqli_fetch_assoc($preparaSqlUser)){
            $codigoUser = $busca['codigo_recuperacao'];
            $idUser = $busca['id'];
        }
        if($codigoUser == $_POST['codigo']){
            $novaSenha = $_POST['senha'];
            $novaSenha = mysqli_real_escape_string($mysqli, $novaSenha); 
            $novaSenha = password_hash($novaSenha, PASSWORD_DEFAULT);
            $sqlNovaSenha = "UPDATE $tabela SET senha = '$novaSenha', codigo_recuperacao='' WHERE id = $idUser";
            if($mysqli->query($sqlNovaSenha)){
                header('Location: login.php?e=12');
            }else{
                header('Location: login.php?e=3');
            }
        }else{
            header('Location: login.php?e=7');
        }
    }else{
        header('Location: login.php?e=6');
    }

}

function identificaTipoUsuario($email){
    include('conexao.php');
    $sqlAluno = "SELECT * FROM aluno WHERE email = '$email'";
    $sqlProfessor = "SELECT * FROM professor WHERE email = '$email'";
    $sqlGestor = "SELECT * FROM gestor WHERE email = '$email'";

    $preparaAluno = $mysqli->query($sqlAluno);
    $preparaProfessor = $mysqli->query($sqlProfessor);
    $preparaGestor = $mysqli->query($sqlGestor);

    if ($preparaAluno->num_rows > 0) {
        return 'aluno';
    }else if($preparaProfessor->num_rows > 0) {
        return 'professor';
    }else if($preparaGestor->num_rows > 0){
        return 'gestor';
    }else{
        return 'nao_encontrado';
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css" />
    <title>Recuperação de senha</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="icon" type="image/png" href="img/favicon/favicon-16x16.png"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <header class="topo-index">
        <a href="inicialAluno.html"
            > <img width="200" class="logo-inicial" src="img/svg/logo_senai_vermelho.svg" />
        </a>
    </header>

    <main class="conteudoConfirmarSenha">
    <?php
        if(!$form2){
    ?>
        <form method="GET">
            <div class="areaCod">
                <h2>Recuperação Senha</h2>
                <label>Insira seu email</label>
                <input type="mail" name="email" placeholder="Insira seu email">
            </div>
            <div class="verificacaoSenha">
                <div class="botoesSenha">
                    <input type="submit" value="Enviar Código" id="Btn_confirmar" class="btn btn-primary" data-toggle="modal" data-target="#senhaModal">
                </div>
            </div>
        </form>
    <?php }else{ ?>
        <form method="POST">
            <div class="areaCod">
                <h2>Redefinir Senha</h2>
                <label>Para que possamos validar seu usuário, insira o código gerado pelo sistema e enviado em seu email</label>

                <input type="email" value="<?php echo $email?>" name= "email" hidden>
                <input type="text" name="codigo" placeholder="Insira o código aqui" id="codigo_recuperacao">
                <input type="text" name="senha" placeholder="Nova senha">
                <input type="text" placeholder="Confirmar senha">
            </div>

            <div class="verificacaoSenha">
                <div class="botoesSenha">
                    <input type="submit" value="Cadastrar Senha" id="Btn_confirmar" class="btn btn-primary" data-toggle="modal" data-target="#senhaModal">
                </div>
            </div>
        </form>
    <?php } ?>
    </main>

    <!-- to do: verificar se as duas senhas sao iguais -->
    <script src="js/script.js"></script>
</body>
</html>

<?php
    if (filter_input(INPUT_GET, 'e')) {
        $mensagem_erro = filter_input(INPUT_GET, 'e');
        echo '<script>erroLogin('.$mensagem_erro.')</script>';
    }
?>
