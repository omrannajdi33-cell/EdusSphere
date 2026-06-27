<?php

return [
    'accepted' => 'Le champ :attribute doit être accepté.',
    'confirmed' => 'La confirmation de :attribute ne correspond pas.',
    'email' => 'Le champ :attribute doit être une adresse email valide.',
    'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
    'in' => 'La valeur sélectionnée pour :attribute est invalide.',
    'max' => [
        'string' => 'Le champ :attribute ne doit pas dépasser :max caractères.',
        'file' => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
    ],
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'required' => 'Le champ :attribute est obligatoire.',
    'unique' => 'Ce :attribute est déjà utilisé.',

    'attributes' => [
        'email' => 'courriel',
        'first_name' => 'prénom',
        'last_name' => 'nom',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'birth_date' => 'date de naissance',
        'school_level_id' => 'niveau scolaire',
        'status' => 'statut',
        'avatar' => 'photo de profil',
    ],
];
