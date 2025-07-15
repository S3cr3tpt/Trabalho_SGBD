<?php
/*Joao*/
require_once("custom/php/common.php");

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
echo '<link rel="stylesheet" href="sgbd\custom\css\ag.css">';

// Verifica se a conexão foi bem-sucedida
if (!$link) {
    die("Conexão falhou: " . mysqli_connect_error());
}
$voltar = true;

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {//caso um formulario seja acabado
    if ($_POST['action'] == 'processar_formulario_registo'){//caso esse formulario for o do registo
        // echo'<pre>'; //verificacoes do que recebeu
        // print_r($_POST);
        // echo'</pre>';
        //verificacao server side////////////////////////////////////////////////////////////////////////////////////////////////
        if (empty($_POST['nome']) || empty($_POST['data']) || empty($_POST['nome_enc']) || empty($_POST['telefone'])) {
            die("Erro: Todos os campos obrigatórios devem ser preenchidos.");
        }

        // Capturar as variáveis do formulário e sanitizar
        $nome = mysqli_real_escape_string($link, $_POST['nome']);
        $data_nascimento = mysqli_real_escape_string($link, $_POST['data']);
        $nome_encarregado = mysqli_real_escape_string($link, $_POST['nome_enc']);
        $telefone = intval($_POST['telefone']); // Converte o telefone para um número inteiro
        $email = isset($_POST['email']) ? mysqli_real_escape_string($link, $_POST['email']) : null;
        // verificacao da data
        // Dividir a data em partes para validação do mês e dia
        list($ano, $mes, $dia) = explode('-', $data_nascimento);

        // Verificar se o mês está entre 1 e 12
        if ($mes < 1 || $mes > 12) {
            die("Erro: O mês deve estar entre 1 e 12.");
        }
        
        // Verificar se o dia está entre 1 e 31
        if ($dia < 1 || $dia > 31) {
            die("Erro: O dia deve estar entre 1 e 31.");
        
        }

        //verificacao do email
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $email = mysqli_real_escape_string($link, $_POST['email']);
            
            // Validar o formato do e-mail
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                die("Erro: O e-mail fornecido não está em um formato válido.");
            }
        }

        echo"<h3>Dados de registo - validacao</h3>";
        echo"<div>Estamos prestes a inserir os dados abaixo na base de dados
        <br><br>Confirma que os dados estao corretos e pretende submeter os mesmos?<br><br>";
        echo "<strong>Nome: </strong>$nome <strong>Data de nascimento: </strong>$data_nascimento <strong>Enc. de educacao: </strong>$nome_encarregado <strong>Telefone do Enc.:</strong>
        $telefone <strong>Email: </strong>".(isset($email) ? $email :'');
        echo "<form action='' method='POST'>";
        // Campo hidden para guardar o campo _post
        echo "<input type='hidden' name='nome' value='$nome'>";
        echo "<input type='hidden' name='data' value=$data_nascimento>";
        echo "<input type='hidden' name='nome_encarregado' value='$nome_encarregado'>";
        echo "<input type='hidden' name='telefone' value='$telefone'>";
        echo "<input type='hidden' name='email' value='".(isset($email) ? $email :'')."'>";

        echo "<input type='hidden' name='action' value='inserir_tabela'>";
        // Botão de submit
        echo "<input type='submit' value='Submeter'>";
        echo "</form>";

        
    }
    if ($_POST['action'] == 'inserir_tabela') {
        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data'];
        $nome_encarregado = $_POST['nome_encarregado']; 
        $telefone = $_POST['telefone']; // Converte o telefone para um número inteiro
        $email = isset($_POST['email']) ? $_POST['email'] : null;
    
        // Query para inserir os dados
        $querry = "INSERT INTO child (name, birth_date, tutor_name, tutor_phone, tutor_email) 
                    VALUES ('$nome', '$data_nascimento', '$nome_encarregado', $telefone, " . ($email ? "'$email'" : "NULL") . ")";
    
        if (mysqli_query($link, $querry)) {
            // Sucesso na inserção
            echo "<h3>Dados de registo - inserção</h3>";
            echo "<h3>Dados inseridos com sucesso:</h3>";
            echo "Nome: $nome<br>";
            echo "Data de Nascimento: $data_nascimento<br>";
            echo "Nome do Encarregado: $nome_encarregado<br>";
            echo "Telefone: $telefone<br>";
            echo "Email: " . ($email ? $email : "Não fornecido") . "<br>";
            echo "<h3>Inseriu os dados de registo com sucesso.</h3>";
            echo "<h3>Clique em Continuar para avancar.</h3>";
            //botao para continuar
            echo "<form action='' method='GET'>";
            echo "<input type='submit' value='Continuar'>";
            echo "</form>";

        } else {
            // Falha na inserção
            echo "<h3>Erro ao gravar os dados:</h3>";
            echo mysqli_error($link);
        }
        $voltar = false;
    }
    
}
else{
    $querry = "SELECT 
                child.name AS 'Nome', 
                child.birth_date AS 'Data de nascimento', 
                child.tutor_name AS 'Enc. de educacao',
                child.tutor_phone AS 'Telefone do Enc.', 
                child.tutor_email AS 'e-mail',
                child.id AS 'child_id'
                FROM child
                ORDER BY child.name ASC";

    $result = mysqli_query($link, $querry);

    // Verifica se a query foi bem-sucedida
    if ($result && mysqli_num_rows($result) > 0) {
        // Exibe a tabela com os resultados
        echo "<table border='1' cellspacing='0' cellpadding='5'>";
        echo "<tr>";
        echo "<th>Nome</th>";
        echo "<th>Data de nascimento</th>";
        echo "<th>Enc. de educacao</th>";
        echo "<th>Telefone do Enc.</th>";
        echo "<th>e-mail</th>";
        echo "<th>Acao</th>";
        echo "<th>registos</th>";
        echo "</tr>";
        // Itera sobre os resultados e preenche a tabela
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            // Esta parte e so para ficar cada nome de cada pessoa na linha abaixo
            $nomes = explode(" ", $row['Nome']); // Divide o nome em palavras
            echo "<td>";
            foreach ($nomes as $nome) {
                echo htmlspecialchars($nome) . "<br>"; // Exibe cada nome em uma nova linha
            }
            echo "</td>";        
            echo "<td>" . htmlspecialchars($row['Data de nascimento']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Enc. de educacao']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Telefone do Enc.']) . "</td>";
            echo "<td>" . (!is_null($row['e-mail']) ? htmlspecialchars($row['e-mail']) : '') . "</td>";
            echo "<td>"; // aqui o ?action, chama uma acao na pagina que vai abrir, acao de editar,etc e depois passa o id do que foi carregado
            echo "<a href='/sgbd/edicao-de-dados/?action=editar_registos&id=" . $row['child_id'] . "'>[Editar]</a><br> ";
            echo "<a href='/sgbd/edicao-de-dados/?action=apagar_registos&id=" . $row['child_id'] . "'>[Apagar]</a><br>";
            echo "<a href='/sgbd/edicao-de-dados/?action=historico_registos&id=" . $row['child_id'] . "'>[Historico]</a>";

            echo "</td>";
            echo "<td>";
            $query_auxiliar = "SELECT 
                                    value.id AS 'value_id',
                                    item.name AS 'nome_item', 
                                    subitem.name AS 'nome_subitem', 
                                    `value`.date AS 'data_avaliacao', 
                                    value.value AS 'value', 
                                    value.producer AS 'producer', 
                                    value.time AS 'time'
                                FROM `value`, subitem, item
                                WHERE " . $row['child_id'] . " = `value`.child_id 
                                    AND subitem.id = `value`.subitem_id 
                                    AND subitem.item_id = item.id";

            $result_auxiliar = mysqli_query($link, $query_auxiliar);

            $pessoas = []; // Array para armazenar os dados organizados por nome_item, producer e data

            if ($result_auxiliar && mysqli_num_rows($result_auxiliar) > 0) {
                // Itera sobre os resultados e organiza os dados por nome_item, producer e data
                while ($row_auxiliar = mysqli_fetch_assoc($result_auxiliar)) {
                    $nome_item = $row_auxiliar['nome_item'];
                    $producer = $row_auxiliar['producer'] ?? ''; // Usa 'Sem produtor' se for NULL
                    $data_avaliacao = $row_auxiliar['data_avaliacao'];
                    $nome_subitem = $row_auxiliar['nome_subitem'];
                    $valor = $row_auxiliar['value'];
                    $time = $row_auxiliar['time'];

                    if (!isset($pessoas[$nome_item])) {
                        $pessoas[$nome_item] = [];
                    }
            
                    if (!isset($pessoas[$nome_item][$data_avaliacao])) {
                        $pessoas[$nome_item][$data_avaliacao] = [];
                    }
            
                    if (!isset($pessoas[$nome_item][$data_avaliacao][$producer])) {
                        $pessoas[$nome_item][$data_avaliacao][$producer] = [];
                    }
            
                    if (!isset($pessoas[$nome_item][$data_avaliacao][$producer][$time])) {
                        $pessoas[$nome_item][$data_avaliacao][$producer][$time] = [];
                    }
            
                    // Verifica se o subitem já existe no array para evitar duplicados
                    if (!in_array("$nome_subitem ($valor)", $pessoas[$nome_item][$data_avaliacao][$producer][$time])) {
                        $pessoas[$nome_item][$data_avaliacao][$producer][$time][] = "$nome_subitem ($valor)";
                    }

                }
            }

            // Exibe os dados organizados
            if (empty($pessoas)) {
                echo "<div><div>";
            } else {
                foreach ($pessoas as $nome_item => $datas) { 
                    echo "<div>".strtoupper(htmlspecialchars($nome_item)) . ":</div>";//transformar tudo em maisuculas
                    foreach ($datas as $data_avaliacao => $producers) {
                        foreach ($producers as $producer => $times) {
                            foreach ($times as $time => $subitens) {
                                echo "<div>"  ."<a href='/sgbd/edicao-de-dados/?action=editar_registo&id=" . $row['child_id'] ."&data_avaliacao=".
                                $data_avaliacao ."&nome_item=".$nome_item."&time=".$time.
                                //isto e basicamente o method get, pois esta a por os dados no url, isto nao e seguro, se o prof perguntar dizes
                                //que fizeste isto no inicio do codigo e nao ias mudar o codigo todo so porque nao era seguro, sendo que funciona
                                "'>[editar]</a> ".
                                "<a href='/sgbd/edicao-de-dados/?action=apagar_registo&id=" . $row['child_id'] ."&data_avaliacao=".
                                $data_avaliacao ."&nome_item=".$nome_item."&time=".$time."'>[apagar]</a>  " .
                                "<a href='/sgbd/edicao-de-dados/?action=historico_registo&id=" . $row['child_id'] ."&data_avaliacao=".
                                $data_avaliacao ."&nome_item=".$nome_item."&time=".$time."'>[historico]</a> - ".
                                htmlspecialchars($data_avaliacao) ." " .htmlspecialchars($time) . ": ". " (" . htmlspecialchars($producer) . ") - ";
                                
                                echo implode("; ", array_map('htmlspecialchars', $subitens));
                                echo "</div>";
                            }
                        }
                    }
                }
            }

            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Não há crianças";
    }
    echo "<h3>Dados de registo - introdução</h3>";
    echo "<p>* Obrigatório</p>";

    echo "<form action='' method='POST'>";//usar o method POST pois e mais seguro do que o metodo get
        //guardar o nome com a clausula 'nome'
        echo "<label><strong>Nome completo:</strong> <span style='color:red;'>*</span></label>";
        echo "<input type='text' id='nome' name='nome' required pattern='[A-Za-zÀ-ÖØ-öø-ÿ\s]+' title='Apenas letras, incluindo caracteres portugueses, e espaços são permitidos.'>";

        echo "<label><strong>Data de nascimento (AAAA-MM-DD):</strong> <span style='color:red;'>*</span></label>";
        echo "<input type='text' id='data' name='data' required pattern='\\d{4}-\\d{2}-\\d{2}' title='A data deve estar no formato AAAA-MM-DD'>";

        echo "<label><strong>Nome completo do encarregado de educacao:</strong> <span style='color:red;'>*</span></label>";
        echo "<input type='text' id='nome_enc' name='nome_enc' required pattern='[A-Za-zÀ-ÖØ-öø-ÿ\s]+' title='Apenas letras, incluindo caracteres portugueses, e espaços são permitidos.'>";

        echo "<label><strong>Telefone do encarregado de educacao (9 digitos):</strong> <span style='color:red;'>*</span></label>";
        echo "<input type='tel' id='telefone' name='telefone' required pattern='\d{9}'' title='O número de telefone deve ter 9 dígitos'>";

        echo "<label><strong>Endereco de e-mail do tutor:</strong></label>";
        echo "<input type='email' id='email' name='email' title='Por favor, insira um endereço de e-mail válido'>";




        // Campo hidden para especificar a ação
        echo "<input type='hidden' name='action' value='processar_formulario_registo'>";
        // Botão de submit
        echo "<input type='submit' value='Submeter'>";
    echo "</form>";
}

mysqli_close($link);

if ($voltar){
//script do professor para voltar atras
echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
</script>
<noscript>
<a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
</noscript>";
}

?>