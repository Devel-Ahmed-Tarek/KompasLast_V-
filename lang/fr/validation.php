<?php

return [
    'accepted'             => 'Le :attribute doit être accepté.',
    'accepted_if'          => 'Le :attribute doit être accepté lorsque :other est :value.',
    'active_url'           => ':attribute n\'est pas une URL valide.',
    'after'                => 'Le :attribute doit être une date après :date.',
    'after_or_equal'       => 'Le :attribute doit être une date après ou égale à :date.',
    'alpha'                => ':attribute ne peut contenir que des lettres.',
    'alpha_dash'           => ':attribute peut seulement contenir des lettres, chiffres, tirets et tirets bas.',
    'alpha_num'            => ':attribute peut seulement contenir des lettres et des chiffres.',
    'array'                => ':attribute doit être un tableau.',
    'before'               => 'Le :attribute doit être une date avant :date.',
    'before_or_equal'      => 'Le :attribute doit être une date avant ou égale à :date.',
    'between'              => [
        'numeric' => ':attribute doit être entre :min et :max.',
        'file'    => 'La taille de :attribute doit être entre :min et :max kilobytes.',
        'string'  => ':attribute doit contenir entre :min et :max caractères.',
        'array'   => ':attribute doit contenir entre :min et :max éléments.',
    ],
    'boolean'              => ':attribute doit être vrai ou faux.',
    'confirmed'            => 'La confirmation de :attribute ne correspond pas.',
    'date'                 => ':attribute n\'est pas une date valide.',
    'date_equals'          => ':attribute doit être une date égale à :date.',
    'date_format'          => ':attribute ne correspond pas au format de date :format.',
    'different'            => ':attribute et :other doivent être différents.',
    'digits'               => ':attribute doit être un nombre de :digits chiffres.',
    'digits_between'       => ':attribute doit être un nombre de chiffres entre :min et :max.',
    'dimensions'           => ':attribute a des dimensions d\'image non valides.',
    'distinct'             => ':attribute a une valeur en double.',
    'email'                => ':attribute doit être une adresse e-mail valide.',
    'ends_with'            => ':attribute doit se terminer par l\'une des valeurs suivantes : :values.',
    'exists'               => ':attribute sélectionné est invalide.',
    'file'                 => ':attribute doit être un fichier.',
    'filled'               => 'Le champ :attribute doit avoir une valeur.',
    'gt'                   => [
        'numeric' => ':attribute doit être supérieur à :value.',
        'file'    => 'La taille de :attribute doit être supérieure à :value kilobytes.',
        'string'  => ':attribute doit contenir plus de :value caractères.',
        'array'   => ':attribute doit contenir plus de :value éléments.',
    ],
    'gte'                  => [
        'numeric' => ':attribute doit être supérieur ou égal à :value.',
        'file'    => 'La taille de :attribute doit être supérieure ou égale à :value kilobytes.',
        'string'  => ':attribute doit contenir :value caractères ou plus.',
        'array'   => ':attribute doit contenir :value éléments ou plus.',
    ],
    'image'                => ':attribute doit être une image.',
    'in'                   => ':attribute sélectionné est invalide.',
    'in_array'             => 'Le champ :attribute n\'existe pas dans :other.',
    'integer'              => ':attribute doit être un nombre entier.',
    'ip'                   => ':attribute doit être une adresse IP valide.',
    'ipv4'                 => ':attribute doit être une adresse IPv4 valide.',
    'ipv6'                 => ':attribute doit être une adresse IPv6 valide.',
    'json'                 => ':attribute doit être une chaîne JSON valide.',
    'lt'                   => [
        'numeric' => ':attribute doit être inférieur à :value.',
        'file'    => 'La taille de :attribute doit être inférieure à :value kilobytes.',
        'string'  => ':attribute doit contenir moins de :value caractères.',
        'array'   => ':attribute doit contenir moins de :value éléments.',
    ],
    'lte'                  => [
        'numeric' => ':attribute doit être inférieur ou égal à :value.',
        'file'    => 'La taille de :attribute doit être inférieure ou égale à :value kilobytes.',
        'string'  => ':attribute doit contenir :value caractères ou moins.',
        'array'   => ':attribute ne doit pas contenir plus de :value éléments.',
    ],
    'max'                  => [
        'numeric' => ':attribute ne peut pas être supérieur à :max.',
        'file'    => 'La taille de :attribute ne peut pas être supérieure à :max kilobytes.',
        'string'  => ':attribute ne peut pas contenir plus de :max caractères.',
        'array'   => ':attribute ne peut pas contenir plus de :max éléments.',
    ],
    'mimes'                => ':attribute doit être un fichier de type :values.',
    'mimetypes'            => ':attribute doit être un fichier de type :values.',
    'min'                  => [
        'numeric' => ':attribute doit être au moins :min.',
        'file'    => 'La taille de :attribute doit être au moins :min kilobytes.',
        'string'  => ':attribute doit contenir au moins :min caractères.',
        'array'   => ':attribute doit contenir au moins :min éléments.',
    ],
    'not_in'               => ':attribute sélectionné est invalide.',
    'not_regex'            => 'Le format de :attribute est invalide.',
    'numeric'              => ':attribute doit être un nombre.',
    'present'              => 'Le champ :attribute doit être présent.',
    'regex'                => 'Le format de :attribute est invalide.',
    'required'             => 'Le champ :attribute est requis.',
    'required_if'          => 'Le champ :attribute est requis lorsque :other est :value.',
    'required_unless'      => 'Le champ :attribute est requis sauf si :other est dans :values.',
    'required_with'        => 'Le champ :attribute est requis lorsque :values est présent.',
    'required_with_all'    => 'Le champ :attribute est requis lorsque :values est présent.',
    'required_without'     => 'Le champ :attribute est requis lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est requis lorsque aucun de :values n\'est présent.',
    'same'                 => ':attribute et :other doivent correspondre.',
    'size'                 => [
        'numeric' => ':attribute doit être de taille :size.',
        'file'    => 'La taille de :attribute doit être :size kilobytes.',
        'string'  => ':attribute doit contenir :size caractères.',
        'array'   => ':attribute doit contenir :size éléments.',
    ],
    'string'               => ':attribute doit être une chaîne.',
    'timezone'             => ':attribute doit être un fuseau horaire valide.',
    'unique'               => ':attribute a déjà été pris.',
    'uploaded'             => 'Le téléchargement de :attribute a échoué.',
    'url'                  => 'Le format de :attribute est invalide.',
    'uuid'                 => ':attribute doit être un UUID valide.',

    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Attributs de validation personnalisés
    |----------------------------------------------------------------------
    |
    | Les lignes de langue suivantes sont utilisées pour remplacer l'espace réservé
    | d'attribut par quelque chose de plus convivial comme "Adresse e-mail"
    | au lieu de "email". Cela nous aide simplement à rendre notre message
    | plus expressif.
    |
     */

    'attributes'           => [],
];
