<?php
return [
    'accepted'             => ':attribute muss akzeptiert werden.',
    'accepted_if'          => ':attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url'           => ':attribute ist keine gültige URL.',
    'after'                => ':attribute muss ein Datum nach :date sein.',
    'after_or_equal'       => ':attribute muss ein Datum nach oder gleich :date sein.',
    'alpha'                => ':attribute darf nur Buchstaben enthalten.',
    'alpha_dash'           => ':attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
    'alpha_num'            => ':attribute darf nur Buchstaben und Zahlen enthalten.',
    'array'                => ':attribute muss ein Array sein.',
    'before'               => ':attribute muss ein Datum vor :date sein.',
    'before_or_equal'      => ':attribute muss ein Datum vor oder gleich :date sein.',
    'between'              => [
        'numeric' => ':attribute muss zwischen :min und :max liegen.',
        'file'    => ':attribute muss zwischen :min und :max Kilobyte groß sein.',
        'string'  => ':attribute muss zwischen :min und :max Zeichen enthalten.',
        'array'   => ':attribute muss zwischen :min und :max Elemente enthalten.',
    ],
    'boolean'              => ':attribute muss wahr oder falsch sein.',
    'confirmed'            => 'Die Bestätigung von :attribute stimmt nicht überein.',
    'date'                 => ':attribute ist kein gültiges Datum.',
    'date_equals'          => ':attribute muss ein Datum sein, das :date entspricht.',
    'date_format'          => ':attribute entspricht nicht dem Format :format.',
    'different'            => ':attribute und :other müssen unterschiedlich sein.',
    'digits'               => ':attribute muss :digits Ziffern haben.',
    'digits_between'       => ':attribute muss zwischen :min und :max Ziffern haben.',
    'dimensions'           => ':attribute hat ungültige Bildabmessungen.',
    'distinct'             => ':attribute hat einen doppelten Wert.',
    'email'                => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with'            => ':attribute muss mit einem der folgenden Werte enden: :values.',
    'exists'               => ':attribute ist nicht vorhanden.',
    'file'                 => ':attribute muss eine Datei sein.',
    'filled'               => ':attribute muss einen Wert enthalten.',
    'gt'                   => [
        'numeric' => ':attribute muss größer sein als :value.',
        'file'    => ':attribute muss größer als :value Kilobyte sein.',
        'string'  => ':attribute muss mehr als :value Zeichen enthalten.',
        'array'   => ':attribute muss mehr als :value Elemente enthalten.',
    ],
    'gte'                  => [
        'numeric' => ':attribute muss größer oder gleich :value sein.',
        'file'    => ':attribute muss größer oder gleich :value Kilobyte sein.',
        'string'  => ':attribute muss mindestens :value Zeichen enthalten.',
        'array'   => ':attribute muss mindestens :value Elemente enthalten.',
    ],
    'image'                => ':attribute muss ein Bild sein.',
    'in'                   => ':attribute ist ungültig.',
    'in_array'             => 'Das Feld :attribute existiert nicht in :other.',
    'integer'              => ':attribute muss eine ganze Zahl sein.',
    'ip'                   => ':attribute muss eine gültige IP-Adresse sein.',
    'ipv4'                 => ':attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6'                 => ':attribute muss eine gültige IPv6-Adresse sein.',
    'json'                 => ':attribute muss eine gültige JSON-Zeichenkette sein.',
    'lt'                   => [
        'numeric' => ':attribute muss kleiner sein als :value.',
        'file'    => ':attribute muss kleiner als :value Kilobyte sein.',
        'string'  => ':attribute muss weniger als :value Zeichen enthalten.',
        'array'   => ':attribute muss weniger als :value Elemente enthalten.',
    ],
    'lte'                  => [
        'numeric' => ':attribute muss kleiner oder gleich :value sein.',
        'file'    => ':attribute muss kleiner oder gleich :value Kilobyte sein.',
        'string'  => ':attribute muss höchstens :value Zeichen enthalten.',
        'array'   => ':attribute muss höchstens :value Elemente enthalten.',
    ],
    'max'                  => [
        'numeric' => ':attribute darf nicht größer als :max sein.',
        'file'    => ':attribute darf nicht größer als :max Kilobyte sein.',
        'string'  => ':attribute darf nicht mehr als :max Zeichen enthalten.',
        'array'   => ':attribute darf nicht mehr als :max Elemente enthalten.',
    ],
    'mimes'                => ':attribute muss eine Datei des Typs: :values sein.',
    'mimetypes'            => ':attribute muss eine Datei des Typs: :values sein.',
    'min'                  => [
        'numeric' => ':attribute muss mindestens :min sein.',
        'file'    => ':attribute muss mindestens :min Kilobyte groß sein.',
        'string'  => ':attribute muss mindestens :min Zeichen enthalten.',
        'array'   => ':attribute muss mindestens :min Elemente enthalten.',
    ],
    'not_in'               => ':attribute ist ungültig.',
    'not_regex'            => 'Das Format von :attribute ist ungültig.',
    'numeric'              => ':attribute muss eine Zahl sein.',
    'present'              => 'Das Feld :attribute muss vorhanden sein.',
    'regex'                => 'Das Format von :attribute ist ungültig.',
    'required'             => 'Das Feld :attribute ist erforderlich.',
    'required_if'          => 'Das Feld :attribute ist erforderlich, wenn :other :value ist.',
    'required_unless'      => 'Das Feld :attribute ist erforderlich, es sei denn, :other ist in :values.',
    'required_with'        => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden ist.',
    'required_with_all'    => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden sind.',
    'required_without'     => 'Das Feld :attribute ist erforderlich, wenn :values nicht vorhanden ist.',
    'required_without_all' => 'Das Feld :attribute ist erforderlich, wenn keines der :values vorhanden ist.',
    'same'                 => ':attribute muss mit :other übereinstimmen.',
    'size'                 => [
        'numeric' => ':attribute muss :size sein.',
        'file'    => ':attribute muss :size Kilobyte groß sein.',
        'string'  => ':attribute muss :size Zeichen enthalten.',
        'array'   => ':attribute muss :size Elemente enthalten.',
    ],
    'string'               => ':attribute muss eine Zeichenkette sein.',
    'timezone'             => ':attribute muss eine gültige Zeitzone sein.',
    'unique'               => ':attribute wurde bereits verwendet.',
    'uploaded'             => ':attribute konnte nicht hochgeladen werden.',
    'url'                  => 'Das Format von :attribute ist ungültig.',
    'uuid'                 => ':attribute muss eine gültige UUID sein.',

    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
     */

    'attributes'           => [],
];
