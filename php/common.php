<?php

$clientsideval=0;
$link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

global $current_page; $current_page = get_site_url().'/'.basename(get_permalink());




function verificar_login() {
    if (is_user_logged_in()) {
        return "Utilizador logado";
    } else {
        return "Utilizador não logado";
    }
}





function db_connect() {
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$link) {
        die("Database connection failed: " . mysqli_connect_error());
    }
    return $link;
}

function verificar_estado_execucao(){

if (empty($_POST['estado'])){
    }
}

function get_enum_values($connection, $table, $column )
{
$query = " SHOW COLUMNS FROM $table LIKE '$column' ";
$result = mysqli_query($connection, $query );
$row = mysqli_fetch_array($result , MYSQLI_NUM );
#extract the values
#the values are enclosed in single quotes
#and separated by commas
$regex = "/'(.*?)'/";
preg_match_all( $regex , $row[1], $enum_array );
$enum_fields = $enum_array[1];
return( $enum_fields );
}

function contar_rowspan($result, $field_name) {
        $rowspan_counts = [];
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            $value = $row[$field_name] ?? '';
            if ($value !== '') {
                if (!isset($rowspan_counts[$value])) {
                    $rowspan_counts[$value] = 0;
                }
                $rowspan_counts[$value]++;
            }
        }
        return $rowspan_counts;
    }


//Função para adicionar capability
function add_manage_capability($capability) {
$roles = wp_roles()->roles; 

foreach ($roles as $role_name => $role_info) {
    $role = get_role($role_name);
        if ($role) {
            $role->add_cap($capability);
            }
        }

    return "Capacidade '$capability' adicionada a todas as roles.";
}

?>