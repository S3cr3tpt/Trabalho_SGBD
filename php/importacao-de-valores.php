<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Subitens</title>
    <link rel="stylesheet" type="text/css" href="/sgbd/custom/css/ag.css">
</head>
<body>

<?php

require_once("custom/php/common.php");
require 'vendor/autoload.php'; 
use PhpOffice\PhpSpreadsheet\IOFactory;

$link = db_connect();

add_manage_capability("Values Import");

// Verifica se o usuário está logado e tem permissão
if (!is_user_logged_in() || !current_user_can("Values Import")) {
    echo "<p>Não tem autorização para aceder a esta página</p>";
    exit;
}

$current_page = get_site_url() . '/' . basename(get_permalink());

// Estado inicial: Escolher Criança
if (empty($_REQUEST['estado'])) {
    echo "<h3>Importação de Valores - Escolher Criança</h3>";

    $query_criancas = "SELECT id, name, birth_date, tutor_name, tutor_phone, tutor_email FROM child";

    $result = mysqli_query($link, $query_criancas);

    if (mysqli_num_rows($result) > 0) {
        echo "
        <table border='1' cellpadding='5' cellspacing='0'>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Data de nascimento</th>
                    <th>Enc. de educação</th>
                    <th>Telefone do Enc.</th>
                    <th>E-mail</th>
                </tr>
            </thead>
            <tbody>
        ";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td><a href='{$current_page}?estado=escolheritem&crianca={$row['id']}'>{$row['name']}</a></td>";
            echo "<td>{$row['birth_date']}</td>";
            echo "<td>{$row['tutor_name']}</td>";
            echo "<td>{$row['tutor_phone']}</td>";
            echo "<td>" . (empty($row['tutor_email']) ? '-' : $row['tutor_email']) . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>Não há crianças registradas.</p>";
    }

// Estado escolheritem: Listar Itens
} elseif ($_REQUEST['estado'] === "escolheritem") {
    $crianca_id = isset($_GET['crianca']) ? intval($_GET['crianca']) : 0;
    echo "<h3>Importação de Valores</h3>";

    $query_item_type = "SELECT item_type.name AS item_type_name, item_type.id AS item_type_id FROM item_type";

    $item_type_result = mysqli_query($link, $query_item_type);

    if (mysqli_num_rows($item_type_result) > 0) {
        echo '<ul class="main-list">';

        while ($row = mysqli_fetch_assoc($item_type_result)) {
            $item_type_name = $row['item_type_name'];

            $query_dados = "SELECT item.name AS item_name, item.id AS item_id FROM item WHERE item.item_type_id = {$row['item_type_id']}";

            $dados_result = mysqli_query($link, $query_dados);

            echo '<li class="main-list-item">';
            echo '<div class="rotating-icon"></div>'; // Bolas animadas
            echo "<strong>$item_type_name</strong>";
            echo '<ul class="sub-list">';

            if (mysqli_num_rows($dados_result) > 0) {
                while ($row_dado = mysqli_fetch_assoc($dados_result)) {
                    $item_name = $row_dado['item_name'];
                    $item_id = $row_dado['item_id'];

                    echo "<li class='sub-list-item'>
                            <a class='item-link' href='{$current_page}?estado=introducao&crianca={$crianca_id}&item={$item_id}'>$item_name</a>
                          </li>";
                }
            } else {
                echo '<li class="sub-list-item">Sem itens disponíveis</li>';
            }

            echo '</ul>';
            echo '</li>';
        }
        echo '</ul>';

    } else {
        echo "<p>Não há tipos de itens disponíveis.</p>";
    }

    echo '<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>';

// Estado introducao: Configuração dos subitens
} elseif ($_REQUEST['estado'] === "introducao") {
    $crianca_id = isset($_GET['crianca']) ? intval($_GET['crianca']) : 0;
    $item_id = isset($_GET['item']) ? intval($_GET['item']) : 0;

    echo "<h3>Importação de Valores - Configuração</h3>";

    $query = "
        SELECT 
            subitem.form_field_name, 
            subitem.id, 
            subitem_allowed_value.value
        FROM subitem
        INNER JOIN item ON subitem.item_id = item.id
        LEFT JOIN subitem_allowed_value ON subitem.id = subitem_allowed_value.subitem_id
        WHERE item.id = $item_id
    ";

    $result = mysqli_query($link, $query);

    $form_field_names = [];
    $ids = [];
    $values = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $form_field_names[] = $row['form_field_name'];
        $ids[] = $row['id'];
        $values[] = $row['value'];
    }

    echo "<table border='1' cellpadding='5' cellspacing='0'>";

    echo "<tr>";
    foreach ($form_field_names as $field_name) {
        echo "<td>$field_name</td>";
    }
    echo "</tr><tr>";
    foreach ($ids as $id) {
        echo "<td>$id</td>";
    }
    echo "</tr><tr>";
    foreach ($values as $value) {
        echo "<td>$value</td>";
    }
    echo "</tr>";

    echo "</table>";
    echo "<p>Deverá copiar estas linhas para um ficheiro excel e introduzir os valores a importar, sendo que, no caso dos subitens enum, deverá constar um 0 quando esse valor permitido não se aplique à instância em causa e um 1 quando esse valor se aplica.";
    echo " O ficheiro deve estar em /Applications/XAMPP/xamppfiles/htdocs/WP/wordpress/NOME_DO_FICHEIRO, sendo o nome import_to_insert.xlsx\b.";

    // formulario de upload do ficheiro excel
    echo '
    <form action="?estado=validar" method="post" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px;">
        <input type="hidden" name="crianca_id" value="' . $crianca_id . '">
        <label for="file_input" style="padding: 8px 15px; background-color: #007BFF; color: white; text-align: center; border-radius: 5px; cursor: pointer;">
            CARREGAR FICHEIRO
        </label>
        <input type="file" id="file_input" name="excel_file" accept=".xls,.xlsx" style="display: none;" required onchange="this.form.submit();">
    </form>
    ';

    echo '<p><a href="javascript:history.back()">Voltar para trás</a></p>';
  
//Guardar na sessao os arrays para depois usar no estado validar
$_SESSION['form_field_names'] = $form_field_names;
$_SESSION['ids'] = $ids;
$_SESSION['values'] = $values;

}

if ($_REQUEST['estado'] == "validar") {
    // Variáveis da sessão
    $form_field_names = $_SESSION['form_field_names'];
    $ids = $_SESSION['ids'];
    $values = $_SESSION['values'];
    $uploaded_file = $_FILES['excel_file']['tmp_name'];

    try {
        // Carregar e processar o ficheiro Excel
        $spreadsheet = IOFactory::load($uploaded_file);
        $data = $spreadsheet->getActiveSheet()->toArray();

        if (empty($data)) {
            exit("<p>Erro: O ficheiro está vazio.</p>");
        }

        $_SESSION['excel_data'] = $data;

        if (empty($_POST['crianca_id'])) {
            exit("<p>Erro: O campo 'crianca_id' não foi fornecido.</p>");
        }

        $_SESSION['crianca_id'] = intval($_POST['crianca_id']);

        // Exibir dados em tabela
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        foreach ($data as $index => $row) {
            echo $index == 0 ? '<thead><tr>' : '<tr>';
            foreach ($row as $cell) {
                echo $index == 0 ? "<th>$cell</th>" : "<td>$cell</td>";
            }
            echo '</tr>' . ($index == 0 ? '</thead><tbody>' : '');
        }
        echo "</tbody></table>";

        echo "<p>Está prestes a inserir os dados seguintes na base de dados. Confirma?</p>";
        echo '<form action="?estado=inserir" method="post">
                <button type="submit">Confirmar</button>
              </form>';

        // Iniciar a variável validação para validação do servidor
        $_SESSION['validacao'] = 1;

        // Validar primeiras 3 linhas do Excel
        $linhas_esperadas = [$form_field_names, $ids, $values];
        foreach ($linhas_esperadas as $indice => $linha_esperada) {
            if ($data[$indice] !== $linha_esperada) {
                echo "<p>Erro na linha " . ($indice + 1) . " do Excel.</p>";
                $_SESSION['validacao'] = 0;
                exit('<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>');
            }
        }

        // Recolher colunas do subitem por tipo
        $query_subitem_types = [
            'enum' => "SELECT subitem.form_field_name AS subitem_form_field_name FROM subitem WHERE value_type = 'enum'",
            'int' => "SELECT subitem.form_field_name AS subitem_form_field_name FROM subitem WHERE value_type = 'int'",
            'double' => "SELECT subitem.form_field_name AS subitem_form_field_name FROM subitem WHERE value_type = 'double'",
            'text' => "SELECT subitem.form_field_name AS subitem_form_field_name FROM subitem WHERE value_type = 'text'"
        ];

        $indices = ['enum' => [], 'int' => [], 'double' => [], 'text' => []];
        foreach ($query_subitem_types as $type => $query) {
            $result = mysqli_query($link, $query);
            while ($row = mysqli_fetch_assoc($result)) {
                $subitems[$type][] = $row['subitem_form_field_name'];
            }
            foreach ($form_field_names as $index => $form_field_name) {
                if (in_array($form_field_name, $subitems[$type] ?? [])) {
                    $indices[$type][] = $index;
                }
            }
        }

        // Validação por tipo
        foreach (['enum', 'int', 'double', 'text'] as $type) {
            foreach ($indices[$type] as $indice) {
                foreach ($data as $rowIndex => $row) {
                    if ($rowIndex >= 3) {
                        $cellValue = isset($row[$indice]) ? trim($row[$indice]) : null;

                        switch ($type) {
                            case 'enum':
                                $cellValue = is_numeric($cellValue) ? (int)$cellValue : $cellValue;
                                if (!in_array($cellValue, [0, 1], true)) {
                                    echo "<p>Erro na linha " . ($rowIndex + 1) . ", coluna " . ($indice + 1) . ": valores permitidos são apenas 0 ou 1.</p>";
                                    $_SESSION['validacao'] = 0;
                                    exit('<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>');
                                }
                                break;

                            case 'int':
                                if (!ctype_digit($cellValue)) {
                                    echo "<p>Erro na linha " . ($rowIndex + 1) . ", coluna " . ($indice + 1) . ": valor deve ser um inteiro válido.</p>";
                                    $_SESSION['validacao'] = 0;
                                    exit('<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>');
                                }
                                break;

                            case 'double':
                                if (!is_numeric($cellValue)) {
                                    echo "<p>Erro na linha " . ($rowIndex + 1) . ", coluna " . ($indice + 1) . ": valor deve ser um número decimal ou inteiro válido.</p>";
                                    $_SESSION['validacao'] = 0;
                                    exit('<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>');
                                }
                                break;

                            case 'text':
                                if (!is_string($cellValue) || $cellValue === '') {
                                    echo "<p>Erro na linha " . ($rowIndex + 1) . ", coluna " . ($indice + 1) . ": valor deve ser texto não vazio.</p>";
                                    $_SESSION['validacao'] = 0;
                                    exit('<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>');
                                }
                                break;
                        }
                    }
                }
            }
        }

    } catch (Exception $e) {
        echo "<p>Erro ao processar o ficheiro: " . $e->getMessage() . "</p>";
    }

} elseif ($_REQUEST['estado'] == 'inserir') {
    $validacao = $_SESSION['validacao'];

    if (!isset($_SESSION['crianca_id']) || !isset($_SESSION['excel_data']) || $validacao == 0) {
        echo "<p>Erro: Dados insuficientes para realizar a inserção.</p>";
        exit('<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>');
    }

    $crianca_id = $_SESSION['crianca_id'];
    $excel_data = $_SESSION['excel_data'];

    $subitens_id = $excel_data[1];
    $query_enum_values = "SELECT subitem_id, value FROM subitem_allowed_value WHERE state = 1";
    $result_enum_values = mysqli_query($link, $query_enum_values);

    $enum_values_map = [];
    while ($row = mysqli_fetch_assoc($result_enum_values)) {
        $enum_values_map[$row['subitem_id']][] = $row['value'];
    }

    $query_inserir = "INSERT INTO `value` (child_id, subitem_id, value, date, time) VALUES ";
    $valores_inserir = [];

    foreach ($excel_data as $index => $row) {
        if ($index < 2) {
            continue;
        }

        foreach ($row as $coluna => $valor) {
            $subitem_id = $subitens_id[$coluna] ?? null;

            if (!empty($valor)) {
                if (isset($enum_values_map[$subitem_id])) {
                    if ($valor == 1) {
                        foreach ($enum_values_map[$subitem_id] as $nome_enum) {
                            $valores_inserir[] = "($crianca_id, $subitem_id, '" . mysqli_real_escape_string($link, $nome_enum) . "', NOW(), CURRENT_TIME())";
                        }
                    }
                } else {
                    $valores_inserir[] = "($crianca_id, $subitem_id, '" . mysqli_real_escape_string($link, $valor) . "', NOW(), CURRENT_TIME())";
                }
            }
        }
    }

    if (!empty($valores_inserir)) {
        $query_inserir .= implode(", ", $valores_inserir);

        if (mysqli_query($link, $query_inserir)) {
            echo "<p>Os valores foram inseridos com sucesso na base de dados.</p>";
        } else {
            echo "<p>Erro ao inserir os dados: " . mysqli_error($link) . "</p>";
        }
    } else {
        echo "<p>Não há dados válidos para inserir.</p>";
    }

    echo '<form action="' . $current_page . '" method="get">
        <button type="submit">INICIO</button>
    </form>';
    echo '<p><a href="javascript:history.back()" class="back-button">Voltar para trás</a></p>';
}

// Fechar conexão com o banco de dados
mysqli_close($link);
?>

</body>
</html>