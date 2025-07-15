<?php

require_once(
    "custom/php/common.php"
);

$link = mysqli_connect(
    DB_HOST,
    DB_USER,
    DB_PASSWORD,
    DB_NAME
);

if (! $link) {
    die(
        "Erro: conexão com o banco de dados não está ativa. Verifique o arquivo common.php."
    );
}

//Estado : Procurar 
if (! isset($_REQUEST['estado']) ||
    $_REQUEST['estado'] === "procurar"
) {
    echo (
        "<h3>Inserção de valores - criança - procurar</h3>"
    );
    echo (
        "<p>Introduza um dos nomes da criança a encontrar e/ou a data de nascimento dela</p>"
    );
    echo (
        "<form method='POST' action=''>" .
        "<label>Nome</label>" .
        "<input type='text' name='nome'>" .
        "<label>Data de nascimento - (no formato AAAA-MM-DD) </label>" .
        "<input type='text' name='data_nascimento' placeholder='AAAA-MM-DD'>" .
        "<input type='hidden' name='estado' value='escolher_crianca'>" .
        "<input type='submit' value='Submeter'>" .
        "</form>"
    );
}

// Estado: escolher a criança
if (
    isset($_REQUEST['estado']) &&
    $_REQUEST['estado'] === "escolher_crianca"
) {
    $nome = mysqli_real_escape_string(
        $link,
        $_POST['nome']
    );

    $data_nascimento = mysqli_real_escape_string(
        $link,
        $_POST['data_nascimento']
    );

    $query = (
        "SELECT * FROM child WHERE name LIKE '%$nome%' " .
        "AND birth_date LIKE '%$data_nascimento%'"
    );

    $result = mysqli_query(
        $link,
        $query
    );

    if (mysqli_num_rows($result) == 0) {
        echo (
            "<p>Nenhuma criança encontrada.</p>"
        );
    } else {
        echo (
            "<h3>Inserção de valores - criança - escolher</h3>"
        );

        while (
            $row = mysqli_fetch_assoc(
                $result
            )
        ) {
            echo (
                "<a href='insercao-de-valores?estado=escolher_item&crianca={$row['id']}'>" .
                "{$row['name']} ({$row['birth_date']})</a><br>"
            );
        }
    }
}

// Estado: escolher item
if (
    isset($_REQUEST['estado']) &&
    $_REQUEST['estado'] === "escolher_item"
) {
    $_SESSION['child_id'] = $_REQUEST['crianca'];

    $child_id = mysqli_real_escape_string(
        $link,
        $_SESSION['child_id']
    );

    echo (
        "<h3>Inserção de valores - escolher item</h3>"
    );

    $query = (
        "SELECT it.name AS item_category, i.name AS item_name, i.id AS item_id " .
        "FROM item_type it " .
        "JOIN item i ON i.item_type_id = it.id " .
        "WHERE i.state = 'active' " .
        "ORDER BY it.name, i.name"
    );

    $result = mysqli_query(
        $link,
        $query
    );

    if (!$result) {
        echo (
            "<p>Erro na query SQL: " .
            mysqli_error($link) .
            "</p>"
        );

        return;
    }

    if (mysqli_num_rows($result) == 0) {
        echo (
            "<p>Não há itens disponíveis para esta criança.</p>"
        );
    } else {
        $items_by_category = [];

        while (
            $row = mysqli_fetch_assoc($result)
        ) {
            $items_by_category[
                $row['item_category']
            ][] = $row;
        }

        echo "<ul>";

        foreach ($items_by_category as $category => $items) {
            echo (
                "<li><strong>$category</strong><ul>"
            );

            foreach ($items as $item) {
                echo (
                    "<li><a href='insercao-de-valores?estado=editar_subitens&item={$item['item_id']}'>" .
                    "{$item['item_name']}</a></li>"
                );
            }

            echo "</ul></li>";
        }

        echo "</ul>";
        echo (
            "<a href='insercao-de-valores?estado=procurar'>Voltar atrás</a>"
        );
    }
}


// Estado: introdução
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "introducao") {
    $_SESSION['item_id'] = $_REQUEST['item'];
    $item_id = mysqli_real_escape_string($link, $_SESSION['item_id']);

    $query = "SELECT * FROM item WHERE id = '$item_id'";
    $result = mysqli_query($link, $query);
    $item = mysqli_fetch_assoc($result);

    echo "<h3>Inserção de valores - {$item['name']} - introdução</h3>";

    $subitem_query = "SELECT * FROM subitem WHERE item_id = '$item_id' AND state = 'active' ORDER BY form_field_order";
    $subitem_result = mysqli_query($link, $subitem_query);

    if (mysqli_num_rows($subitem_result) == 0) {
        echo "<p>Não há subitens ativos para este item.</p>";
    } else {
        echo "<form method='POST' action='insercao-de-valores?estado=validar&item=$item_id'>";
        while ($subitem = mysqli_fetch_assoc($subitem_result)) {
            echo "<label>{$subitem['name']}:</label> ";

            switch ($subitem['value_type']) {
                case 'text':
                    echo "<input type='text' name='{$subitem['form_field_name']}'><br>";
                    break;
                case 'int':
                    echo "<input type='number' name='{$subitem['form_field_name']}'><br>";
                    break;
                case 'bool':
                    echo "<input type='radio' name='{$subitem['form_field_name']}' value='1'> Sim ";
                    echo "<input type='radio' name='{$subitem['form_field_name']}' value='0'> Não<br>";
                    break;
                case 'enum':
                    $allowed_values_query = "SELECT * FROM subitem_allowed_value WHERE subitem_id = {$subitem['id']} AND state = 'active'";
                    $allowed_values_result = mysqli_query($link, $allowed_values_query);

                    echo "<select name='{$subitem['form_field_name']}'>";
                    while ($value = mysqli_fetch_assoc($allowed_values_result)) {
                        echo "<option value='{$value['value']}'>{$value['value']}</option>";
                    }
                    echo "</select><br>";
                    break;
            }
        }
        echo "<input type='submit' value='Submeter'>";
        echo "</form>";
    }
}

// Estado: validar
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "validar") {
    echo "<h3>Validação de dados</h3>";
    echo "<p>Confirme os dados antes de inserir:</p>";
    echo "<ul>";
    foreach ($_POST as $key => $value) {
        echo "<li><strong>{$key}:</strong> {$value}</li>";
    }
    echo "</ul>";
    echo "<form method='POST' action='insercao-de-valores?estado=inserir'>";
    foreach ($_POST as $key => $value) {
        echo "<input type='hidden' name='{$key}' value='{$value}'>";
    }
    echo "<input type='submit' value='Confirmar'>";
    echo "</form>";
}

// Estado: inserir
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "inserir") {
    $child_id = $_SESSION['child_id'];
    $item_id = $_SESSION['item_id'];
    $time = date('H:i:s');
    $date = date('Y-m-d');
    $producer = wp_get_current_user()->user_login;

    foreach ($_POST as $key => $value) {
        $query = "INSERT INTO value (child_id, subitem_id, value, date, time, producer) VALUES ('$child_id', '$key', '$value', '$date', '$time', '$producer')";
        mysqli_query($link, $query);
    }

    echo "<p>Os valores foram inseridos com sucesso.</p>";
}




// Estado: editar subitens do cabelo ou autismo
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "editar_subitens") {
    $item_id = mysqli_real_escape_string($link, $_REQUEST['item']);
    $_SESSION['item_id'] = $item_id;

    // Obter informações do item
    $query = "SELECT name FROM item WHERE id = '$item_id'";
    $result = mysqli_query($link, $query);

    if (!$result || mysqli_num_rows($result) == 0) {
        echo "<p>Erro: Item não encontrado.</p>";
        echo "<a href='insercao-de-valores?estado=escolher_item&crianca={$_SESSION['child_id']}'>Voltar atrás</a>";
        return;
    }

    $item = mysqli_fetch_assoc($result);
    echo "<h3>Inserção de valores - {$item['name']}</h3>";
    echo "<p><strong>* Obrigatório</strong></p>";

    // Obter subitens associados ao item
    $subitem_query = "
        SELECT si.id AS subitem_id, si.name AS subitem_name, si.form_field_name, si.form_field_type, si.value_type
        FROM subitem si
        WHERE si.item_id = '$item_id' AND si.state = 'active'
        ORDER BY si.form_field_order ASC
    ";
    $subitem_result = mysqli_query($link, $subitem_query);

    if (!$subitem_result || mysqli_num_rows($subitem_result) == 0) {
        echo "<p>Não há subitens associados a este item.</p>";
        echo "<a href='insercao-de-valores?estado=escolher_item&crianca={$_SESSION['child_id']}'>Voltar atrás</a>";
        return;
    }

    // Início do formulário para os subitens
    echo "<form method='POST' action='/sgbd/insercao-de-valores?estado=validar_subitens'>";

    while ($subitem = mysqli_fetch_assoc($subitem_result)) {
        $form_field_type = $subitem['form_field_type']; // Tipo do campo (text, selectbox, checkbox, etc.)
        $form_field_name = $subitem['form_field_name']; // Nome do campo para o formulário
        $subitem_name = $subitem['subitem_name']; // Nome do subitem para exibição

        echo "<div style='margin-bottom: 15px;'>";
        echo "<label for='{$form_field_name}'><strong>{$subitem_name}*</strong></label>";

        // Gerar o campo dinamicamente
        switch ($form_field_type) {
            case 'text':
                echo "<input type='text' id='{$form_field_name}' name='{$form_field_name}' required><br>";
                break;

            case 'number':
                echo "<input type='number' id='{$form_field_name}' name='{$form_field_name}' required><br>";
                break;

            case 'radio':
            case 'checkbox':
            case 'selectbox':
                // Buscar valores permitidos para o subitem
                $allowed_values_query = "
                    SELECT value 
                    FROM subitem_allowed_value 
                    WHERE subitem_id = '{$subitem['subitem_id']}' AND state = 'active'
                ";
                $allowed_values_result = mysqli_query($link, $allowed_values_query);

                if ($allowed_values_result && mysqli_num_rows($allowed_values_result) > 0) {
                    if ($form_field_type === 'radio') {
                        echo "<div>";
                        while ($value = mysqli_fetch_assoc($allowed_values_result)) {
                            echo "<input type='radio' id='{$form_field_name}_{$value['value']}' name='{$form_field_name}' value='{$value['value']}'> 
                                  <label for='{$form_field_name}_{$value['value']}'>{$value['value']}</label><br>";
                        }
                        echo "</div>";
                    } elseif ($form_field_type === 'checkbox') {
                        echo "<div>";
                        while ($value = mysqli_fetch_assoc($allowed_values_result)) {
                            echo "<input type='checkbox' id='{$form_field_name}_{$value['value']}' name='{$form_field_name}[]' value='{$value['value']}'> 
                                  <label for='{$form_field_name}_{$value['value']}'>{$value['value']}</label><br>";
                        }
                        echo "</div>";
                    } elseif ($form_field_type === 'selectbox') {
                        echo "<select id='{$form_field_name}' name='{$form_field_name}' required>";
                        echo "<option value=''>Selecione</option>";
                        while ($value = mysqli_fetch_assoc($allowed_values_result)) {
                            echo "<option value='{$value['value']}'>{$value['value']}</option>";
                        }
                        echo "</select><br>";
                    }
                } else {
                    echo "<p>Erro: Nenhum valor permitido definido para {$subitem_name} na base de dados.</p>";
                }
                break;

            default:
                echo "<p>Erro: Tipo de campo não suportado para {$subitem_name}.</p>";
                break;
        }

        echo "</div>";
    }

    echo "<button type='submit'>Submeter</button>";
    echo "</form>";
    echo "<a href='insercao-de-valores?estado=escolher_item&crianca={$_SESSION['child_id']}'>Voltar atrás</a>";
}


// Estado: validar subitens antes de salvar
if (isset($_REQUEST['estado']) && $_REQUEST['estado'] === "validar_subitens") {
    echo "<h3>Inserção de valores - validar</h3>";

    // Exibir os valores submetidos pelo usuário
    echo "<ul>";
    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            $value = implode(', ', $value);
        }
        echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";

    echo "<p><strong>Estamos prestes a inserir os dados abaixo na base de dados.</strong></p>";
    echo "<p><strong>Confirma que os dados estão correctos e pretende submeter os mesmos?</strong></p>";

    // Formulário para confirmação
    echo "<form method='POST' action='/sgbd/insercao-de-valores?estado=salvar_subitens'>";

    foreach ($_POST as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subValue) {
                echo "<input type='hidden' name='" . htmlspecialchars($key) . "[]' value='" . htmlspecialchars($subValue) . "'>";
            }
        } else {
            echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        }
    }
    
    echo "<button type='submit'>SUBMETER</button>";
    echo "</form>";

    // Link para voltar à edição
    echo "<a href='insercao-de-valores?estado=editar_subitens&item={$_SESSION['item_id']}'>Voltar atrás</a>";
}


// Estado: salvar subitens
if (
    isset($_REQUEST['estado']) &&
    $_REQUEST['estado'] === "salvar_subitens"
) {
    $item_id = $_SESSION['item_id'];
    $child_id = $_SESSION['child_id'];

    $query = "SELECT id, form_field_name FROM subitem WHERE item_id = '$item_id' AND state = 'active'";
    $result = mysqli_query(
        $link,
        $query
    );

    $subitems_map = [];
    while (
        $row = mysqli_fetch_assoc(
            $result
        )
    ) {
        $subitems_map[$row['form_field_name']] = $row['id'];
    }

    $date = date(
        'Y-m-d'
    );
    $time = date(
        'H:i:s'
    );
    $producer = wp_get_current_user()->user_login;

    $id_auxiliar = 1;
    foreach (
        $subitems_map as $field_name => $subitem_id
    ) {
        if (
            isset($_POST[$field_name]) &&
            !empty($_POST[$field_name])
        ) {
            $value = is_array(
                $_POST[$field_name]
            ) ? implode(
                ', ',
                $_POST[$field_name]
            ) : $_POST[$field_name];
            $value = mysqli_real_escape_string(
                $link,
                $value
            );
            if (preg_match('/(\d+)_/', $field_name, $matches)) {
                $id_auxiliar = $matches[1];
            }

            $query = "INSERT INTO `value` (id, child_id, subitem_id, value, date, time, producer)
                      VALUES ($id_auxiliar, '$child_id', '$subitem_id', '$value', '$date', '$time', '$producer')";

            if (!mysqli_query(
                $link,
                $query
            )) {
                echo (
                    "<p>Erro ao salvar '$field_name': " . mysqli_error(
                        $link
                    ) . "</p>"
                );
            }
        } else {
            echo (
                "<p>Aviso: Campo '$field_name' não foi enviado ou está vazio.</p>"
            );
        }
    }

    echo (
        "<p><strong>Os valores foram inseridos com sucesso!</strong></p>"
    );

    // Botões de navegação
    echo (
        "<form method='GET' action='insercao-de-valores'>"
    );
    echo (
        "<input type='hidden' name='estado' value='escolher_item'>"
    );
    echo (
        "<input type='hidden' name='crianca' value='{$child_id}'>"
    );
    echo (
        "<button type='submit'>Escolher</button>"
    );
    echo (
        "</form>"
    );
    echo (
        "<form method='GET' action='insercao-de-valores'>"
    );
    echo (
        "<input type='hidden' name='estado' value='procurar'>"
    );
    echo (
        "<button type='submit'>Voltar</button>"
    );
    echo (
        "</form>"
    );
}

?>