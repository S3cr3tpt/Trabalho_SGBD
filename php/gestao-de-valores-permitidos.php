<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Valores Permitidos</title>
    <link rel="stylesheet" type="text/css" href="/sgbd/custom/css/ag.css">
</head>
<body>

<?php
require_once("custom/php/common.php");

// Obtém o URL dinâmico da página atual
$current_page = get_site_url() . '/' . basename(get_permalink());

// Conecta ao banco de dados
$link = db_connect();

// Verifica a permissão do usuário
add_manage_capability("Manage allowed values");
if (!is_user_logged_in() || !current_user_can("Manage allowed values")) {
    // Exibe mensagem de erro e interrompe a execução se o usuário não tiver permissão
    echo "<p>Não tem autorização para aceder a esta página</p>";
    exit;
}

if (empty($_REQUEST['estado'])) {
    // Código para listar valores permitidos
    $query = "
    SELECT 
        item.name AS item_name,
        subitem.id AS subitem_id,
        subitem.name AS subitem_name,
        subitem_allowed_value.value AS allowed_value,
        subitem_allowed_value.id AS allowed_value_id,
        subitem_allowed_value.state AS allowed_value_state,
        subitem.value_type AS subitem_value_type
    FROM item
    LEFT JOIN subitem
        ON subitem.item_id = item.id
    LEFT JOIN subitem_allowed_value
        ON subitem_allowed_value.subitem_id = subitem.id
    WHERE subitem.value_type = 'enum'
    ORDER BY item.name, subitem.id";

    // Executa a consulta SQL
    $result = mysqli_query($link, $query);
    if (!$result) {
        die("Erro na query: " . mysqli_error($link));
    }

    // Conta os rowspans necessários para itens e subitens
    $item_rowspan_counts = contar_rowspan($result, 'item_name');
    $subitem_rowspan_counts = contar_rowspan($result, 'subitem_id');

    if ($result->num_rows > 0) {
        // Início da tabela de valores permitidos
        echo '<table cellspacing="2" cellpadding="2" border="1" width="100%">
        <thead>
            <tr>
                <th>Item</th>
                <th>ID Subitem</th>
                <th>Nome Subitem</th>
                <th>ID Valor Permitido</th>
                <th>Valor Permitido</th>
                <th>Estado</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>';

        $current_item = null; // Variável para controlar o item atual
        $current_subitem = null; // Variável para controlar o subitem atual

        mysqli_data_seek($result, 0); // Reinicia o ponteiro do resultado
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";

            // Imprime o item somente se for um novo
            if ($current_item !== $row['item_name']) {
                $current_item = $row['item_name'];
                $rowspan = $item_rowspan_counts[$current_item] ?? 1;
                echo "<td rowspan='{$rowspan}'>{$current_item}</td>";
            }

            // Imprime o subitem somente se for um novo
            if ($current_subitem !== $row['subitem_id']) {
                $current_subitem = $row['subitem_id'];
                $rowspan = $subitem_rowspan_counts[$current_subitem] ?? 1;
                echo "<td rowspan='{$rowspan}'>{$current_subitem}</td>";
                echo "<td rowspan='{$rowspan}'><a href='{$current_page}?estado=introducao&subitem_id={$row['subitem_id']}' style='color: blue;'>[{$row['subitem_name']}]</a></td>";

                // Exibe mensagem se não houver valores permitidos
                if (empty($row['allowed_value_id'])) {
                    echo "<td colspan='3' style='text-align: center;'>Não há valores permitidos definidos</td>";
                    echo "</tr>";
                    continue;
                }
            }

            // Exibe detalhes do valor permitido
            echo "<td>{$row['allowed_value_id']}</td>";
            echo "<td>{$row['allowed_value']}</td>";
            echo "<td>{$row['allowed_value_state']}</td>";

            // Links de ação para editar, desativar, apagar ou visualizar histórico
            $state_action = ($row['allowed_value_state'] === 'inactive') ? "Ativar" : "Desativar";
            echo "<td>";
            $STATES = ($row['allowed_value_state'] == "inactive") ? " Ativar ":" Desativar ";
            echo "<td>"; 
            echo "<a href='/sgbd/edicao-de-dados/?action=editar_valores_permitidos&id=" . $row['allowed_value_id'] . "'>[Editar]</a> ";
            echo "<a href='/sgbd/edicao-de-dados/?action=desativar_valores_permitidos&id=" . $row['allowed_value_id'] . "'>[" .$STATES. "]</a> ";
            echo "<a href='/sgbd/edicao-de-dados/?action=apagar_valores_permitidos&id=" . $row['allowed_value_id'] . "'>[Apagar]</a>";   
            echo "<a href='/sgbd/edicao-de-dados/?action=historico_valores_permitidos&id=" . $row['allowed_value_id'] . "'>[Historico]</a>";   

            echo "</td>";

            echo "</tr>";
        }

        echo "</tbody></table>"; // Fim da tabela
    } else {
        echo "<p>Não há dados para exibir.</p>"; // Mensagem se não houver resultados
    }

} elseif ($_REQUEST['estado'] === 'introducao') {
    $subitem_id = intval($_REQUEST['subitem_id']); // Obtém o ID do subitem da requisição

    // Formulário para introduzir novos valores permitidos
    echo "<h3>Gestão de valores permitidos - introdução</h3>";
    echo "<form method='POST' action=''>";
    echo "<label for='valor'>Valor:</label>";
    echo "<input type='text' name='valor' id='valor'>"; // Campo de texto para o valor
    echo "<input type='hidden' name='estado' value='inserir'>";
    echo "<input type='hidden' name='subitem_id' value='{$subitem_id}'>"; // Campo oculto com o ID do subitem
    echo "<input type='submit' value='Inserir valor permitido'>";
    echo "</form>";

    echo '<p><a href="javascript:history.back()">Voltar para trás</a></p>'; // Link para voltar à página anterior

} elseif ($_REQUEST['estado'] === 'inserir') {
    $valor = trim($_REQUEST['valor']); // Valor do formulário
    $subitem_id = intval($_REQUEST['subitem_id']); // ID do subitem
    $state = 'active'; // Estado inicial

    // Validação do valor para aceitar apenas letras e espaços
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $valor)) {
        echo "<p>Erro: O valor permitido só pode conter letras e espaços.</p>";
        echo '<p><a href="javascript:history.back()">Voltar para trás</a></p>';
        exit;
    }

    // Verifica se o subitem existe e é do tipo enum
    $check_query = "SELECT id FROM subitem WHERE id = $subitem_id AND value_type = 'enum'";
    $check_result = mysqli_query($link, $check_query);

    if (mysqli_num_rows($check_result) === 0) {
        echo "<p>Erro: O subitem com ID $subitem_id não existe ou não é do tipo enum.</p>";
        exit;
    }

    // Sanitiza o valor para evitar injeções
    $valor_sanitizado = mysqli_real_escape_string($link, $valor);

    // Insere o novo valor permitido
    $insert_query = "
        INSERT INTO subitem_allowed_value (subitem_id, value, state) 
        VALUES ($subitem_id, '$valor_sanitizado', '$state')
    ";

    $insert_result = mysqli_query($link, $insert_query);

    if ($insert_result) {
        echo "<p>Inseriu o novo valor permitido com sucesso!</p>";
        echo "<form method='get' action='{$current_page}'>";
        echo "<button type='submit'>Continuar</button>";
        echo "</form>";
    } else {
        echo "<p>Erro ao executar a query: " . mysqli_error($link) . "</p>";
    }
    echo '<p><a href="javascript:history.back()">Voltar para trás</a></p>';
}

// Fecha a conexão com o banco de dados
mysqli_close($link);
?>

</body>
</html>