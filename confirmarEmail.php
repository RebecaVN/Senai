<?php
include("conexao.php");
$pSenhaIncorreta = false;
$email="";
if(isset($_GET["emailconf"])){
    $email=$_GET["emailconf"];
}

if (isset($_GET["codigo"]) && isset($_GET["emailconf"])) {
    $codigoInserido = $_GET["codigo"];

    echo "<script>console.log(aqui)</script>";

    $sqlBuscaConta = "SELECT * FROM aluno WHERE email = '$email' AND conta_pendente = 1";
    $preparaSqlBuscaConta = $mysqli->query($sqlBuscaConta);

    if ($busca = mysqli_fetch_assoc($preparaSqlBuscaConta)) {
        $codigoConf = $busca['cod_confimacao'];

        if ($codigoInserido == $codigoConf) {
            // Código correto, prosseguir com o cadastro na tabela de alunos
            $id = $busca['id'];
            $nome = $busca['nome'];
            $senha = $busca['senha'];

            $sqlDeletaContaPendente = "UPDATE aluno SET conta_pendente=0, cod_confimacao='' WHERE id = '$id' ";

            if ($mysqli->query($sqlDeletaContaPendente)) {
                // Sucesso - redirecionar 
                header('Location: direcionamentoLogin.php?email='.$email.'&senha='.$senha);
            } else {
                // Erro na comunicação com o banco
                header('Location: login.php?e=7');
            }
        } else {
            // Código incorreto
            $pSenhaIncorreta = true;
        }
    } else {
        header('Location: login.php?e=2');
    }
};
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css" />
    <script src="https://unpkg.com/scrollreveal"></script>
    <link rel="icon" type="image/png" href="img/favicon/favicon-16x16.png"/>
    <title>Confirmação de email</title>
</head>
<body>
<header class="topo-index">
      <a href="inicialAluno.html"
        ><img width="200" class="logo-inicial" src="img/svg/logo_senai_vermelho.svg" /></a>
    </header>
    <main  class="conteudoConfirmarEmail">
   
        <form action="" method="GET">
        <div class="areaCodTurmaNova">
          <h2>Confirmação de Email</h2>
          <label 
            >Para que possamos validar seu email, insira o código gerado pelo
            sistema e enviado em seu email cadastrado.</label
          >
          <input type="email" value="<?php echo $email?>" name= "emailconf" hidden>
            <input type="text" maxlength="6"  placeholder="Insira o código aqui" name="codigo" required>
        </div>
            <div class="verificacaoEmail">
                <div class="botoesTurmaNova">
                <input type="submit" value="Confirmar" name="confirmar">
                </div>
            </div>
            
        </form>
    

  
    
   
        <p>Não foi enviado? Clique em <a href="direcionamentoLogin.php?email=<?php echo $email ?>&reenviarCodigo=true">Reenviar Código</a></p>
   

    


    <script src="js/script.js"></script>
    <?php
    if ($pSenhaIncorreta) {
        echo '<script>erroLogin(8)</script>';
    }
    ?>
    </main>
    <script src="js/reveal.js"></script>
</body>
</html>
<?php
    if (filter_input(INPUT_GET, 'e')) {
        $mensagem_erro = filter_input(INPUT_GET, 'e');
        echo '<script>erroLogin('.$mensagem_erro.')</script>';
    }
?>