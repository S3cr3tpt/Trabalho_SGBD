<?php
/*Joao*/
require_once("custom/php/common.php");
// Incluindo o CSS externo
echo '<link rel="stylesheet" href="sgbd\custom\css\ag.css">';

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Inicializa a variável de mensagem de erro ou sucesso
$mensagem = "";
// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se a conexão foi bem-sucedida
    if (!$link) {
        die("Conexão falhou: " . mysqli_connect_error());
    }
    echo"<h3>Gestao de itens- insercao<br><br>";
    // mudar isto para ficar dinamico
    $tipos_validos = ['dado_de_crianca', 'diagnostico', 'intervencao', 'avaliacao', 'reserva'];
    $estados_validos = ['active', 'inactive'];

    // Sanitiza os dados do formulário
    $nome = mysqli_real_escape_string($link, $_POST['nome']);
    $tipo = mysqli_real_escape_string($link,$_POST['tipo']); 
    $estado = mysqli_real_escape_string($link, $_POST['estado']);
    //Verificacoes server SIDE//////////////////////////////////////////////////////////////////////////////////////////////////
    if (empty($nome)) {
        die("Erro: O campo 'nome' não pode ser vazio.");
    }
    // Verificação se 'tipo' é uma das opções válidas
    if (!in_array($tipo, $tipos_validos)) {
        die("Erro: O valor de 'tipo' é inválido.");
    } else {
        // Se for válido, sanitizamos
        $tipo = mysqli_real_escape_string($link, $tipo);
    }
    // Verificação se 'estado' é uma das opções válidas
    if (!in_array($estado, $estados_validos)) {
        die("Erro: O valor de 'estado' é inválido.");
    } else {
        // Se for válido, sanitizamos
        $estado = mysqli_real_escape_string($link, $estado);
    }

    $querry = "SELECT id, name FROM item_type";
    $result = mysqli_query($link, $querry);//ir buscar o id do item?type
    while ($row = mysqli_fetch_assoc($result)){
        if ($tipo == $row['name']){
            $tipo = intval($row['id']);
        }
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //Escreve a query SQL para inserir os dados
    $querry = "INSERT INTO item (name, item_type_id, state) 
            VALUES ('$nome', $tipo, '$estado')";
    $result = mysqli_query($link, $querry);

    if(!$result){
        die("Erro ao inserir dados: " . mysqli_error($link));
    }
    // Executa a query e define a mensagem de sucesso ou erro
    if ($result) {
        $mensagem = "Inserio os dados de novo item com sucesso";
    } else {
        $mensagem = "Erro ao inserir dados, tente novamente.";
    }
    // Exibe a mensagem, se existir
    if ($mensagem) {
        echo "<div class='mensagem'>$mensagem</div>";
    }
    // Botão de continuar
    echo "<form action='' method='VOLTAR'>";
    echo "<input type='submit' value='Continuar'>";
    echo "</form>";
    
} 
else{


// Exibição da tabela de itens
$querry = "SELECT item_type.name AS `tipo de item`,
                  item.id AS id, 
                  item.name AS `nome do item`,
                  item.state AS estado
            FROM 
                item, item_type
            WHERE item.item_type_id = item_type.id
            ORDER BY 
                item_type.name ASC,
                item.id ASC";

 //Ligacao com a base de dados e fazer a query ao mesmo tempo
$result = mysqli_query($link, $querry);

if (!$result) {
    die("Erro de consulta : " . mysqli_error($link));
}
//inicializacao das tabelas
echo "<table>";
echo "<tr>";
echo "<th>tipo de item</th>";
echo "<th>id</th>";
echo "<th>nome do item</th>";
echo "<th>estado</th>";
echo "<th>acao</th>";
echo "</tr>";

$previousType = ""; // Variável para armazenar o tipo de item anterior
$rowspanCount = 0; // Contador de linhas para rowspan
$rows = [];

// Coletar todas as linhas para calcular o rowspan
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

// Itera sobre as linhas coletadas e exibe a tabela com rowspan
foreach ($rows as $index => $row) {
    if ($row['tipo de item'] !== $previousType) {
        $rowspanCount = 1;
        for ($i = $index + 1; $i < count($rows); $i++) {
            if ($rows[$i]['tipo de item'] === $row['tipo de item']) {
                $rowspanCount++;
            } else {
                break;
            }
        }

        echo "<tr>";
        echo "<td rowspan='$rowspanCount'>" . str_replace('_',' ',htmlspecialchars($row['tipo de item'])) . "</td>";
    } else {
        echo "<tr>";
    }

    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['nome do item']) . "</td>";
    echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
    $STATES =($row['estado'] == "inactive") ? " Ativar ":" Desativar ";
    echo "<td>"; // aqui o action, chama uma acao na pagina que vai abrir, acao de editar,etc e depois passa o id do que foi carregado
    echo "<a href='/sgbd/edicao-de-dados/?action=editar_itens&id=" . $row['id'] . "'>[Editar]</a> ";
    echo "<a href='/sgbd/edicao-de-dados/?action=desativar_itens&id=" . $row['id'] . "'>[" .$STATES. "]</a> ";
    echo "<a href='/sgbd/edicao-de-dados/?action=apagar_itens&id=" . $row['id'] . "'>[Apagar]</a>";
    echo "<a href='/sgbd/edicao-de-dados/?action=historico_itens&id=" . $row['id'] . "'>[Historico]</a>";
    echo "</td>";
    echo "</tr>";

    $previousType = $row['tipo de item'];
}

//Caso ainda nao haja a reserva cria mais uma linha para emplementala isto e hardcoded mas nao vejo outra maneira do o fazer
if ($previousType != "reserva") {
    echo "<tr>";
    echo "<td>reserva</td>";
    echo "<td colspan='4'>Não existem itens para este tipo de item</td>";
    echo "</tr>";
}
echo "</table>";
mysqli_close($link);

// Exibe o formulário
echo "<h3>Gestão de itens - introdução</h3>";
echo "<p>* Obrigatório</p>";

echo "<form action='' method='POST'>";
echo "<label><strong>Nome:</strong> <span style='color:red;'>*</span></label>";
echo "<input type='text' id='nome' name='nome' required pattern='[A-Za-zÀ-ÖØ-öø-ÿ\s]+' title='Apenas letras, incluindo caracteres portugueses, e espaços são permitidos.'>";


echo "<label><strong>Tipo:</strong> <span style='color:red;'>*</span></label>";
echo "<input type='radio' name='tipo' value='dado_de_crianca' required> dado de criança";
echo "<input type='radio' name='tipo' value='diagnostico'> diagnóstico";
echo "<input type='radio' name='tipo' value='intervencao'> intervenção";
echo "<input type='radio' name='tipo' value='avaliacao'> avaliação";
echo "<input type='radio' name='tipo' value='reserva'> reserva"; 

// Segunda escolha - Estado
echo "<label><strong> Estado:</strong> <span style='color:red;'>*</span></label>";
echo "<input type='radio' name='estado' value='active' required> active";
echo "<input type='radio' name='estado' value='inactive'> inactive";

// Campo hidden para especificar a ação
echo "<input type='hidden' name='action' value='processar_formulario_item'>";

// Botão de submit
echo "<input type='submit' value='Submeter'>";
echo "</form>";


// Script para voltar atrás
echo "<br>";
echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atrás'>Voltar atrás</a>\");
</script>
<noscript>
<a href='" . $_SERVER['HTTP_REFERER'] . "' class='backLink' title='Voltar atrás'>Voltar atrás</a>
</noscript>";
}
?>
