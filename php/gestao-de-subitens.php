<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Subitens</title>
    <link rel="stylesheet" type="text/css" href="/sgbd/custom/css/ag.css">
    <script>
        // Função para validar o formulário
        function validateForm() {
            const ordemFormulario = document.querySelector('input[name="ordem_formulario"]');
            if (isNaN(ordemFormulario.value)) {
                alert('Erro: O campo "Ordem do campo no formulário" deve conter apenas números.');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
<?php
// Inclui as configurações necessárias e conecta ao banco de dados
global $current_page;
$current_page = get_site_url() . '/' . basename(get_permalink());
require_once("custom/php/common.php");
$link = db_connect();

// Verifica se o usuário tem a permissão necessária
add_manage_capability("Manage subitems");

if (!is_user_logged_in() || !current_user_can("Manage subitems")) {
    echo "<p>Não tem autorização para aceder a esta página</p>";
    exit;
}

// Função para imprimir uma linha da tabela, agrupando itens iguais
function imprimir($row, $count, &$current_item, $item_name_column) {
    echo "<tr>";
    if ($current_item !== $row[$item_name_column]) {
        $current_item = $row[$item_name_column];
        $rowspan = $count[$current_item] ?? 1;
        echo "<td rowspan='{$rowspan}'>{$current_item}</td>";
    }
}

// Se nenhum estado for definido, exibe os subitens
if (empty($_POST['estado'])) {
    // Query para buscar todos os subitens
    $allsubitem = "
    SELECT 
    item.name AS item_name,
    subitem.id AS subitem_id,
    subitem.name AS subitem_name,
    subitem.value_type AS subitem_value_type,
    subitem.form_field_name AS subitem_form_field_name,
    subitem.form_field_type AS subitem_form_field_type,
    subitem_unit_type.name AS subitem_unit_type,
    subitem.form_field_order AS subitem_form_field_order,
    subitem.mandatory AS subitem_mandatory,
    subitem.state AS subitem_state
    FROM item
    LEFT JOIN subitem
        ON subitem.item_id = item.id
    LEFT JOIN subitem_unit_type
        ON subitem.unit_type_id = subitem_unit_type.id
    ORDER BY item.name";

    // Executa a query e trata erros
    $result = mysqli_query($link, $allsubitem);

    if (!$result) {
        die("Erro na query: " . mysqli_error($link));
    }

    // Verifica se há resultados
    if ($result->num_rows > 0) {
        echo "
        <table border='10' cellpadding='5' cellspacing='0'>
            <thead>
              <tr>
                    <th>item</th>
                    <th>id</th>
                    <th>subitem</th>
                    <th>tipo de valor</th>
                    <th>nome do campo no formulário</th>
                    <th>tipo do campo no formulário</th>
                    <th>tipo de unidade</th>
                    <th>ordem no campo no formulário</th>
                    <th>obrigatório</th>
                    <th>estado</th>
                    <th>ação</th>
              </tr>
            </thead>";

        echo "<tbody>";

        // Conta as ocorrências de itens para rowspan
        $count = contar_rowspan($result, 'item_name');

        mysqli_data_seek($result, 0);

        $current_item = null;
        while ($row = mysqli_fetch_assoc($result)) {
            imprimir($row, $count, $current_item, 'item_name');

            if (empty($row['subitem_id'])) {
                echo "<td colspan='10' style='text-align: center;'>Não há subitens especificados</td>";
                continue;
            }

            // Exibe os dados do subitem e os links de ação
            echo "
                    <td>" . ($row['subitem_id'] ?? '') . "</td>
                    <td>" . ($row['subitem_name'] ?? '') . "</td>
                    <td>" . ($row['subitem_value_type'] ?? '') . "</td>
                    <td>" . ($row['subitem_form_field_name'] ?? '') . "</td>
                    <td>" . ($row['subitem_form_field_type'] ?? '') . "</td>
                    <td>" . ($row['subitem_unit_type'] ?? '') . "</td>
                    <td>" . ($row['subitem_form_field_order'] ?? '') . "</td>
                    <td>" . ($row['subitem_mandatory'] ? 'Sim' : 'Não') . "</td>
                    <td>" . ($row['subitem_state'] ?? '') . "</td>";

            $STATES = ($row['subitem_state'] == "inactive") ? " Ativar " : " Desativar ";
            echo "<td>"; 
            echo "<a href='" . $current_page . "?action=editar_subitens&id=" . $row['subitem_id'] . "'>[Editar]</a> ";
            echo "<a href='" . $current_page . "?action=desativar_subitens&id=" . $row['subitem_id'] . "'>[" . $STATES . "]</a> ";
            echo "<a href='" . $current_page . "?action=apagar_subitens&id=" . $row['subitem_id'] . "'>[Apagar]</a>";   
            echo "<a href='/sgbd/edicao-de-dados/?action=historico_subitens&id=" . $row['subitem_id'] . "'>[Historico]</a>";  

            echo "</td>";
        }     
        echo "
            </tbody>
        </table>";
    }

    // Formulário para inserir novo subitem
    echo "<h2> Gestão de subitens - introdução</h2>";

    echo '<form method="POST" action="" onsubmit="return validateForm()">';

    echo '<input type="text" name="nome_subitem" class="shared-style" placeholder="Nome do Subitem" /><br>';
    echo 'Tipo de valor: <br>';

    // Obtém os valores enum para o tipo de valor
    $enum_values = get_enum_values($link, 'subitem', 'value_type');

    if (empty($enum_values)) {
        echo "<p>Não existem valores enum</p>";
    } else {
        foreach ($enum_values as $value) {
             echo '<div class="radio-container">';
            echo "<input type=\"radio\" class=\"custom-radio\" name=\"valor_subitem\" value=\"$value\" id=\"$value\">";
            echo "<label for=\"$value\">$value</label>";
            echo '</div>';

        }
    }

    // Lista de itens disponíveis
    $query = "SELECT name FROM item";
    $result = mysqli_query($link, $query);

    if (!$result) {
        die("Erro na query: " . mysqli_error($link));
    }

    echo '<label for="select_item">Escolha um item:</label>';
    echo '<select name="select_item" id="select_item" class="shared-style">';

    while ($row = mysqli_fetch_assoc($result)) {
        $nome_item = $row['name'];
        echo '<option value="' . $nome_item . '">' . $nome_item . '</option>';
    }
    echo '</select><br>';

    // Obtém os valores enum para o tipo de campo no formulário
    $enum_values = get_enum_values($link, 'subitem', 'form_field_type');

    if (empty($enum_values)) {
        echo "<p>Não existem valores enum</p>";
    } else {
        foreach ($enum_values as $value) {
            echo '<div class="radio-container">';
                echo '<input type="radio" class="custom-radio" id="' . htmlspecialchars($value) . '" name="tipo_campo_formulario" value="' . htmlspecialchars($value) . '">';
                echo '<label for="' . htmlspecialchars($value) . '">' . htmlspecialchars($value) . '</label>';
            echo '</div>';

        }

    }

    // Lista de tipos de unidade disponíveis
    $query = "SELECT name FROM subitem_unit_type";
    $result = mysqli_query($link, $query);

    if (!$result) {
        die("Erro na query: " . mysqli_error($link));
    }

    echo '<label for="select_tipo_unidade">Escolha um tipo de unidade:</label>';
    echo '<select name="select_tipo_unidade" id="select_tipo_unidade" class="shared-style">';
    echo '<option value="null">em branco</option>';

    while ($row = mysqli_fetch_assoc($result)) {
        $nome_tipo_unidade = $row['name'];
        echo '<option value="' . $nome_tipo_unidade . '">' . $nome_tipo_unidade . '</option>';
    }
    echo '</select><br>';

    echo '<label for="ordem_formulario">Ordem do campo no formulário:</label>';
    echo '<input type="text" name="ordem_formulario" class="shared-style" /><br>';

    // Campo para definir se o subitem é obrigatório
    echo '<p>Obrigatório:</p>';
    echo '<div class="radio-container">';
    echo '<input type="radio" id="obrigatorio_sim" name="obrigatorio" class="custom-radio" value="1">';
    echo '<label for="obrigatorio_sim">Sim</label>';
    echo '</div>';
    echo '<div class="radio-container">';
    echo '<input type="radio" id="obrigatorio_nao" name="obrigatorio" class="custom-radio" value="0">';
    echo '<label for="obrigatorio_nao">Não</label>';
    echo '</div><br>';

    echo '<input type="hidden" name="estado" value="inserir">';

    echo '<button type="submit" name="inserir_subitem" class="btn-inserir">Inserir subitem</button>';

    echo '</form>';
} elseif ($_REQUEST['estado'] === "inserir") {
    // Lê os dados do formulário
    $nome_subitem = $_POST['nome_subitem'] ?? '';
    $valor_subitem = $_POST['valor_subitem'] ?? '';
    $item_escolhido = $_POST['select_item'] ?? '';
    $tipo_campo_formulario = $_POST['tipo_campo_formulario'] ?? '';
    $tipo_unidade = $_POST['select_tipo_unidade'] === 'null' ? 'NULL' : "(SELECT id FROM subitem_unit_type WHERE name = '" . $_POST['select_tipo_unidade'] . "')";
    $ordem_formulario = $_POST['ordem_formulario'] ?? '';
    $obrigatorio = $_POST['obrigatorio'] ?? 0;

    echo "<h3>Gestão de subitens - inserção</h3>";

    // Valida o nome do subitem
    if (!preg_match('/^[a-zA-Z]+$/', $nome_subitem)) {
        echo "<p>Erro: O nome do subitem deve conter apenas letras e não pode conter espaços.</p>";
        echo '<p><a href="javascript:history.back()">Voltar para trás</a></p>';
        exit;
    }

    // Valida se o campo 'ordem_formulario' contém apenas números
    if (!is_numeric($ordem_formulario)) {
        echo "<p>Erro: O campo 'Ordem do campo no formulário' deve conter apenas números.</p>";
        echo '<p><a href="javascript:history.back()">Voltar para trás</a></p>';
        exit;
    }

    // Verifica se todos os campos obrigatórios foram preenchidos
    if (!empty($nome_subitem) && !empty($valor_subitem) && !empty($item_escolhido) && !empty($tipo_campo_formulario) && $ordem_formulario !== '') {
        // Insere os dados no banco de dados
        $query_inserir = "
        INSERT INTO subitem (
            name, 
            value_type, 
            item_id, 
            form_field_name, 
            form_field_type, 
            unit_type_id, 
            form_field_order, 
            mandatory, 
            state
        ) VALUES (
            '$nome_subitem', 
            '$valor_subitem', 
            (SELECT id FROM item WHERE name = '$item_escolhido'), 
            '$nome_subitem', 
            '$tipo_campo_formulario', 
            $tipo_unidade, 
            $ordem_formulario, 
            $obrigatorio, 
            'active'
        )";

        $result = mysqli_query($link, $query_inserir);

        if ($result) {
            echo "<p>Inseriu os dados de novo subitem com sucesso</p>";
            echo '<form action="" method="get">';
            echo '<button type="submit">CONTINUAR</button>';
            echo '</form>';
        } else {
            die("Erro na query: " . mysqli_error($link));
        }
    } else {
        echo "Por favor, preencha todos os campos obrigatórios.";
    }
    echo '<p><a href="javascript:history.back()">Voltar para trás</a></p>';
}

// Fecha a conexão com o banco de dados
mysqli_close($link);
?>
</body>
</html>