<?php

// Справочник "Ошибки 404"
return array(
    'params' => array(
        'in_structures' => array('Ideal_DataList'), // в каких структурах можно создавать эту структуру
        'elements_cms' => 10, // количество элементов в списке в CMS
        'elements_site' => 15, // количество элементов в списке на сайте
        'field_name' => '', // поле для входа в список потомков
        'field_sort' => 'date_create DESC', // поле, по которому проводится сортировка в CMS
        'field_list' => array('date_create', 'url', 'count')
    ),
    'fields' => array(
        'ID' => array(
            'label' => 'Идентификатор',
            'sql' => 'int(4) unsigned not null auto_increment primary key',
            'type' => 'Ideal_Hidden'
        ),
        'prev_structure' => array(
            'label' => 'ID родительских структур',
            'sql' => 'char(15)',
            'type' => 'Ideal_Hidden'
        ),
        'date_create' => array(
            'label' => 'Дата создания',
            'sql' => 'int(11) not null',
            'type' => 'Ideal_DateSet'
        ),
        'url' => array(
            'label' => 'Адрес',
            'sql' => 'text not null',
            'type' => 'Ideal_Text'
        ),
        'count' => array(
            'label' => 'Количество заходов',
            'sql' => 'int',
            'type' => 'Ideal_Integer'
        ),
    ),
);
