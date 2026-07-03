<?php

declare(strict_types=1);

return [
    'actions' => [
        ['code' => 'view', 'label' => 'Ver', 'description' => 'Consultar pantallas, listados y detalles.'],
        ['code' => 'create', 'label' => 'Crear', 'description' => 'Registrar nuevos datos.'],
        ['code' => 'update', 'label' => 'Editar', 'description' => 'Modificar datos existentes.'],
        ['code' => 'delete', 'label' => 'Eliminar', 'description' => 'Eliminar registros cuando el módulo lo permita.'],
        ['code' => 'export', 'label' => 'Exportar', 'description' => 'Descargar reportes o archivos.'],
        ['code' => 'import', 'label' => 'Importar', 'description' => 'Realizar cargas masivas.'],
        ['code' => 'operate', 'label' => 'Operar', 'description' => 'Ejecutar procesos como ventas, movimientos, cierres o traslados.']
    ],

    'route_tokens' => [
        'export' => ['export', 'download', 'template'],
        'import' => ['import', 'upload'],
        'delete' => ['destroy', 'delete'],
        'operate' => [
            'pos', 'cancel', 'open', 'close', 'movement', 'movements',
            'operation', 'operations', 'transfer', 'transfers', 'receive',
            'assign', 'unassign', 'qrcamera', 'qrscanner', 'finalize'
        ],
        'create' => ['create', 'store'],
        'update' => ['edit', 'update']
    ],

    'route_actions' => [
        'stocks_management.store' => 'operate',
        'stocks_management.update' => 'operate',
        'user_attendances.checkin' => 'operate',
        'user_attendances.biometric-checkin' => 'operate',
        'user_attendances.checkout' => 'operate',
        'users.biometric-fingerprints.store' => 'update',
        'service_operations.sessions.store' => 'operate',
        'service_operations.floors.store' => 'create',
        'service_operations.stations.layout' => 'update',
        'service_operations.items.store' => 'operate',
        'service_operations.sessions.start' => 'operate',
        'service_operations.sessions.complete' => 'operate',
        'service_operations.items.start' => 'operate',
        'service_operations.items.complete' => 'operate'
    ],

    /* Endpoints compartidos por varias pantallas del mismo módulo técnico. */
    'route_modules' => [
        'sales.initParams' => ['sales.index', 'sales.create', 'sales.pos'],
        'sales.list' => ['sales.index'],
        'sales.show' => ['sales.index'],
        'sales.edit' => ['sales.index'],
        'sales.update' => ['sales.index'],
        'sales.cancel' => ['sales.index'],
        'sales.store' => ['sales.create', 'sales.pos'],

        'user_attendances.initParams' => ['user_attendances.index'],
        'user_attendances.list' => ['user_attendances.index'],
        'user_attendances.weekly' => ['user_attendances.index'],
        'user_attendances.checkin' => ['user_attendances.index'],
        'user_attendances.biometric-checkin' => ['user_attendances.index'],
        'user_attendances.checkout' => ['user_attendances.index'],

        'users.biometric-fingerprints.store' => ['users.index'],

        'service_operations.initParams' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.floors' => ['restaurant_pos.index'],
        'service_operations.floors.store' => ['restaurant_pos.index'],
        'service_operations.stations' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.stations.store' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.stations.layout' => ['restaurant_pos.index'],
        'service_operations.sessions' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.sessions.store' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.sessions.show' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.items.store' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.sessions.start' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.sessions.complete' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.items.start' => ['restaurant_pos.index', 'service_sessions.index'],
        'service_operations.items.complete' => ['restaurant_pos.index', 'service_sessions.index'],

        'stocks_management.index' => ['stocks_management.stock.index'],
        'stocks_management.initParams' => [
            'stocks_management.stock.index',
            'stocks_management.kardex.index',
            'stocks_management.transfers.index',
            'stocks_management.valued.index'
        ],
        'stocks_management.list' => ['stocks_management.stock.index'],
        'stocks_management.create' => ['stocks_management.stock.index'],
        'stocks_management.store' => ['stocks_management.stock.index'],
        'stocks_management.show' => ['stocks_management.stock.index'],
        'stocks_management.edit' => ['stocks_management.stock.index'],
        'stocks_management.update' => ['stocks_management.stock.index'],
        'stocks_management.movements' => [
            'stocks_management.kardex.index',
            'stocks_management.transfers.index',
            'stocks_management.valued.index'
        ],
        'stocks_management.movements.store' => ['stocks_management.stock.index'],
        'stocks_management.operations.store' => ['stocks_management.stock.index'],
        'stocks_management.transfers.store' => ['stocks_management.transfers.index'],
        'stocks_management.export' => [
            'stocks_management.stock.index',
            'stocks_management.kardex.index',
            'stocks_management.transfers.index',
            'stocks_management.valued.index'
        ],

        'cash_registers.index' => ['cash_registers.registers.index'],
        'cash_registers.initParams' => [
            'cash_registers.registers.index',
            'cash_registers.sessions.index',
            'cash_registers.movements.index',
            'cash_registers.summary.index'
        ],
        'cash_registers.list' => ['cash_registers.registers.index'],
        'cash_registers.store' => ['cash_registers.registers.index'],
        'cash_registers.sessions' => ['cash_registers.sessions.index'],
        'cash_registers.open' => ['cash_registers.sessions.index'],
        'cash_registers.close' => ['cash_registers.sessions.index'],
        'cash_registers.movements' => ['cash_registers.movements.index'],
        'cash_registers.movement' => ['cash_registers.movements.index'],
        'cash_registers.summary' => ['cash_registers.summary.index'],
        'cash_registers.export' => [
            'cash_registers.registers.index',
            'cash_registers.sessions.index',
            'cash_registers.movements.index',
            'cash_registers.summary.index'
        ]
    ]
];
