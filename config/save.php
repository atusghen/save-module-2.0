<?php

return [
    "plant" => [
        [
           [
            "name"      => "label",
            "width"     => 8,
            "type"      => "text",
            "disabled"  => false,
            "readonly"  => false,
            "required"  => true,
            "classes"   => []
           ],
           [
            "name"      => "municipality_code",
            "width"     => 4,
            "type"      => "select",
            "options"   => [],
            "options_translate" => false,
            "disabled"  => false,
            "readonly"  => false,
            "required"  => true,
            "classes"   => []
           ]
        ]
    ],
    "ha" => [
        [
            [
                "name"      => "plant_id",
                "width"     => 4,
                "type"      => "hidden",
                "disabled"  => true,
                "readonly"  => false,
                "required"  => true,
                "classes"   => []
            ],
            [
                "name"      => "type",
                "width"     => 4,
                "type"      => "select",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "options"   => ["as_is","to_be"],
                "options_translate" => true
            ]
        ],
        [
            [
                "name"      => "lamp_cost",
                "width"     => 6,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "euro"
            ],
            [
                "name"      => "lamp_disposal",
                "width"     => 6,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "euro"
            ],
        ],
        [
            [
                "name"      => "lamp_maintenance_interval",
                "width"     => 6,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 20,
                "step"      => 1,
                "symbol"    => "years"
            ],
            [
                "name"      => "panel_cost",
                "width"     => 6,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "euro"
            ],
        ],
    ],
    "cluster" => [
        [
            [
                "name"      => "ha_id",
                "width"     => 4,
                "type"      => "hidden",
                "disabled"  => true,
                "readonly"  => false,
                "required"  => true,
                "classes"   => []
            ],
            [
                "name"      => "label",
                "width"     => 6,
                "type"      => "text",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => []
            ],
        ],
        [
            [
                "name"      => "technology",
                "width"     => 4,
                "type"      => "select",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "options"   => [
                    "option_lamp_01",
                    "option_lamp_02",
                    "option_lamp_03",
                    "option_lamp_04",
                    "option_lamp_05",
                    "option_lamp_06",
                    "option_lamp_07",
                    "option_lamp_08",
                    "option_lamp_09",
                    "option_lamp_95"
                ],
                "options_translate" => true
            ],
            [
                "name"      => "lamp_num",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 20,
                "step"      => 1,
                "symbol"    => "#"
            ],
            [
                "name"      => "device_num",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 20,
                "step"      => 1,
                "symbol"    => "#"
            ],
        ],
        [
            [
                "name"      => "average_device_power",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "Kwh"
            ],
            [
                "name"      => "dimmering",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 100,
                "step"      => 0.5,
                "symbol"    => "%"
            ],
        ],
    ],
    "investment" => [
        [
            [
                "name"      => "label",
                "width"     => 4,
                "type"      => "text",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => []
            ]
        ],
        [
            [
                "name"      => "wacc",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "share_municipality",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "share_bank",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
        ],
        [
            [
                "name"      => "mortgage_installment",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#",
            ],
            [
                "name"      => "fee_esco",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "share_esco",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
        ],
        [
            [
                "name"      => "energy_unit_cost",
                "width"     => 3,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "incentives_duration",
                "width"     => 3,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "tep_kwh",
                "width"     => 3,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "tep_value",
                "width"     => 3,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
        ],
        [
            [
                "name"      => "management_cost",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "duration_amortization",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "project_duration",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
        ],
        [
            [
                "name"      => "taxes",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "share_funded",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
            [
                "name"      => "cost_funded",
                "width"     => 4,
                "type"      => "number",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => [],
                "min"       => 0,
                "max"       => 1000,
                "step"      => 0.1,
                "symbol"    => "#"
            ],
        ],
    ],
    "analysis" => [
        [
            [
                "name"      => "label",
                "width"     => 6,
                "type"      => "text",
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => []
            ]
        ],
        [
            [
                "name"      => "plant_name",
                "width"     => 4,
                "type"      => "select",
                "options"   => [],
                "options_translate" => false,
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => []
            ],
            [
                "name"      => "investment_name",
                "width"     => 4,
                "type"      => "select",
                "options"   => [],
                "options_translate" => false,
                "disabled"  => false,
                "readonly"  => false,
                "required"  => true,
                "classes"   => []
            ]
        ]
    ]
];
