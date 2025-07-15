<?php
/*Joao*/

use PhpOffice\PhpSpreadsheet\Worksheet\Row;

require_once("custom/php/common.php");

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Verifica se a conexão foi bem-sucedida
if (!$link) {
    die("Conexão falhou: " . mysqli_connect_error());
}
$voltar = true;
echo '<link rel="stylesheet" href="sgbd\custom\css\ag.css">';
if ($_SERVER["REQUEST_METHOD"] == "POST") {//verificar se um formulario foi finalizado
    // echo "<pre>";
    // print_r($_POST);
    // print_r($_GET);
    // echo"</pre>";
    if ($_POST['action'] == 'EDITAR_DADOS') {//verificar se o formulario era para editar os dados
        $states_validos = ['active','inactive'];
        if (in_array($_POST['state'], $states_validos)) {// verificacao
            $id = intval($_POST['id']); // Valor do campo hidden (ID do item)
            $nome = mysqli_real_escape_string($link, $_POST['nome']); // Valor inserido pelo usuário
            $state = mysqli_real_escape_string($link, $_POST['state']); // Opção escolhida pelo usuário
            $item_type = mysqli_real_escape_string($link, $_POST['item_type']); // Captura o tipo de item
            //////////////////////////////////////////////////////////////////////////////////
            //verificacao do nome se e null
            if (empty($_POST['nome'])){
                //script do professor para voltar atras
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
                die("Erro: O campo 'nome' nao pode ser null.");
            }
            $querry = "SELECT name 
                        FROM item_type";
            $result = mysqli_query($link,$querry);
            if (!$result){
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro ao buscar o ID do item_type: " . mysqli_error($link));
            }
            while ($row = mysqli_fetch_array($result)){
                if ($row['name'] == $item_type){
                    $encontrado = true;
                }
            }
            if (!$encontrado){
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die ("Porfavor insira um tipo valido");
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
            // Query para buscar o id do item_type
            $querry = "SELECT id FROM item_type 
                        WHERE name = '$item_type'";
            $result = mysqli_query($link, $querry);
    
            if (!$result) { // Verifica se a consulta falhou
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro ao buscar o ID do item_type: " . mysqli_error($link));
            }
            
            $row = mysqli_fetch_assoc($result);
            if (!$row) { // Verifica se o item_type foi encontrado
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Item type não encontrado.");
            }
    
            $tipoId = $row['id']; // ID do item_type encontrado
            //querry para ir ver os valores alterados
            $querry = "SELECT * 
                        FROM item
                        WHERE id = $id";
            $result = mysqli_query($link, $querry);
            if (!$result) { // Verifica se a consulta falhou
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro ao buscar o ID do item_type: " . mysqli_error($link));
            }
            $nome_aux = 'NULL';
            $item_type_id_aux = 'NULL';
            $state_aux = 'NULL';
            $operacao = 'edicao';
            $id_original = $id;
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['name'] != $nome){
                    $nome_aux = $row['name'];
                } 
                if ($row['item_type_id'] != $tipoId){
                    $item_type_id_aux = $row['item_type_id'];
                }
                if ($row['state'] != $state){
                    $state_aux = $row['state'];
                }
            }
            //Querry de historico
            $querry = "INSERT INTO item_h (name, item_type_id, state, operacao, id_original)
                        VALUES (" . ($nome_aux === 'NULL' ? 'NULL' : "'".$nome_aux.", ".$nome."'") . ", 
                                " . ($item_type_id_aux === 'NULL' ? 'NULL' : $item_type_id_aux) . ", 
                                " . ($state_aux === 'NULL' ? 'NULL' : "'$state_aux'") . ", 
                                '$operacao', 
                                $id_original)";

            // Executa a query
            $result = mysqli_query($link, $querry);
            if (!$result) { // Verifica se a consulta falhou
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro ao inserir valores na tabela: " . mysqli_error($link));
            }
            // Query de update
            $querry = "UPDATE item SET name = '$nome',
                                        state = '$state', 
                                        item_type_id = $tipoId 
                                    WHERE id = $id";
            if (!mysqli_query($link, $querry)) { // Verifica se a atualização falhou
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro ao atualizar a tabela item: " . mysqli_error($link));
            }

            echo "<h3>Atualização realizada com sucesso!</h3>";   
            echo "<a href='/sgbd/gestao-de-itens/'>Continuar</a><br>";     
        } else {
            // Se o valor não for válido, trate como um erro
            echo "Erro: Tipo inválido selecionado.";
            echo "<br>Porvafor volte atras e reintruduza os dados";
        }

    }
    elseif ($_POST["action"] == "DESATIVAR_DADOS") {//verificar se o formulario era para desativar/ativar dos
        //guarda o estado
        $state = mysqli_real_escape_string($link, $_POST["state"]);
        $state_aux =($state == "inactive") ? "ativacao":"desativacao";
        $state = ($state == "inactive") ? "active":"inactive";
        //guarda o id
        $id = intval($_POST['id']);
        
        //query de historico
        $querry = "INSERT INTO item_h (state,operacao,id_original)
                    VALUES ('".htmlspecialchars($_POST["state"])."','".$state_aux."',$id)";
        $result = mysqli_query($link,$querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao inserir na tabela de historico de item ". mysqli_error($link));
        }            
        //query de update
        $querry = "UPDATE item 
                    SET state = '$state' 
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao atualizar tabela item ". mysqli_error($link));
        }
        echo "<h3>Atualização realizada com sucesso!</h3>";
        echo "<a href='/sgbd/gestao-de-itens/'>Continuar</a><br>";     
        $voltar = false;
    }
    elseif ($_POST["action"] == "APAGAR"){
        //guardar o id 
        $id = intval($_POST['id']);

        //query de historico do item
        $querry = "SELECT * 
                    FROM item
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao selecionar item ". mysqli_error($link));
        }
        $row = mysqli_fetch_array($result);
        $querry = "INSERT INTO item_h (name, item_type_id,state,operacao,id_original)
                    VALUES('".$row['name']."',".$row['item_type_id'].",'".$row['state']."', 'eliminacao', $id)";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao inserir na tabela de historico de item ". mysqli_error($link));
        }
        try {
            // Iniciar a transação
            mysqli_begin_transaction($link);
        
            // Query para deletar da tabela `value`
            $querry = "DELETE FROM value 
                        WHERE value.subitem_id IN (
                            SELECT id FROM subitem WHERE subitem.item_id = $id
                        )";
            $result = mysqli_query($link, $querry);
            if (!$result) {
                throw new Exception("Erro ao atualizar tabela value: " . mysqli_error($link));
            }
        
            // Query para deletar da tabela `subitem`
            $querry = "DELETE FROM subitem WHERE subitem.item_id = $id";
            $result = mysqli_query($link, $querry);
            if (!$result) {
                throw new Exception("Erro ao atualizar tabela subitem: " . mysqli_error($link));
            }
        
            // Query para deletar da tabela `item`
            $querry = "DELETE FROM item WHERE id = $id";
            $result = mysqli_query($link, $querry);
            if (!$result) {
                throw new Exception("Erro ao atualizar tabela item: " . mysqli_error($link));
            }
        
            // Se todas as operações forem bem-sucedidas, confirmar a transação
            mysqli_commit($link);
        
            echo "Todas as operações foram realizadas com sucesso.";
        
        } catch (Exception $e) {
            // Em caso de erro, reverter a transação
            mysqli_rollback($link);
        
            // Exibir mensagem de erro e link para voltar
            echo "<br><script type='text/javascript'>
                    document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='" . $_SERVER['HTTP_REFERER'] . "' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro durante a execução: " . $e->getMessage());
        }

        echo "<h3>Eliminacoes realizadas com sucesso.</h3>";
        echo "<a href='/sgbd/gestao-de-itens/'>Continuar</a><br>"; 
        $voltar = false;
    }
    elseif ($_POST["action"] == "APAGAR_REGISTO") {// falta fazer o historico
        //echo "<pre>";
        //print_r($_POST);
        //print_r($_GET);
        //echo"</pre>";
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $id = intval($_GET['id']);
        if ($id <= 0) {
            die("Erro: ID inválido. Deve ser um número inteiro positivo.");
        }
        $data_avaliacao = mysqli_real_escape_string($link, $_POST["data_avaliacao"]);
        list($ano, $mes, $dia) = explode('-', $data_avaliacao);

        // Verificar se o mês está entre 1 e 12
        if ($mes < 1 || $mes > 12) {
            die("Erro: O mês deve estar entre 1 e 12.");
        }

        // Verificar se o dia está entre 1 e 31
        if ($dia < 1 || $dia > 31) {
            die("Erro: O dia deve estar entre 1 e 31.");
        }
        $nome_item = mysqli_real_escape_string($link,$_POST["nome_item"]);
        if (empty($nome_item)) {
            die("Erro: O nome do item não pode ser vazio.");
        }
        
        $time = mysqli_real_escape_string($link, $_POST["time"]);
        if (empty($time)) {
            die("Erro: O tempo não pode ser vazio.");
        }

        $time_auxiliar = explode(":", $time);

        $hour = intval($time_auxiliar[0]);
        $minute = intval($time_auxiliar[1]);
        $second = intval($time_auxiliar[2]);

        // Validação das partes do tempo
        if ($hour < 0 || $hour > 23) {
            die("Erro: A hora deve estar no intervalo de 00 a 23.");
        }

        if ($minute < 0 || $minute > 59) {
            die("Erro: O minuto deve estar no intervalo de 00 a 59.");
        }

        if ($second < 0 || $second > 59) {
            die("Erro: O segundo deve estar no intervalo de 00 a 59.");
        }
        
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //inserir na tabela de historico do value
        $querry = "SELECT id AS id,
                            child_id AS child_id,
                            subitem_id AS subitem_id,
                            value AS value,
                            date AS date,
                            time AS time,
                            producer AS producer
                        FROM value
                        WHERE id = ".$_POST['value_id'];
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao consultar a tabela value: ". mysqli_error($link));
        }
        
        while ($row = mysqli_fetch_array($result)){
            $querry_aux = "INSERT INTO value_h (child_id, subitem_id, value, date, time, producer, operacao, id_original)
                            VALUES (".$row['child_id'].",
                                    ".$row['subitem_id'].",
                                    '".$row['value']."',
                                    '".$row['date']."',
                                    '".$row['time']."',
                                    '".(isset($row['producer'])?'NULL': $row['producer'])."',
                                    'eliminacao',
                                    ".$row['id'].")";
            $result_aux = mysqli_query($link, $querry_aux);
            if (!$result){
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro ao inserir valores na tabela value_: ". mysqli_error($link));
            }
        }


        $querry ="DELETE FROM value
                    WHERE value.child_id = $id 
                    AND value.date = '$data_avaliacao' 
                    AND value.time = '$time' 
                    AND value.subitem_id IN (
                        SELECT subitem.id 
                        FROM subitem, item
                        WHERE item.id = subitem.item_id 
                            AND item.name = '$nome_item')";//delete o value da crianca,verificando o seu subitem e o seu item para garantir que so apaga um valor

        $result = mysqli_query($link, $querry);
        if (!$result) {
            throw new Exception("Erro ao atualizar tabela item ". mysqli_error($link));
        }
        
        echo "<h3>Eliminacoes realizadas com sucesso.</h3>";
        echo "<a href='/sgbd/gestao-de-registo/'>Continuar</a><br>"; 
        $voltar = false;

    }
    elseif ($_POST["action"] == 'EDITAR_REGISTOS') {
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //verificacao para ver se todos os campos estao preenchidos
        if (empty($_POST['id']) || empty($_POST['nome']) || empty($_POST['data_nascimento']) || empty($_POST['enc_educacao']) || empty($_POST['telefone_enc'])) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: Todos os campos obrigatórios devem ser preenchidos.");
        }
        $id = intval($_POST['id']);
        $nome = mysqli_real_escape_string($link,$_POST['nome']);
        $data_nascimento  = mysqli_real_escape_string($link,$_POST['data_nascimento']);
        $enc_educacao  = mysqli_real_escape_string($link,$_POST['enc_educacao']);
        $telefone_enc = intval($_POST['telefone_enc']);
        $email  = mysqli_real_escape_string($link,$_POST['email']);


        // Dividir a data em partes para validação do mês e dia
        list($ano, $mes, $dia) = explode('-', $data_nascimento);

        // Verificar se o mês está entre 1 e 12
        if ($mes < 1 || $mes > 12) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: O mês deve estar entre 1 e 12.");
        }

        // Verificar se o dia está entre 1 e 31
        if ($dia < 1 || $dia > 31) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: O dia deve estar entre 1 e 31.");
        }

        // Verificação de 'telefone_enc' (deve ser um número com 9 dígitos)
        if (!preg_match("/^\d{9}$/", $_POST['telefone_enc'])) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: O número de telefone deve ter exatamente 9 dígitos.");
        }
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //Criar a query de historico, primeiro fazendo a verificacao de quais campos mudaram
        //Query de verificacao
        $querry = "SELECT * 
                    FROM child
                    WHERE id = $id";
        $result = mysqli_query($link,$querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: Falha ao conectar ao banco de dados.");
        }
        $nome_aux = 'NULL';
        $birth_date_aux = 'NULL';
        $tutor_name_aux = 'NULL';
        $tutor_phone_aux = 'NULL';
        $tutor_email_aux = 'NULL';
        $operacao = 'edicao';
        $id_original = $id;
        while ($row = mysqli_fetch_array($result)){
            if ($nome == $row['name']){
                $nome_aux = $nome;
            }
            if ($birth_date == $row['birth_date']){
                $birth_date_aux = $birth_date;
            }
            if ($tutor_name == $row['tutor_name']){
                $tutor_name_aux = $tutor_name;
            }
            if ($tutor_phone_aux == $row['tutor_phone']){
                $tutor_phone_aux = $tutor_phone;
            }
            if ($tutor_email_aux == $row['tutor_email']){

            }
        }
        
        //Criacao da query de historico
        $querry = "INSERT INTO child_h (name, birth_date, tutor_name, tutor_phone, tutor_email, operacao, id_original)
                    VALUES (". ($nome_aux === 'NULL' ? 'NULL' : "'".$nome_aux.", ".$nome ."'").",
                            ". ($birth_date_aux === 'NULL' ? 'NULL' : "'".$birth_date_aux.", ".$data_nascimento ."'").",
                            ". ($tutor_name_aux === 'NULL' ? 'NULL' : "'".$tutor_name_aux.", ".$enc_educacao ."'").",
                            ". ($tutor_phone_aux === 'NULL' ? 'NULL' : "'".$tutor_phone_aux.", ".$telefone_enc ."'").",
                            ". ($tutor_email_aux === 'NULL' ? 'NULL' : "'".$tutor_email_aux.", ".$email ."'").",
                            '$operacao',
                            $id_original)";
        $result = mysqli_query($link, $querry);
        if (!$result) { // Verifica se a consulta falhou
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao inserir valores na tabela: " . mysqli_error($link));
        }
        // Criar a query de UPDATE
        $querry = "UPDATE child SET 
                name = '$nome', 
                birth_date = '$data_nascimento', 
                tutor_name = '$enc_educacao', 
                tutor_phone = $telefone_enc, 
                tutor_email = " . ($email ? "'$email'" : "NULL") . " 
                WHERE id = $id";

        // Executar a query
        if (mysqli_query($link, $querry)) {
            echo "<h3>Atualizacores realizadas com sucesso</h3>";
            echo "<a href='/sgbd/gestao-de-registos/'>Continuar</a>"; 
        } else {
            echo "Erro ao atualizar os registros: " . mysqli_error($link);
        }
    }
    elseif ($_POST["action"] == 'EDITAR_REGISTO') {
        // echo "<pre>";//testar o que foi recebido
        // print_r($_POST);
        // echo "</pre>";
        $contagem_dados = intval($_POST['contagem_dados']);
        $time = mysqli_real_escape_string($link, $_POST['time']);
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if (empty($time)) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: O tempo não pode ser vazio.");
        }

        $time_auxiliar = explode(":", $time);

        $hour = intval($time_auxiliar[0]);
        $minute = intval($time_auxiliar[1]);
        $second = intval($time_auxiliar[2]);

        // Validação das partes do tempo
        if ($hour < 0 || $hour > 23) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: A hora deve estar no intervalo de 00 a 23.");
        }

        if ($minute < 0 || $minute > 59) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: O minuto deve estar no intervalo de 00 a 59.");
        }

        if ($second < 0 || $second > 59) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro: O segundo deve estar no intervalo de 00 a 59.");
        }

        for ($i = 0; $i < $contagem_dados; $i++) {
            // Recuperar valores enviados
            $value_name = mysqli_real_escape_string($link, $_POST["value_name$i"]);
            $value_type = mysqli_real_escape_string($link, $_POST["value_type$i"]);
            $item_name = mysqli_real_escape_string($link, $_POST["item_name$i"]);
            $value_id = intval($_POST["value_id$i"]);
            $values = $_POST[$value_name] ?? [];

            //Verifica se o tipo de dados recebido para alterar o value e valido, para cada um dos valores
            $querry = "SELECT id, form_field_type FROM subitem WHERE form_field_name = '$value_name'";
            $result = mysqli_query($link, $querry);

            if (!$result || mysqli_num_rows($result) == 0) {
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro: O campo '$value_name' não é válido.");
            }

            $subitem = mysqli_fetch_assoc($result);
            $subitem_id = $subitem['id'];
            $expected_type = $subitem['form_field_type'];

            // Verificar se o tipo do campo é compatível
            if ($value_type !== $expected_type) {
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro: O tipo do campo '$value_name' não corresponde ao esperado ('$expected_type').");
            }
            // Validar valores permitidos para `radio`, `checkbox` ou `selectbox`
            if (in_array($value_type, ['radio', 'checkbox', 'selectbox'])) {
                // Buscar valores permitidos
                $querry_allowed = "SELECT value FROM subitem_allowed_value WHERE subitem_id = $subitem_id";
                $result_allowed = mysqli_query($link, $querry_allowed);
    
                if (!$result_allowed) {
                    echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
                    die("Erro ao buscar valores permitidos para '$value_name': " . mysqli_error($link));
                }
    
                $allowed_values = [];
                while ($row = mysqli_fetch_assoc($result_allowed)) {
                    $allowed_values[] = $row['value'];
                }
    
                // Validar se os valores recebidos estão na lista de valores permitidos
                $values = is_array($values) ? $values : [$values];
                foreach ($values as $value) {
                    if (!in_array($value, $allowed_values)) {
                        echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                        </script>
                        <noscript>
                        <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                        </noscript>";
                        die("Erro: O valor '$value' para o campo '$value_name' não é permitido.");
                    }
                }
            }
            // Concatenar valores para checkbox
            if ($value_type === 'checkbox') {
                $values = is_array($values) ? implode(',', $values) : $values;
            } else {
                // Para outros tipos, pegar o valor diretamente
                $values = is_array($values) ? $values[0] : $values;
            }

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //query para fazer o historico fazendo uma verificacao
            $querry_historico = "SELECT value FROM value WHERE $value_id = id";
            $result_historico = mysqli_query($link, $querry_historico);
            if (!$result_historico) {
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
                die("Erro ao consultar na tabela value ". mysqli_error($link));
            }
            $row_aux = mysqli_fetch_assoc($result_historico);
            if ($row_aux['value'] != mysqli_real_escape_string($link, $values)){
                $querry_aux = "INSERT INTO value_h (`value`, operacao, id_original)
                                VALUES ('".$row_aux['value'].", ".mysqli_real_escape_string($link, $values)."', 
                                        'edicao',
                                        $value_id)";
                $result_aux = mysqli_query($link, $querry_aux);
                if (!$result_aux) {
                    echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
                    die("Erro ao inserir na tabela value_h ". mysqli_error($link));
                }
            }
            //Inserir os valores na tabela `value`
            $querry_update = "UPDATE value 
                                SET value = '".mysqli_real_escape_string($link, $values)."'
                                WHERE id = $value_id";
            $result = mysqli_query($link, $querry_update);
            if (!mysqli_query($link, $querry_update)) {
                die("Erro ao inserir os dados na tabela `value`: " . mysqli_error($link));
            }
        }
        echo "<h3>Atualizacores realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-registos/'>Continuar</a>"; 
    }
    elseif ($_POST["action"] == 'APAGAR_REGISTOS') {    
        $id = intval($_POST['id']);
        $querry = "SELECT * FROM child WHERE id = $id";
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao selecionar child ". mysqli_error($link));
        }
        $row = mysqli_fetch_assoc($result);
        //criacao da query de historico
        $querry = "INSERT INTO child_h (name, birth_date, tutor_name, tutor_phone, tutor_email, operacao, id_original)
                    VALUES('".$row['name']."',".$row['birth_date'].",'".$row['tutor_name'].",'"
                    .$row['tutor_phone']."',".($row['tutor_email'] === 'NULL'? 'NULL': $row['tutor_email']).
                    ",'eliminacao', $id)";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao inserir na tabela de historico de child ". mysqli_error($link));
        }
         //query de delete do chiled
        $querry = "DELETE FROM child
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>
                    document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='" . $_SERVER['HTTP_REFERER'] . "' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao atualizar tabela item ". mysqli_error($link));
        }
        echo "<h3>Eliminacoes realizadas com sucesso.</h3>";
        echo "<a href='/sgbd/gestao-de-registos/'>Continuar</a><br>"; 
        $voltar = false;

    }
    elseif ($_POST["action"] == "APAGAR_UNIDADES") {
        $id = intval($_POST['id']);
        //query de historico
        $querry = "SELECT * FROM subitem_unit_type WHERE id = $id";
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao selecionar subitem_unit_type ". mysqli_error($link));
        }
        $row = mysqli_fetch_array($result);
        $querry = "INSERT INTO subitem_unit_type_h (name, operacao,id_original)
                    VALUES ('".$row['name']."', 'eliminacao', $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao inserir na tabela de historico de subitem_unit_type ". mysqli_error($link));
        }
        //update aos valores
        try {
            // Iniciar a transação
            mysqli_begin_transaction($link);
        
            // Atualizar os subitens para definir unit_type_id como NULL
            $querry = "UPDATE subitem
                        SET subitem.unit_type_id = NULL
                        WHERE subitem.unit_type_id = $id";
            $result = mysqli_query($link, $querry);
            if (!$result) {
                throw new Exception("Erro ao atualizar unit_type_id para NULL: " . mysqli_error($link));
            }
        
            // Deletar o tipo de unidade
            $querry = "DELETE FROM subitem_unit_type WHERE id = $id";
            $result = mysqli_query($link, $querry);
            if (!$result) {
                throw new Exception("Erro ao apagar o tipo de unidade: " . mysqli_error($link));
            }
        
            // Confirmar a transação se tudo correr bem
            mysqli_commit($link);
        
            echo "Operações realizadas com sucesso.";
        
        } catch (Exception $e) {
            // Reverter a transação em caso de erro
            mysqli_rollback($link);
        
            echo "<br><script type='text/javascript'>
                    document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='" . $_SERVER['HTTP_REFERER'] . "' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro durante a execução: " . $e->getMessage());
        }
        


        echo "<h3>Eliminacoes realizadas com sucesso.</h3>";
        echo "<a href='/sgbd/gestao-de-unidades/'>Continuar</a><br>"; 
        $voltar = false;

    }
    elseif ($_POST["action"] == "EDITAR_UNIDADES") {
        /////////////////////////////////////
        if (!isset($_POST["nome"])) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
            die ("O campo nome nao pode ser null");
        }
        ///////////////////////////////////
        $id = intval($_POST["id"]);
        $nome = mysqli_real_escape_string($link, $_POST["nome"]);
        //criacao da query de historico e neste caso nao e perciso vazer verificacao
        //pois se houve uma edicao deste dado a unica mudanca e o nome
        $querry = "SELECT * FROM subitem_unit_type WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
            die ("Erro consultar a tabela subitem_unit_type: ".mysqli_error($link));
        }
        $row = mysqli_fetch_array($result);

        $querry = "INSERT INTO subitem_unit_type_h (name, operacao, id_original)
                    VALUES('". $row['name'] . ", ".$nome."', 'edicao',$id)";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                </script>
                <noscript>
                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                </noscript>";
            die ("Erro ao inserir na tabela de historico de subitem_unit_type: ".mysqli_error($link));
        }
        $querry = "UPDATE subitem_unit_type
                    SET name = '$nome'
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            die("Erro ao atualizar o nome: ". mysqli_error($link));
        }
        echo "<h3>Atualizacores realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-unidades/'>Continuar</a><br>"; 
        
    }
    elseif ($_POST["action"] == "EDITAR_SUBITENS") {
        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        
        $id = intval($_GET["id"]);

        //validacao server-side////////////////////////////////////////////////////////////
        //validar nome
        if (!isset($_POST["nome"])) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die ("O campo nome nao pode ser null");
        }
        //validar o item
        $querry = "SELECT id 
                    FROM item";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            die("Erro ao selecionar o id do item: ". mysqli_error($link));
        }
        $encontrado = false;
        while ($row = mysqli_fetch_array($result)){
            if (intval($_POST["item_id"]) == $row["id"]){
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("O item selecionado nao existe");
        }
        //validar o unit time
        if (!isset($_POST["unit_type_id"])){
            $querry = "SELECT id 
                        FROM subitem_unit_type";
            $result = mysqli_query($link, $querry);
            if (!$result) {
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
                die("Erro ao selecionar o id do subitem: ". mysqli_error($link));
            }
            while ($row = mysqli_fetch_array($result)){
                if (intval($_POST["unit_type_id"]) == $row["id"]){
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado){
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
                die("O tipo de unidade selecionado nao existe");    
            }
        }     
        //validar o unit time
        if (!isset($_POST["form_field_order"]) || $_POST["form_field_order"]<1){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die ("O campo form_field_order nao pode ser null");
        }
        //validar o mandatory
        if (!isset($_POST["mandatory"])){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die ("O campo mandatory nao pode ser null");
        }
        if (!in_array($_POST["mandatory"],[0,1])){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die ("O campo mandatory tem que ser 0 ou 1");
        }
        ////////////////////////////////////////////////////////////////////////////////////////
        $nome = mysqli_real_escape_string($link,$_POST["nome"]);
        $item_id = intval($_POST["item_id"]);
        $unit_type_id = isset($_POST["unit_type_id"]) ? intval($_POST["unit_type_id"]) : null; 
        $form_field_order = intval($_POST["form_field_order"]);
        $mandatory = intval($_POST["mandatory"]);

        //query de historico com a validacao para ver qual parametro mudou
        //verificacao do que mudou
        $querry = "SELECT * FROM subitem WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die ("Erro ao consultar tabela de subitens: ". mysqli_error($link));
        }
        $nome_aux = "NULL";
        $item_id_aux = "NULL";
        $unit_type_id_aux = "NULL"; 
        $form_field_order_aux = "NULL";
        $mandatory_aux = "NULL";
        $operacao = "edicao";
        $id_original = $id;

        //associacao dos valores que foram mudados
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['name'] != $nome){
                $nome_aux = $row['name'];
            } 
            if ($row['item_id'] != $item_id){
                $item_id_aux = $row['item_id'];
            }
            if ($row['unit_type_id'] != $unit_type_id){
                $unit_type_id_aux = $row['unit_type_id'];
            }
            if ($row['form_field_order'] != $form_field_order){
                $form_field_order_aux = $row['form_field_order'];
            } 
            if ($row['mandatory'] != $mandatory){
                $mandatory_aux = $row['mandatory'];
            }
        }

        //querry de historico
        $querry = "INSERT INTO subitem_h (name, item_id, unit_type_id, form_field_order, mandatory, operacao, id_original)
                    VALUES (" . ($nome_aux === 'NULL' ? 'NULL' : "'".$nome_aux.", ".$nome."'") . ", 
                                " . ($item_id_aux === 'NULL' ? 'NULL' : $item_id_aux) . ", 
                                " . ($unit_type_id_aux === 'NULL' ? 'NULL' : "$unit_type_id_aux") . ",
                                " . ($form_field_order_aux === 'NULL' ? 'NULL' : "'".$form_field_order_aux.", ".$form_field_order."'") . ", 
                                " . ($mandatory_aux === 'NULL' ? 'NULL' : $mandatory_aux) . ", 
                                '$operacao', 
                                $id_original)";
        $result = mysqli_query($link,$querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao atualizar o subitem_h: ". mysqli_error($link));
        }
        //query de update do subitem
        $querry = "UPDATE subitem
                    SET name = '$nome',
                        item_id = $item_id,
                        unit_type_id = $unit_type_id,
                        form_field_order = $form_field_order,
                        mandatory = $mandatory
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao atualizar o subitem: ". mysqli_error($link));
        }
        echo "<h3>Atualizacores realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-subitens/'>Continuar</a>"; 
    }
    elseif ($_POST["action"] == "DESATIVAR_SUBITENS") {
        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        $id = intval($_POST['id']);
        $state = mysqli_real_escape_string($link, $_POST["state"]);
        $state_aux =($state == "inactive") ? "ativacao":"desativacao";//tem que fazer isto antes do $state porqeu eu quero o valor recebido
        $state = ("active" == mysqli_real_escape_string($link,$_POST['state'])) ? 'inactive' : 'active';
        //query de insercao do historico
        $querry = "INSERT INTO subitem_h (state, operacao, id_original)
                    VALUES ('".htmlspecialchars($_POST["state"])."','".$state_aux."', $id)";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao inserir na tabela subitem_h: ". mysqli_error($link));
        }
        $querry = "UPDATE subitem
                    SET state = '$state'
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao desativar o subitem: ". mysqli_error($link));
        }
        echo "<h3>Atualizacores realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-subitens/'>Continuar</a>"; 
        $voltar = false;
    }
    elseif ($_POST["action"] == "APAGAR_SUBITENS") {
        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        $id = intval($_POST['id']);
        //query para guardar os valores do subitem
        $querry = "SELECT * FROM subitem WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao consultar a tabela do subitem: ". mysqli_error($link));
        }
        $row = mysqli_fetch_assoc($result);
        //query de historico do subitem
        $querry = "INSERT INTO subitem_h (name, item_id, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state, operacao, id_original)
                    VALUES (". $row['name'] .", 
                            ". $row['item_id'] .", 
                            ". $row['value_type'] .", 
                            ". $row['form_field_name'] .", 
                            ". $row['form_field_type'] .", 
                            ". $row['unit_type_id'] .", 
                            ". $row['form_field_order'] .", 
                            ". $row['mandatory'] .", 
                            ". $row['state'] .", 
                            'eliminacao', 
                            $id)";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao inserir na tabela de historico de subitem_h ". mysqli_error($link));
        }
        $querry = "DELETE FROM subitem 
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao apagar o subitem: ". mysqli_error($link));
        }
        echo "<h3>Eliminacoes realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-subitens/'>Continuar</a>";
        $voltar = false;
    }
    elseif ($_POST["action"] == "EDITAR_VALORES_PERMITIDOS"){
        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        $id = intval($_GET['id']);
        //validacao server-side//////////////////////////////////////////////////////////////////
        //validar o id do subitem
        $querry = "SELECT id 
                    FROM subitem";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao verificar o id do subitem: ". mysqli_error($link));
        }
        if (isset($_POST['subitem_id'])){
            $encontrado = false;
            while ($row = mysqli_fetch_array($result)){
                if ($row['id'] == intval($_POST['subitem_id'])){
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado){
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
                die("Erro ao editar valores permitidos: id do subitem nao encontrado");
            }
        }
        else {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die ("Erro, o subitem_id nao pode ser NULL");
        }
        //validar o value
        if (!isset($_POST["value"])){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao editar valores permitidos: value nao pode ser NULL");
        }
        /////////////////////////////////////////////////////////////////////////////////////////
        $value = mysqli_real_escape_string($link,$_POST["value"]);
        $subitem_id = intval($_POST["subitem_id"]);
        //query para o historico com a verificacao para ver o que mudou
        $querry = "SELECT * FROM subitem_allowed_value WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao consultar a tabela subitem_allowed_value: ". mysqli_error($link));
        }
        $value_aux = 'NULL';
        $subitem_id_aux = 'NULL';
        while ($row = mysqli_fetch_array($result)){
            if ($row['value'] != $value){
                $value_aux = $row['value'];
            }
            if ($row['subitem_id'] != $subitem_id){
                $subitem_id_aux = $row['subitem_id'];
            } 
        }
        $querry = "INSERT INTO subitem_allowed_value_h (subitem_id,value,operacao,id_original)
                    VALUES(" . ($subitem_id_aux === 'NULL' ? 'NULL' : $subitem_id_aux) . ", 
                            " . ($value_aux === 'NULL' ? 'NULL' : "'".$value_aux.", ".$value."'") . ",
                            'edicao', 
                            $id)";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao inserir na tabela subitem_allowed_value_h: ". mysqli_error($link));
        }
        //query de update
        $querry = "UPDATE subitem_allowed_value
                SET value = '$value',
                    subitem_id = $subitem_id
                WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao editar valores permitidos: ". mysqli_error($link));
        }

        echo "<h3>Alteracoes realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-valores-permitidos/'>Continuar</a>";
    }
    elseif ($_POST["action"] == "DESATIVAR_VALORES_PERMITIDOS"){
        // echo "<pre>";
        // print_r($_POST);
        // echo "</pre>";
        $id = intval($_POST['id']);
        $state = mysqli_real_escape_string($link, $_POST["state"]);
        $state_aux =($state == "inactive") ? "ativacao":"desativacao";//tem que fazer isto antes do $state porqeu eu quero o valor recebido
        $state = ("active" == mysqli_real_escape_string($link,$_POST['state'])) ? 'inactive' : 'active';
        //query de insercao no historico
        $querry = "INSERT INTO subitem_allowed_value_h (state, operacao, id_original)
                    VALUES ('".htmlspecialchars($_POST["state"])."','".$state_aux."', $id)";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao inserir na tabela subitem_allowed_value_h: ". mysqli_error($link));
        }

        $querry = "UPDATE subitem_allowed_value
                    SET state = '$state'
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao desativar o valor permitido: ". mysqli_error($link));
        }
        echo "<h3>Atualizacores realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-valores-permitidos/'>Continuar</a>"; 
        $voltar = false;
    }
    elseif ($_POST["action"] == "APAGAR_VALORES_PERMITIDOS"){
        $id = intval($_POST['id']);
        //query de insercao e busca para o historico
        $querry = "SELECT * FROM subitem_allowed_value WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao consultar a tabela subitem_allowed_value:". mysqli_error($link));
        }
        $row = mysqli_fetch_array($result);
        $querry = "INSERT INTO subitem_allowed_value_h (subitem, value, state, operacao, id_original)
                    VALUES (". $row['subitem_id'] .", 
                            ". $row['value'] .", 
                            ". $row['state'] .", 
                            'eliminacao', 
                            $id)";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao inserir na tabela de historico de subitem_allowed_value_h ". mysqli_error($link));
        }
        
        $querry = "DELETE FROM subitem_allowed_value 
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao apagar o subitem_allowed_value: ". mysqli_error($link));
        }
        echo "<h3>Eliminacoes realizadas com sucesso</h3>";
        echo "<a href='/sgbd/gestao-de-subitens/'>Continuar</a>";
        $voltar = false;
    }
}
//divisao entre a pagina de acoes e a pagina de edicoes////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif (isset($_GET['action']) && isset($_GET['id'])) {

    $action = $_GET['action'];
    $id = intval($_GET['id']); // Converte o ID para um inteiro por segurança, e ja inicializa a variavel $id para todas as suas utilizacoes
    //caso venha da pagina de itens
    if ($action == 'editar_itens'|| $action == 'desativar_itens'|| $action == 'apagar_itens') {
        //a querry tem um as `nome item_type` para conseguir acessar melhor o nome item_type do tipo, para nao ficar o item e o item tipo os 2 com o mesmo nome item_type o mesmo aplica se para o id
        $querry = "SELECT item.id AS `id item`, item_type.id, item_type.name AS `nome item_type`, item.name, item.state, item.item_type_id 
                FROM item, item_type 
                WHERE item.item_type_id = item_type.id AND item.id = ".$id;
         //Vai buscar o resultado e guarda num array "row"                
        $result = mysqli_query($link, $querry);
        $row = mysqli_fetch_assoc($result);
        if (!$result) {
            // Exibe uma mensagem de erro se a consulta falhar
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro na consulta SQL: " . mysqli_error($link));
        }
        //acao de quando se clica no editar na tabela
        if ($action === 'editar_itens') {
            if ($result && mysqli_num_rows($result) > 0) {    
                // Exibe a tabela com os dados do item
                echo "<table>";
                echo "<tr>";
                echo "<th>id</th>";
                echo "<th>name</th>";
                echo "<th>name</th>";
                echo "<th>state</th>";           
                echo "</tr>";
                // Começa o "formulário para editar o dados junto com a tabela 
                echo "<form action='' method='POST'>";
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id item']) . "</td>";
                //caixa de texto
                echo "<td><input type='text' placeholder = '".htmlspecialchars($row['name'])."' id='nome' name='nome' required pattern='[A-Za-zÀ-ÖØ-öø-ÿ\s]+' title='Apenas letras, incluindo caracteres portugueses, e espaços são permitidos.''></td>";
                
                //primeira escolha
                echo "<td>";
                echo "<select name='item_type' required>";
                echo "<option value='dado_de_crianca' " . ($row['nome item_type'] == 'dado_de_crianca' ? 'selected' : '') . ">dado_de_crianca</option>";
                echo "<option value='diagnostico' " . ($row['nome item_type'] == 'diagnostico' ? 'selected' : '') . ">diagnostico</option>";
                echo "<option value='intervencao' " . ($row['nome item_type'] == 'intervencao' ? 'selected' : '') . ">intervencao</option>";
                echo "<option value='avaliacao' " . ($row['nome item_type'] == 'avaliacao' ? 'selected' : '') . ">avaliacao</option>";
                echo "<option value='reserva' " . ($row['nome item_type'] == 'reserva' ? 'selected' : '') . ">reserva</option>";
                echo "</td>";
                
                //segunda escolha
                echo "<td>";
                echo "<select name='state' required>";
                echo "<option value='active' " . ($row['state'] == 'active' ? 'selected' : '') . ">Active</option>";
                echo "<option value='inactive' " . ($row['state'] == 'inactive' ? 'selected' : '') . ">Inactive</option>";
                echo "</select>";
                echo "</td>";
                echo "</tr>";
                echo "</table>";

                //botao de submit
                echo "<input type='hidden'  name='id' value='". htmlspecialchars($row['id item']) ."'>";//enviar o id
                echo "<input type='hidden' name='action' value='EDITAR_DADOS'>";//enviar o que e para fazer
                echo "<input type='submit' value='Submeter'><br>";
                echo "</form>";
            } else {
                echo "Item não encontrado.";
            }
        } 
        
        elseif ($action === 'desativar_itens') {
            //o texto em cima para ser atualizado consoante o state recebido
            $STATES =($row['state'] == "inactive") ? " ativar ":" desativar ";
            echo "<h3>Pretende". $STATES . "o item?</h3>";

            //inicializacao da tabela
            echo "<table>";
            echo "<tr>";
            echo "<th>id</th>";
            echo "<th>name</th>";
            echo "<th>name</th>";
            echo "<th>state</th>";           
            echo "</tr>";
            //inicializar os dados
            echo "<td><strong>" . htmlspecialchars($row['id item']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['nome item_type']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['state']) . "</strong></td>";
            echo"</table>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='state' value='". htmlspecialchars($row['state']) ."'>";//enviar o estado
            echo "<input type='hidden' name='id' value='". htmlspecialchars($row['id item']) ."'>";//enviar o id
            echo "<input type='hidden' name='action' value='DESATIVAR_DADOS'>";//enviar o que e para fazer
            echo "<input type='submit' value='Submeter'><br>";
            echo "</form>";
        } 
        
        elseif ($action === 'apagar_itens') {
            echo "<h3>Estamos prestes a apagar os dados abaixo da base de dados.
                    Confirma que pretende apagar os mesmos</h3>";
                    
            //inicializacao da tabela
            echo "<table>";
            echo "<tr>";
            echo "<th>id</th>";
            echo "<th>name</th>";
            echo "<th>name</th>";
            echo "<th>state</th>";           
            echo "</tr>";
            //inicializar os dados
            echo "<td><strong>" . htmlspecialchars($row['id item']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['nome item_type']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['state']) . "</strong></td>";
            echo"</table>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='". htmlspecialchars($row['id item']) ."'>";//enviar o id
            echo "<input type='hidden' name='action' value='APAGAR'>";//enviar o que e para fazer
            echo "<input type='submit' value='Submeter'><br>";
            echo "</form>";
        } 
        
    }
    //caso venha da pagina de registos
    elseif ($action === "editar_registos" || $action === "apagar_registos") {
        $querry = "SELECT child.name AS 'Nome', child.birth_date AS 'Data de nascimento', child.tutor_name AS 'Enc. de educacao',
        child.tutor_phone AS 'Telefone do Enc.', child.tutor_email AS 'e-mail',child.id AS 'id'
                    FROM child
                    WHERE child.id = ".$id;

        $result = mysqli_query($link, $querry);
        $row = mysqli_fetch_array($result);

        // Verifica se a query foi bem-sucedida
        if (!$result) {
            // Exibe uma mensagem de erro se a consulta falhar
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro na consulta SQL: " . mysqli_error($link));
        }
        if (mysqli_num_rows($result) > 0) {
                // Exibe a tabela com os resultados
                echo "<table border='1' cellspacing='0' cellpadding='5'>";
                echo "<tr>";
                echo "<th>id</th>";
                echo "<th>Nome</th>";
                echo "<th>Data de nascimento</th>";
                echo "<th>Enc. de educacao</th>";
                echo "<th>Telefone do Enc.</th>";
                echo "<th>e-mail</th>";
                echo "</tr>";
            
            if( $action === "editar_registos") {//basicamente por isto tudo como formulario com um butao de submit em baixo e uns quantos butoes hidden
                // Exibição dos dados como caixas de texto
                echo "<form action='' method='POST'>";
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td><input type='text' name='nome' value='" . htmlspecialchars($row['Nome']) . "'></td>";
                echo "<td><input type='text' name='data_nascimento' value='" . htmlspecialchars($row['Data de nascimento']) . "'></td>";
                echo "<td><input type='text' name='enc_educacao' value='" . htmlspecialchars($row['Enc. de educacao']) . "'></td>";
                echo "<td><input type='text' name='telefone_enc' value='" . htmlspecialchars($row['Telefone do Enc.']) . "'></td>";
                echo "<td><input type='text' name='email' value='" . (!is_null($row['e-mail']) ? htmlspecialchars($row['e-mail']) : "") . "'></td>";
                echo "</tr>";
                echo"</table>";


                // Campos hidden para editar os dados
                echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<input type='hidden' name='action' value='EDITAR_REGISTOS'>";
                // Botão de submit
                echo "<input type='submit' value='Submeter'>";

                // Fim do formulário
                echo "</form>";
            }
            elseif( $action === "apagar_registos") {//basicamente por um botao de submissao para levar ao ecran parade sucesso que apagou
                echo "<h3>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</h3>";
                echo "<tr>";
                echo "<td>". htmlspecialchars($row['id'])."</td>";
                echo "<td>" . htmlspecialchars($row['Nome']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Data de nascimento']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Enc. de educacao']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Telefone do Enc.']) . "</td>";
                echo "<td>" . (!is_null($row['e-mail']) ? htmlspecialchars($row['e-mail']) : "Sem e-mail") . "</td>";
                echo "</tr>";
                echo"</table>";
                echo "<form action='' method='POST'>";

                echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<input type='hidden' name='action' value='APAGAR_REGISTOS'>";
                // Botão de submit
                echo "<input type='submit' value='Submeter'>";

                // Fim do formulário
                echo "</form>";  
            }
        }
    }
    elseif ($action === "editar_registo" || $action === "apagar_registo") {
        $data_avaliacao = $_GET['data_avaliacao'];//defenir a data para a variavel data
        $nome_item = $_GET['nome_item'];
        $time = $_GET['time'];
        //a variavel $id nao e perciso inicializar pois ja e inicializada quando o a hyperlicagcao e feita
        //aqui usei o distinct porque estava me a dar um erro de os dados retornares 3 vezes mais do que os que haviam na tabela
        $querry  = "SELECT DISTINCT 
                            value.id AS 'valor_id',
                            child.id AS 'child.id',
                            subitem.id AS 'subitem_id',
                            value.value AS 'value',
                            subitem.form_field_name AS 'value_name',
                            subitem.form_field_type AS 'value_type',
                            value.date AS 'date',
                            value.time AS 'time',
                            value.producer AS 'producer',
                            item.name AS 'item_name'
                            FROM `value`, subitem, child,item
                            WHERE `value`.child_id = " . $id . " 
                                    AND child.id = " . $id . " 
                                    AND subitem.id = `value`.subitem_id 
                                    AND item.id = subitem.item_id  
                                    AND item.name = '" . $nome_item . "' 
                                    AND value.date = '" . $data_avaliacao . "'
                                    AND value.time = '". $time ."'";//colocar aspas aqui porque o data_avaliacao e considerado string
                    //e se nao pusermos as aspas este vai considerar um inteiro e entao nem sequer tenta verificar se e igual
            //esta a repetir os dados 2 vezes, depois ver o porque de isto estar a acontecer, depois disso e facil implemetnar o resto
            //basta saber como e que isso esta a dar erro
        $result = mysqli_query($link, $querry); 
        //testar o que foi recebido pelo get, e verificar quantas linhas o result deu
        // echo "<pre>";
        // print_r($_GET);
        // print_r($result);
        // echo"</pre>";
        // Verifica se a query foi bem-sucedida
        if (!$result) {
            // Exibe uma mensagem de erro se a consulta falhar
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro na consulta SQL: " . mysqli_error($link));
        }
        //inicializar tabela
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>child_id</th>";
        echo "<th>subitem_id</th>";
        echo "<th>value</th>";
        echo "<th>date</th>";
        echo "<th>time</th>";
        echo "<th>producer</th>";
        echo "</tr>";

        if ($action === "editar_registo"){//se for para editar o item

            $contagem_dados = 0;
            echo "<form action='' method='POST'>";
            for ($i = 0; $i < mysqli_num_rows($result); $i++){
                $row = mysqli_fetch_array($result);
                echo "<tr>";
                echo "<td>". htmlspecialchars($row['valor_id'])."</td>";
                echo "<td>". htmlspecialchars($id)."</td>";
                echo "<td>". htmlspecialchars($row['subitem_id'])."</td>";
                echo "<td>";
                // Se for 'radio' ou 'checkbox', renderiza as opções
                if ($row['value_type'] === 'radio' || $row['value_type'] === 'checkbox' || $row['value_type'] === 'selectbox' ) {
                    $querry_Auxiliar = "SELECT value 
                                        FROM subitem_allowed_value
                                        WHERE subitem_allowed_value.subitem_id IN (SELECT subitem.id 
                                                                            FROM subitem, item
                                                                            WHERE subitem.form_field_type ='".$row['value_type']."'
                                                                                AND subitem.item_id = item.id
                                                                                AND item.name = '".$row['item_name']."' )";//query para ir buscar os valores permitidos
                    $result_Axiliar = mysqli_query($link, $querry_Auxiliar);
                    if (!$result_Axiliar) {
                        echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                            </script>
                            <noscript>
                            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                            </noscript>";
                        echo "<br>";
                        echo "<h3>Erro ao gravar os dados:</h3>";
                        echo mysqli_error($link);  
                        die;
                    }

                    while($row_Auxiliar = mysqli_fetch_array($result_Axiliar)) {
                        if ($row['value_type'] == 'checkbox') {
                            $checkbox__recived_values = explode(',', $row['value']);
                            // echo" <pre>";
                            // print_r($checkbox__recived_values);
                            // echo"</pre>";
                            $checked = (in_array($row_Auxiliar['value'],$checkbox__recived_values)) ? "checked='checked'" : "";
                            //aqui eu guardo os valores recebidos num array para conseguir iterar sobre ele quando estamos a atualizar os dados
                            echo "<label>";
                            echo "<input type='checkbox' name='" . htmlspecialchars($row['value_name']). "[]' 
                                value='" . htmlspecialchars($row_Auxiliar['value']) . "' $checked>";
                            echo htmlspecialchars($row_Auxiliar['value']);
                            echo "</label><br>";
                        }
                        elseif ($row['value_type'] == 'radio') {// se for radio
                            $checked = ($row_Auxiliar['value'] == $row['value']) ? "checked='checked'" : "";
                            //dar um valor ao check
                            echo "<label>";
                            echo "<input type= 'radio' name='" . htmlspecialchars($row['value_name']) ."' value='" . htmlspecialchars($row_Auxiliar['value']) . "' $checked>";
                            echo htmlspecialchars($row_Auxiliar['value']);
                            echo "</label><br>";
                        }
                    }
                    if ($row['value_type'] == 'selectbox'){
                        $result_Axiliar = mysqli_query($link, $querry_Auxiliar);
                        if (!$result_Axiliar) {
                            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                                </script>
                                <noscript>
                                <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                                </noscript>";
                            echo "<br>";
                            echo "<h3>Erro ao gravar os dados:</h3>";
                            echo mysqli_error($link);
                            die;
                        }
                        echo "<select name='" . htmlspecialchars($row['value_name']) . "'>";
                        while($row_Auxiliar = mysqli_fetch_array($result_Axiliar)) {
                            $selected = in_array($row_Auxiliar['value'], explode(',', $row['value'])) ? "selected='selected'" : "";
                            echo "<option value='" . htmlspecialchars($row_Auxiliar['value']) . "' $selected>" . htmlspecialchars($row_Auxiliar['value']) . "</option>";
                        }
                        echo "</select>";
                    }
                }
                
                else{
                    // Se for qualquer outro tipo, renderiza o input normal
                    echo "<input type='" . htmlspecialchars($row['value_type']) . "' name='" . htmlspecialchars($row['value_name']) ."' placeholder ='".htmlspecialchars($row['value'])."'>";
                }
                echo "</td>";
                echo "<td>". htmlspecialchars($row['date'])."</td>";
                echo "<td>". htmlspecialchars($row['time'])."</td>";
                echo "<td>". (!is_null($row['producer']) ? htmlspecialchars($row['producer']) : "")."</td>";
                echo "</tr>";
                echo "<input type='hidden' name='item_name$contagem_dados' value='" . htmlspecialchars($row['item_name']) . "'>";
                echo "<input type='hidden' name='value_name$contagem_dados' value='" . htmlspecialchars($row['value_name']) . "'>";
                echo "<input type='hidden' name='value_type$contagem_dados' value='" . htmlspecialchars($row['value_type']) . "'>";
                echo "<input type='hidden' name='value_id$contagem_dados' value='" . htmlspecialchars($row['valor_id']) . "'>";
                // guardar o nome de cada valor, para ser mais facil a sua atualizacao na base de dados            
                $contagem_dados ++;                  
                echo "<input type='hidden' name='contagem_dados' value='$contagem_dados'>";// guardar a contagem dos dados para conseguir iterar sobre ela 
            }

            echo "</table>";
            echo "<input type='hidden' name='time' value='$time'>";
            echo "<input type='hidden' name='action' value='EDITAR_REGISTO'>";
            echo "<input type='submit' value='Submeter'>";
            echo "</form>";
        }
        
        if ($action === "apagar_registo"){//se for para editar o item
            echo "<h3>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</h3>";
            for ($i = 0; $i < mysqli_num_rows($result); $i++){
                $row = mysqli_fetch_array($result);
                echo "<tr>";
                echo "<td>". htmlspecialchars($row['valor_id'])."</td>";
                echo "<td>". htmlspecialchars($id)."</td>";
                echo "<td>". htmlspecialchars($row['subitem_id'])."</td>";
                echo "<td>". htmlspecialchars($row['value'])."</td>";
                echo "<td>". htmlspecialchars($row['date'])."</td>";
                echo "<td>". htmlspecialchars($row['time'])."</td>";
                echo "<td>". (!is_null($row['producer']) ? htmlspecialchars($row['producer']) : "")."</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<form action='' method='POST'";
            //guardar as variaveis necessarias
            echo "<input type='hidden' name='child_id' value='".$id."'>";
            echo "<input type='hidden' name='data_avaliacao' value=".$data_avaliacao.">";
            echo "<input type='hidden' name='nome_item' value=".$nome_item.">";
            echo "<input type='hidden' name='time' value='".$time."'>";
            echo "<input type='hidden' name='value_id' value='" . htmlspecialchars($row['valor_id']) . "'>";
            //guardar a acao que vamos fazer
            echo "<input type='hidden' name='action' value='APAGAR_REGISTO'>";
            echo "<input type='submit' value='Submeter'>";
            echo "</form>";
        }
    }
    elseif ($action === "editar_unidade" || $action === "apagar_unidade") {  
        echo "<table>";  
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>name</th>";
        echo "</tr>";
        
        $querry = "SELECT id,name
                    FROM subitem_unit_type
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao consultar tabela: ".mysqli_error($link));
        }
        if ($action == "apagar_unidade") {
            echo "<h3>Estamos prestes a apagar os dados abaixo da base de dados. Confirma que pretende apagar os mesmos?</h3>";
            $row = mysqli_fetch_array($result);
            echo "<tr>";
            echo"<td>".htmlspecialchars($row['id'])."</td>";
            echo "<td>". htmlspecialchars($row["name"])."</td>";
            echo "</tr>";
            echo "<form action= '' method='POST'>";
            echo "<input type='hidden' name='id' value=".htmlspecialchars($row['id']).">";
            echo "<input type='hidden' name='action' value='APAGAR_UNIDADES'>";
            echo "</table>";
            echo "<input type='submit' value='Submeter'>";
            echo "</form>";
        }
        else {
            $row = mysqli_fetch_array($result);
            echo "<form action= '' method='POST'>";
            echo "<tr>";
            echo"<td>".htmlspecialchars($row['id'])."</td>";
            echo "<td><input type='text' placeholder = '".htmlspecialchars($row['name'])."' id='nome' name='nome' required pattern='[A-Za-zÀ-ÖØ-öø-ÿ\s]+' title='Apenas letras, incluindo caracteres portugueses, e espaços são permitidos.''></td>";
            echo "</tr>";
            echo "<input type='hidden' name='id' value=".htmlspecialchars($row['id']).">";
            echo "<input type='hidden' name='action' value='EDITAR_UNIDADES'>";
            echo "</table>";
            echo "<input type='submit' value='Submeter'>";
            echo "</form>";
        }

    }
    elseif ($action == 'editar_subitens'|| $action == 'desativar_subitens'|| $action == 'apagar_subitens') {
        $querry = "SELECT subitem.id AS 'id', 
                            subitem.name AS 'name', 
                            subitem.item_id AS 'item_id', 
                            subitem.value_type AS 'value_type',
                            subitem.form_field_name AS 'form_field_name',
                            subitem.form_field_type AS 'form_field_type',
                            subitem.unit_type_id AS 'unit_type_id',
                            subitem.form_field_order AS 'form_field_order',
                            subitem.mandatory AS 'mandatory',
                            subitem.state AS 'state',
                            (SELECT subitem_unit_type.name 
                            FROM subitem_unit_type 
                            WHERE subitem_unit_type.id = subitem.unit_type_id) AS 'unit_type_name'
                    FROM subitem
                    WHERE subitem.id = $id";
        //Vai buscar o resultado e guarda num array "row"                
        $result = mysqli_query($link, $querry);
        if (!$result) {
        // Exibe uma mensagem de erro se a consulta falhar
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro na consulta SQL: " . mysqli_error($link));
        }
        $row = mysqli_fetch_assoc($result);
        //acao de quando se clica no editar na tabela
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>name</th>";
        echo "<th>item_id</th>";
        echo "<th>value_type</th>";
        echo "<th>form_field_name</th>";
        echo "<th>form_field_type</th>";
        echo "<th>unit_type_id</th>"; 
        echo "<th>form_field_order</th>";
        echo "<th>mandatory</th>";
        echo "<th>state</th>";                 
        echo "</tr>";
        if ($action === 'editar_subitens') {
            if ($result && mysqli_num_rows($result) > 0) {    
                echo "<form action='' method='POST'>";
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td><input type='text' placeholder = '".htmlspecialchars($row['name'])."' id='nome' name='nome' required pattern='[A-Za-zÀ-ÖØ-öø-ÿ\s]+' title='Apenas letras, incluindo caracteres portugueses, e espaços são permitidos.''></td>";
                //ids dos itens depois verificar server side
                $querry_auxiliar = "SELECT id
                                    FROM item";
                $result_auxiliar = mysqli_query($link, $querry_auxiliar);
                if (!$result_auxiliar) {
                    echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                        </script>
                        <noscript>
                        <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                        </noscript>";
                    die("Erro na consulta SQL: " . mysqli_error($link));
                }

                echo "<td><select name='item_id'>";
                while ($row_auxiliar = mysqli_fetch_assoc($result_auxiliar)) {
                    $selected = $row_auxiliar['id'] == $row['item_id'] ? "selected" : "";
                    echo "<option value='".$row_auxiliar['id']."' $selected>" . htmlspecialchars($row_auxiliar['id']) . "</option>";
                }
                echo "</select></td>";

                echo "<td>" . htmlspecialchars($row['value_type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['form_field_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['form_field_type']) . "</td>";

                //selecionar o nome e o id para inserir como opcoes
                $querry_auxiliar = "SELECT subitem_unit_type.id ,subitem_unit_type.name
                                    FROM subitem_unit_type,subitem
                                    WHERE subitem_unit_type.id = subitem.unit_type_id";
                $result_auxiliar = mysqli_query($link, $querry_auxiliar);
                if (!$result_auxiliar) {
                    echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                        </script>
                        <noscript>
                        <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                        </noscript>";
                    die("Erro na consulta SQL: " . mysqli_error($link));
                }
                $ja_selecionado = false;
                echo "<td><select name='unit_type_id'>";
                while ($row_auxiliar = mysqli_fetch_assoc($result_auxiliar)) {
                    $selected = $row_auxiliar['name'] === $row['unit_type_name'] ? "selected" : "";
                    if ($selected) {
                        $ja_selecionado = true;
                    }
                    echo "<option value='". htmlspecialchars($row_auxiliar['id'])."' $selected>" . htmlspecialchars($row_auxiliar['id']) . "</option>";
                }
                //se nao houver nenhum valor selecionado entao
                if (!$ja_selecionado && $row['unit_type_id'] === NULL) {
                    echo "<option value='nenhum' selected>Insira um valor</option>";
                }
                echo "</select></td>";

                echo "<td><input type='number' name='form_field_order' value='" . intval($row['form_field_order']) . "'></td>";

                echo "<td><select name='mandatory'>";
                echo "<option value='1'" . ($row['mandatory'] ? "selected" : "") . ">Sim</option>";
                echo "<option value='0'" . (!$row['mandatory'] ? "selected" : "") . ">Não</option>";
                echo "</select></td>";

                echo "<td>" . htmlspecialchars($row['state']) . "</td>";
                echo "</tr>";
                echo "</table>";

                echo "<input type='hidden' name='action' value='EDITAR_SUBITENS'>";
                echo "<input type='submit' value='submeter'>";
                echo "</form>";
            } else {
                echo "SubItem não encontrado.";
            }
        } 

        elseif ($action === 'desativar_subitens' ) {
            //o texto em cima para ser atualizado consoante o state recebido
            $STATES =($row['state'] == "inactive") ? " ativar ":" desativar ";
            echo "<h3>Pretende". $STATES . "o item?</h3>";
            echo "<tr>";
            //inicializar os dados
            echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['item_id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['value_type']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['form_field_name']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['form_field_type']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['unit_type_id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['form_field_order']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['mandatory']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['state']) . "</strong></td>";
            echo "</tr>";

            echo"</table>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='state' value='". htmlspecialchars($row['state']) ."'>";//enviar o estado
            echo "<input type='hidden' name='id' value='". htmlspecialchars($row['id']) ."'>";//enviar o id
            echo "<input type='hidden' name='action' value='DESATIVAR_SUBITENS'>";//enviar o que e para fazer
            echo "<input type='submit' value='Submeter'><br>";
            echo "</form>";
    } 

        elseif ($action === 'apagar_subitens') {
            echo "<h3>Estamos prestes a apagar os dados abaixo da base de dados.
                    Confirma que pretende apagar os mesmos</h3>";
                    
            //inicializacao da tabela
            echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['item_id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['value_type']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['form_field_name']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['form_field_type']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['unit_type_id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['form_field_order']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['mandatory']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['state']) . "</strong></td>";
            echo "</tr>";

            echo"</table>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='". htmlspecialchars($row['id']) ."'>";//enviar o id
            echo "<input type='hidden' name='action' value='APAGAR_SUBITENS'>";//enviar o que e para fazer
            echo "<input type='submit' value='Submeter'><br>";
            echo "</form>";
        } 

    }
    elseif ($action === 'editar_valores_permitidos' || $action === 'desativar_valores_permitidos' || $action === 'apagar_valores_permitidos'){
        // echo "<pre>";
        // print_r($_GET);
        // echo "</pre>";
        $querry = "SELECT id, 
                        subitem_id,
                        value,
                        state
                    FROM subitem_allowed_value
                    WHERE id = $id";
        $result = mysqli_query($link, $querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
            die("Erro ao executar a query: " . mysqli_error($link));
        }
        $row = mysqli_fetch_array($result);
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>subitem_id</th>";
        echo "<th>value</th>";
        echo "<th>state</th>";
        echo "</tr>";
        if ($action === 'editar_valores_permitidos'){
            echo "<form action='' method='POST'>";
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";

            echo "<td>";
            $querry_auxiliar = "SELECT subitem.id 
                                FROM subitem
                                WHERE subitem.id
                                ORDER BY subitem.id ASC";
            $result_auxiliar = mysqli_query($link, $querry_auxiliar);
            if (!$result_auxiliar) {
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                    </script>
                    <noscript>
                    <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                    </noscript>";
                die("Erro na consulta SQL: " . mysqli_error($link));
            }

            echo "<select name='subitem_id'>";
            while ($row_auxiliar = mysqli_fetch_assoc($result_auxiliar)) {
                $selected = $row_auxiliar['id'] === $row['subitem_id'] ? "selected" : "";
                if ($selected) {
                }
                echo "<option value='". htmlspecialchars($row_auxiliar['id'])."' $selected>" . htmlspecialchars($row_auxiliar['id']) . "</option>";
            }
            echo "</select>";
            echo "</td>";
            echo "<td><input type='text' placeholder = '".htmlspecialchars($row['value'])."' id='value' name='value' required pattern='[A-Za-zÀ-ÖØ-öø-ÿ\s]+' title='Apenas letras, incluindo caracteres portugueses, e espaços são permitidos.''></td>";
            
            echo "<td><strong>" . htmlspecialchars($row['state']) . "</strong></td>";
            echo "</tr>";
            echo "</table>";

            echo "<input type='hidden' name='action' value='EDITAR_VALORES_PERMITIDOS'>";
            echo "<input type='submit' value='submeter'>";
            echo "</form>";
        }
        elseif ($action === 'desativar_valores_permitidos'){
            $STATES =($row['state'] == "inactive") ? " ativar ":" desativar ";
            echo "<h3>Pretende". $STATES . "o item?</h3>";
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['subitem_id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['value']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['state']) . "</strong></td>";
            echo"</table>";
            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='state' value='". htmlspecialchars($row['state']) ."'>";//enviar o estado
            echo "<input type='hidden' name='id' value='". htmlspecialchars($row['id']) ."'>";//enviar o id
            echo "<input type='hidden' name='action' value='DESATIVAR_VALORES_PERMITIDOS'>";//enviar o que e para fazer
            echo "<input type='submit' value='Submeter'><br>";
            echo "</form>";
        }
        elseif ($action === 'apagar_valores_permitidos'){
            echo "<h3>Estamos prestes a apagar os dados abaixo da base de dados.
                    Confirma que pretende apagar os mesmos</h3>";            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['subitem_id']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['value']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['state']) . "</strong></td>";
            echo"</table>";

            echo "<form action='' method='POST'>";
            echo "<input type='hidden' name='id' value='". htmlspecialchars($row['id']) ."'>";//enviar o id
            echo "<input type='hidden' name='action' value='APAGAR_VALORES_PERMITIDOS'>";//enviar o que e para fazer
            echo "<input type='submit' value='Submeter'><br>";
            echo "</form>";
        }

    }
    elseif ($action =='historico_itens'){
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>name</th>";
        echo "<th>item_type_id</th>";
        echo "<th>state</th>";
        echo "<th>operacao</th>";
        echo "<th>selotemporal</th>";
        echo "<th>id_original</th>";
        echo "</tr>";

        $querry = "SELECT id AS id,
                        name AS name,
                        item_type_id AS item_type_id,
                        selotemporal AS selotemporal,
                        id_original AS id_original,
                        state AS state,
                        operacao AS operacao
                    FROM item_h
                    WHERE id_original = $id
                    ORDER BY selotemporal ASC";
        $result = mysqli_query($link,$querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao ir buscar os valores da tabela item_h: ".mysqli_error($link));
        }
        while ($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
            echo "<td>". ($row['name'] == NULL ? NULL: htmlspecialchars($row['name']))."</td>";
            echo "<td><strong>" . ($row['item_type_id'] === NULL ? NULL: htmlspecialchars($row['item_type_id'])) . "</strong></td>";
            echo "<td><strong>" . ($row['state'] === NULL ? NULL: htmlspecialchars($row['state'])) . "</strong></td>";
            echo "<td><strong>Operação de " . htmlspecialchars($row['operacao']) ."</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['selotemporal']) . "</strong>:</td>";
            echo "<td><strong>" . htmlspecialchars($row['id_original']) . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    elseif ($action =='historico_registos'){
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>Nome</th>";
        echo "<th>Data de nascimento</th>";
        echo "<th>Enc. de educacao</th>";
        echo "<th>Telefone do Enc.</th>";
        echo "<th>e-mail</th>";
        echo "<th>operacao</th>";
        echo "<th>selotemporal</th>";
        echo "<th>id_original</th>";
        echo "</tr>";
        
        $querry = "SELECT id AS id,
                        name AS name,
                        birth_date AS birth_date,
                        tutor_name AS tutor_name,
                        tutor_email AS tutor_email,
                        operacao AS operacao,
                        selotemporal AS selotemporal,
                        id_original AS id_original
                    FROM child_h
                    WHERE id_original = $id
                    ORDER BY selotemporal ASC";
        $result = mysqli_query($link,$querry);
        if (!$result){
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro ao ir buscar os valores da tabela child_h: ".mysqli_error($link));
        }
        while ($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
            echo "<td><strong>" . ($row['name'] == NULL ? NULL:htmlspecialchars($row['name'])) . "</strong></td>";
            echo "<td><strong>" . ($row['birth_date'] == NULL ? NULL:htmlspecialchars($row['birth_date'])) . "</strong></td>";
            echo "<td><strong>" . ($row['tutor_name'] == NULL ? NULL:htmlspecialchars($row['tutor_name'])) . "</strong></td>";
            echo "<td><strong>" . ($row['tutor_email'] == NULL ? NULL:htmlspecialchars($row['tutor_email'])) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['operacao']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['selotemporal']) . "</strong></td>";
            echo "<td><strong>" . htmlspecialchars($row['id_original']) . "</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    elseif($action == 'historico_registo'){// nem sei se e perciso fazer entao depois verificar com o professor
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>child_id</th>";
        echo "<th>subitem_id</th>";
        echo "<th>value</th>";
        echo "<th>date</th>";
        echo "<th>time</th>";
        echo "<th>producer</th>";
        echo "<th>operacao</th>";
        echo "<th>selotemporal</th>";
        echo "<th>id_original</th>";
        echo "</tr>";
        
        $data_avaliacao = $_GET['data_avaliacao'];//defenir a data para a variavel data
        $nome_item = $_GET['nome_item'];
        $time = $_GET['time'];
        //a variavel $id nao e perciso inicializar pois ja e inicializada quando o a hyperlicagcao e feita
        //aqui usei o distinct porque estava me a dar um erro de os dados retornares 3 vezes mais do que os que haviam na tabela
        $querry = "SELECT DISTINCT value.id AS id FROM value, child,subitem WHERE child.id = $id 
                                                                    AND value.child_id = child.id
                                                                    AND value.subitem_id = subitem.id
                                                                    AND value.date = '$data_avaliacao'
                                                                    AND value.time = '$time'";
        $result_ini = mysqli_query($link, $querry);
        while ($row_ini = mysqli_fetch_array($result_ini)){
            $querry  = "SELECT id, child_id, subitem_id, value, date, time, producer, operacao, selotemporal, id_original 
                        FROM value_h
                        WHERE id_original = ".$row_ini['id'];

            $result = mysqli_query($link, $querry); 
            //testar o que foi recebido pelo get, e verificar quantas linhas o result deu
            // echo "<pre>";
            // print_r($_GET);
            // print_r($result);
            // echo"</pre>";
            // Verifica se a query foi bem-sucedida
            if (!$result) {
                // Exibe uma mensagem de erro se a consulta falhar
                echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
                        </script>
                        <noscript>
                        <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
                        </noscript>";
                die("Erro na consulta SQL: " . mysqli_error($link));
            }

            while ($row = mysqli_fetch_assoc($result)){
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($row['id']) . "</strong></td>";
                echo "<td><strong>" . ($row['child_id'] == NULL ? NULL:htmlspecialchars($row['child_id'])) . "</strong></td>";
                echo "<td><strong>" . ($row['subitem_id'] == NULL ? NULL:htmlspecialchars($row['subitem_id'])) . "</strong></td>";
                echo "<td><strong>" . ($row['value'] == NULL ? NULL:htmlspecialchars($row['value'])) . "</strong></td>";
                echo "<td><strong>" . ($row['date'] == NULL ? NULL:htmlspecialchars($row['date'])) . "</strong></td>";
                echo "<td><strong>" . ($row['time'] == NULL ? NULL:htmlspecialchars($row['time'])) . "</strong></td>";
                echo "<td><strong>" . ($row['producer'] == NULL ? NULL:htmlspecialchars($row['producer'])) . "</strong></td>";
                echo "<td><strong>" . htmlspecialchars($row['operacao']) . "</strong></td>";
                echo "<td><strong>" . htmlspecialchars($row['selotemporal']) . "</strong></td>";
                echo "<td><strong>" . htmlspecialchars($row['id_original']) . "</strong></td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }
    elseif ($action == 'historico_valores_permitidos'){
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>subitem_id</th>";
        echo "<th>value</th>";
        echo "<th>state</th>";
        echo "<th>operacao</th>";
        echo "<th>selotemporal</th>";
        echo "<th>id_original</th>";
        echo "</tr>";

        $querry = 'SELECT id AS id,
                            subitem_id AS subitem_id, 
                            value AS value,
                            state AS state,
                            operacao AS operacao,
                            selotemporal AS selotemporal,
                            id_original AS id_original
                    FROM subitem_allowed_value_h
                    WHERE id_original = '.$id.'
                    ORDER BY selotemporal ASC';
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro na consulta SQL: " . mysqli_error($link));
        }
        while ($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td>". htmlspecialchars($row['id']) . "</td>";
            echo "<td>". ($row['subitem_id'] == NULL? NULL: htmlspecialchars($row['subitem_id']))."</td>";
            echo "<td>". ($row['value'] == NULL? NULL: htmlspecialchars($row['value']))."</td>";
            echo "<td>". ($row['state'] == NULL? NULL: htmlspecialchars($row['state']))."</td>";
            echo "<td>". htmlspecialchars($row['operacao'])."</td>";
            echo "<td>". htmlspecialchars($row['selotemporal'])."</td>";
            echo "<td>". htmlspecialchars($row['id_original'])."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    elseif($action == 'historico_subitens'){
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>name</th>";
        echo "<th>item_id</th>";
        echo "<th>value_type</th>";
        echo "<th>form_field_name</th>";
        echo "<th>form_field_type</th>";
        echo "<th>unit_type_id</th>"; 
        echo "<th>form_field_order</th>";
        echo "<th>mandatory</th>";
        echo "<th>state</th>";
        echo "<th>operacao</th>";
        echo "<th>selotemporal</th>";
        echo "<th>id_original</th>";
        echo "</tr>";
        $querry = 'SELECT id AS id,
                        name AS name,
                        item_id AS item_id,
                        value_type AS value_type,
                        form_field_name AS form_field_name,
                        form_field_type AS form_field_type,
                        unit_type_id AS unit_type_id,
                        form_field_order AS form_field_order,
                        mandatory AS mandatory,
                        state AS state,
                        operacao AS operacao,
                        selotemporal AS selotemporal,
                        id_original AS id_original 
                    FROM subitem_h
                    WHERE id_original = '.$id.'
                    ORDER BY selotemporal ASC';
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro na consulta SQL: " . mysqli_error($link));
        }
        while ($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td>". htmlspecialchars($row['id'])."</td>";
            echo "<td>". ($row['name'] == NULL ? NULL : htmlspecialchars($row['name']))."</td>";
            echo "<td>". ($row['item_id'] == NULL ? NULL : htmlspecialchars($row['item_id']))."</td>";
            echo "<td>". ($row['value_type'] == NULL ? NULL : htmlspecialchars($row['value_type']))."</td>";
            echo "<td>". ($row['form_field_name'] == NULL ? NULL : htmlspecialchars($row['form_field_name']))."</td>";
            echo "<td>". ($row['form_field_type'] == NULL ? NULL : htmlspecialchars($row['form_field_type']))."</td>";
            echo "<td>". ($row['unit_type_id'] == NULL ? NULL : htmlspecialchars($row['unit_type_id']))."</td>";
            echo "<td>". ($row['form_field_order'] == NULL ? NULL : htmlspecialchars($row['form_field_order']))."</td>";
            echo "<td>". ($row['mandatory'] == NULL ? NULL : htmlspecialchars($row['mandatory']))."</td>";
            echo "<td>". ($row['state'] == NULL ? NULL : htmlspecialchars($row['state']))."</td>";
            echo "<td>". htmlspecialchars($row['operacao'])."</td>";
            echo "<td>". htmlspecialchars($row['selotemporal'])."</td>";
            echo "<td>". htmlspecialchars($row['id_original'])."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    elseif($action == 'historico_unidade'){
        echo "<table>";
        echo "<tr>";
        echo "<th>id</th>";
        echo "<th>name</th>";
        echo "<th>operacao</th>";
        echo "<th>selotemporal</th>";
        echo "<th>id_original</th>";
        echo "</tr>";
        $querry = "SELECT id, name, operacao, selotemporal, id_original
                    FROM subitem_unit_type_h
                    WHERE id_original = $id
                    ORDER BY selotemporal ASC";
        $result = mysqli_query($link, $querry);
        if (!$result) {
            echo "<br><script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");
            </script>
            <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
            </noscript>";
            die("Erro na consulta SQL: " . mysqli_error($link));
        }
        while ($row = mysqli_fetch_assoc($result)){
            echo "<tr>";
            echo "<td>". htmlspecialchars($row['id'])."</td>";
            echo "<td>". ($row['name'] == NULL ? NULL : htmlspecialchars($row['name']))."</td>";
            echo "<td>". htmlspecialchars($row['operacao'])."</td>";
            echo "<td>". htmlspecialchars($row['selotemporal'])."</td>";
            echo "<td>". htmlspecialchars($row['id_original'])."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    else {
        echo "Ação inválida.";
    }
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