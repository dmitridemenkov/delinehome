<?php
/**
 * Раздел «Материалы» — самостоятельные страницы со вкладками.
 * К модулю «Материалы» на главной и к странице «Поставщики» отношения не имеет.
 */

function deline_materials_tabs() {
    return [
        ['slug' => 'materialy-korpusa',      'label' => 'Корпуса'],
        ['slug' => 'materialy-fasady',       'label' => 'Фасады'],
        ['slug' => 'materialy-stoleshnitsy', 'label' => 'Столешницы'],
        ['slug' => 'materialy-furnitura',    'label' => 'Фурнитура'],
    ];
}
