<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class JsonToArrayTransformer implements DataTransformerInterface
{
    /**
     * Transforme un tableau en chaîne JSON pour l'affichage dans le formulaire.
     *
     * @param mixed|null $value La valeur d'entrée (tableau ou null)
     * @return mixed La chaîne JSON ou une chaîne vide
     */
    public function transform(mixed $value): mixed
    {
        if (is_array($value)) {
            return json_encode($value);
        }

        return ''; // Retourne une chaîne vide si la valeur est null ou non valide
    }

    /**
     * Transforme une chaîne JSON en tableau pour l'entité.
     *
     * @param mixed|null $value La valeur d'entrée (chaîne JSON ou tableau)
     * @return mixed Le tableau ou un tableau vide
     * @throws \InvalidArgumentException Si le JSON est invalide
     */
    public function reverseTransform(mixed $value): mixed
    {
        // Si la valeur est déjà un tableau, la retourner directement
        if (is_array($value)) {
            return $value;
        }

        // Si la valeur est une chaîne JSON, la décoder
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Le JSON fourni est invalide.');
            }
            return $decoded;
        }

        // Retourner un tableau vide pour toute autre entrée invalide
        return [];
    }
}
